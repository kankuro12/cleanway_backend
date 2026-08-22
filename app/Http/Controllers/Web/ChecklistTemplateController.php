<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistTemplateRequest;
use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use App\Models\ChecklistTemplate;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChecklistTemplateController extends Controller
{
    public function index(): View
    {
        return view('pages.checklists', [
            'templates' => ChecklistTemplate::with('sections.items')->orderBy('name')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('pages.checklist-edit', [
            'template' => new ChecklistTemplate(),
            'isCreate' => true,
        ]);
    }

    public function edit(ChecklistTemplate $template): View
    {
        $template->load('sections.items');

        return view('pages.checklist-edit', [
            'template' => $template,
            'isCreate' => false,
        ]);
    }

    public function items(ChecklistTemplate $checklist): \Illuminate\Http\JsonResponse
    {
        $checklist->load('sections.items');
        return response()->json([
            'sections' => $checklist->sections->map(fn ($s) => [
                'name' => $s->name,
                'items' => $s->items->map(fn ($i) => [
                    'label' => $i->label,
                    'item_type' => $i->item_type,
                    'is_photo_required' => (bool) $i->is_photo_required,
                    'is_comment_required' => (bool) $i->is_comment_required,
                ]),
            ]),
        ]);
    }

    public function store(StoreChecklistTemplateRequest $request, AuditLogger $audit): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $template = DB::transaction(function () use ($request): ChecklistTemplate {
            $template = ChecklistTemplate::create(
                $request->safe()->except(['sections']) + ['slug' => ChecklistTemplate::uniqueSlug($request->string('name'))]
            );

            $this->storeSections($template, $request->input('sections', []));

            return $template;
        });

        $audit->log('checklist_template.created', 'checklist_template', $template->id, ['after' => ['name' => $template->name]]);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Checklist created successfully.',
                'redirect' => route('checklists.edit', $template),
                'template' => $template->load('sections.items'),
            ]);
        }

        return redirect()->route('checklists')->with('status', 'Checklist created.');
    }

    public function update(StoreChecklistTemplateRequest $request, ChecklistTemplate $template, AuditLogger $audit): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        DB::transaction(function () use ($request, $template): void {
            $template->update($request->safe()->except(['sections']));

            if ($request->has('sections')) {
                $template->sections()->delete();
                $this->storeSections($template, $request->input('sections', []));
            }
        });

        $audit->log('checklist_template.updated', 'checklist_template', $template->id);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Checklist updated successfully.',
                'template' => $template->fresh(['sections.items']),
            ]);
        }

        return redirect()->route('checklists')->with('status', 'Checklist updated.');
    }

    public function destroy(ChecklistTemplate $template, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($template): void {
            $template->sections()->delete();
            $template->delete();
        });

        $audit->log('checklist_template.deleted', 'checklist_template', $template->id, ['before' => ['name' => $template->name]]);

        return redirect()->route('checklists')->with('status', 'Checklist deleted successfully.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    private function storeSections(ChecklistTemplate $template, array $sections): void
    {
        foreach ($sections as $sectionIndex => $section) {
            $sectionModel = ChecklistSection::create([
                'checklist_template_id' => $template->id,
                'name' => $section['name'],
                'sort_order' => $sectionIndex,
            ]);

            foreach ($section['items'] ?? [] as $itemIndex => $item) {
                ChecklistItem::create([
                    'checklist_section_id' => $sectionModel->id,
                    'label' => $item['label'],
                    'item_type' => $item['item_type'],
                    'required' => (bool) ($item['required'] ?? true),
                    'is_photo_required' => (bool) ($item['is_photo_required'] ?? false),
                    'is_comment_required' => (bool) ($item['is_comment_required'] ?? false),
                    'issue_triggering' => (bool) ($item['issue_triggering'] ?? false),
                    'sort_order' => $itemIndex,
                ]);
            }
        }
    }
}
