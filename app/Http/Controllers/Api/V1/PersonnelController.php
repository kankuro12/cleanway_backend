<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\PersonnelScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = PersonnelScope::apply(User::query(), $request->user())
            ->filter($request->only(['search', 'role', 'status', 'branch_id']))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => ['pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]],
            'links' => $users->toArray()['links'] ?? null,
        ]);
    }

    public function store(StorePersonnelRequest $request): JsonResponse
    {
        $user = DB::transaction(fn () => User::create($request->validated()));

        return response()->json(['data' => new UserResource($user)], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => new UserResource($user)]);
    }

    public function update(UpdatePersonnelRequest $request, User $user): JsonResponse
    {
        DB::transaction(fn () => $user->update($request->validated()));

        return response()->json(['data' => new UserResource($user->fresh())]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        DB::transaction(function () use ($user): void {
            $user->update(['status' => User::STATUS_ARCHIVED]);
            $user->delete();
        });

        return response()->json(['data' => null]);
    }

    public function teamStatus(Request $request): JsonResponse
    {
        $users = PersonnelScope::apply(User::query(), $request->user())
            ->whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])
            ->with(['branch:id,name', 'team:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'status', 'branch_id', 'team_id']);

        return response()->json(['data' => $users]);
    }
}
