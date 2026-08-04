<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Auto-writes audit_logs entries on created/updated/deleted.
 * Use AuditLogger explicitly for actions with no model (login, approvals…).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            if (! $model->shouldAudit('create')) {
                return;
            }

            $model->writeAuditLog('create', null, $model->getAttributes());
        });

        static::updated(function (Model $model): void {
            if (! $model->shouldAudit('update')) {
                return;
            }

            $dirty = $model->getDirty();

            $before = array_intersect_key($model->getOriginal(), $dirty);
            $after = $dirty;

            if (! empty($before)) {
                $model->writeAuditLog('update', $before, $after);
            }
        });

        static::deleted(function (Model $model): void {
            if (! $model->shouldAudit('delete')) {
                return;
            }

            $model->writeAuditLog('delete', $model->getOriginal(), null);
        });
    }

    public function shouldAudit(string $event): bool
    {
        $disabled = $this->auditDisabled ?? false;

        return ! $disabled;
    }

    public function writeAuditLog(string $action, ?array $before, ?array $after): void
    {
        DB::afterCommit(function () use ($action, $before, $after): void {
            app(AuditLogger::class)->log(
                $action,
                static::class,
                $this->getKey(),
                ['before' => $before, 'after' => $after],
            );
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'entity', 'entity_type', 'entity_id');
    }
}
