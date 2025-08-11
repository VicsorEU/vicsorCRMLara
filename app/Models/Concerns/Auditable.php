<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename(static::class))
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'создано',
            'updated' => 'обновлено',
            'deleted' => 'удалено',
            default   => $eventName,
        };
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (auth()->check()) {
            $activity->causer()->associate(auth()->user());
        }

        $activity->properties = $activity->properties->merge([
            'ip'     => request()->ip(),
            'agent'  => (string) request()->userAgent(),
            'url'    => (string) request()->fullUrl(),
            'method' => (string) request()->method(),
            'label'  => $this->getAuditLabel(),
        ]);
    }

    protected function getAuditLabel(): string
    {
        $titleKeys = ['name','title','full_name','slug','code','email'];
        $val = collect($titleKeys)->map(fn($k)=>$this->{$k} ?? null)
            ->first(fn($v)=>filled($v));
        return trim(class_basename(static::class).' #'.$this->getKey().($val ? ' — '.$val : ''));
    }
}
