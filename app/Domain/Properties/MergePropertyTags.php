<?php

namespace App\Domain\Properties;

use App\Models\PropertyTag;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class MergePropertyTags
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function execute(PropertyTag $keep, PropertyTag $merge): PropertyTag
    {
        if ($keep->is($merge)) {
            return $keep;
        }

        return DB::transaction(function () use ($keep, $merge): PropertyTag {
            // Repoint pivot rows before deleting the duplicate.
            DB::table('property_tag')
                ->where('property_tag_id', $merge->id)
                ->whereNotIn('property_id', DB::table('property_tag')->where('property_tag_id', $keep->id)->select('property_id'))
                ->update(['property_tag_id' => $keep->id]);

            $merge->delete();

            $this->audit->log('property.tag_merged', 'property_tag', $keep->id, [
                'before' => ['merged_tag_id' => $merge->id],
                'after' => ['kept_tag_id' => $keep->id],
            ]);

            return $keep->fresh();
        });
    }
}
