<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Write an audit entry. Fields are inferred from the current request
     * (actor, ip, device, source, request id) unless overridden.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function log(string $action, string $entityType, ?int $entityId = null, array $attributes = []): AuditLog
    {
        if (! config('audit.enabled', true)) {
            return new AuditLog;
        }

        $actor = $attributes['actor_id'] ?? Auth::id();
        $request = request();

        return AuditLog::create([
            'actor_id' => $actor instanceof User ? $actor->id : $actor,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $attributes['before'] ?? null,
            'after' => $attributes['after'] ?? null,
            'ip' => $attributes['ip'] ?? $request?->ip(),
            'device' => $attributes['device'] ?? substr((string) $request?->userAgent(), 0, 255) ?: null,
            'source' => $attributes['source'] ?? $this->detectSource(),
            'request_id' => $attributes['request_id'] ?? $request?->header('X-Request-Id') ?: Str::uuid()->toString(),
            'created_at' => now(),
        ]);
    }

    public function detectSource(): string
    {
        if (app()->runningInConsole()) {
            return 'system';
        }

        return request()?->is('api/*') ? 'api' : 'web';
    }
}
