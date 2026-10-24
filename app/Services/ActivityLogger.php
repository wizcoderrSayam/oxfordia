<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Writes the administrative audit trail. Never pass passwords, tokens or unnecessary personal data.
 */
class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Names of the attributes that changed on a model, without their values.
     *
     * @param  list<string>  $ignore
     * @return list<string>
     */
    public function changedFields(Model $model, array $ignore = ['updated_at']): array
    {
        return array_values(array_diff(array_keys($model->getChanges()), $ignore));
    }
}
