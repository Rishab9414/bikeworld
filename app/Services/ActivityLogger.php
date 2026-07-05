<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(string $action, string $module, ?Model $subject = null, ?string $description = null, array $properties = [], ?int $userId = null): void
    {
        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'ip_address' => app()->runningInConsole() ? null : request()?->ip(),
            'properties' => $properties ?: null,
        ]);
    }
}
