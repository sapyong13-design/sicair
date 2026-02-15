<?php

namespace App\Traits;

use App\Models\AuditLog;

trait LogsActivity
{
    /**
     * Boot the trait
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('create', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $model->logActivity('update', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logActivity('delete', $model->getAttributes(), null);
        });
    }

    /**
     * Log activity for the model
     */
    protected function logActivity(string $action, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): void
    {
        // Skip logging if not authenticated
        if (!auth()->check()) {
            return;
        }

        // Filter out sensitive fields
        $oldValues = $this->filterSensitiveFields($oldValues);
        $newValues = $this->filterSensitiveFields($newValues);

        AuditLog::log(
            action: $action,
            model: class_basename($this),
            modelId: $this->getKey(),
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description
        );
    }

    /**
     * Filter sensitive fields from logging
     */
    protected function filterSensitiveFields(?array $values): ?array
    {
        if (!$values) {
            return null;
        }

        $sensitiveFields = ['password', 'remember_token', 'api_token'];

        return collect($values)
            ->reject(function ($value, $key) use ($sensitiveFields) {
                return in_array($key, $sensitiveFields);
            })
            ->toArray();
    }

    /**
     * Get the list of attributes to audit
     * Override in model if you want to log only specific fields
     */
    public function getAuditableAttributes(): array
    {
        return $this->fillable ?? array_keys($this->getAttributes());
    }
}
