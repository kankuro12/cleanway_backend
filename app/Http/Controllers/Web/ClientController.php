<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $clients = Client::query()
            ->withCount('properties')
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('active', false))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('pages.clients', [
            'clients' => $clients,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $term = $request->string('q')->trim()->toString();

        $clients = Client::query()
            ->where('active', true)
            ->when($term !== '', fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('company_name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'company_name', 'email', 'phone']);

        return response()->json([
            'results' => $clients->map(fn ($c) => [
                'id' => $c->id,
                'text' => $c->company_name ? "{$c->name} ({$c->company_name})" : $c->name,
                'name' => $c->name,
                'company_name' => $c->company_name,
                'email' => $c->email,
                'phone' => $c->phone,
            ]),
        ]);
    }

    public function store(StoreClientRequest $request, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);
        $data['created_by'] = $request->user()?->id;

        $client = DB::transaction(fn () => Client::create($data));

        $audit->log('client.created', 'client', $client->id, ['after' => ['name' => $client->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Client created successfully.',
                'client' => $client,
            ]);
        }

        return redirect()->route('clients')->with('status', 'Client created successfully.');
    }

    public function update(StoreClientRequest $request, Client $client, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);
        $data['updated_by'] = $request->user()?->id;

        DB::transaction(fn () => $client->update($data));

        $audit->log('client.updated', 'client', $client->id, ['after' => ['name' => $client->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully.',
                'client' => $client,
            ]);
        }

        return redirect()->route('clients')->with('status', 'Client updated successfully.');
    }

    public function destroy(Client $client, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(fn () => $client->delete());

        $audit->log('client.deleted', 'client', $client->id, ['before' => ['name' => $client->name]]);

        return redirect()->route('clients')->with('status', 'Client archived successfully.');
    }
}
