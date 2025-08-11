<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename(static::class))   // лог-нэйм = имя модели
            ->logFillable()                               // логируем поля из $fillable
            ->logOnlyDirty()                              // только изменившиеся
            ->dontSubmitEmptyLogs();                      // не писать пустые
    }

    // Читабельное описание события
    public function getDescriptionForEvent(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'создано',
            'updated' => 'обновлено',
            'deleted' => 'удалено',
            default   => $eventName,
        };
    }

    // Добавляем контекст запроса в каждую запись
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->causer()->associate(auth()->user()); // кто сделал

        $extra = [
            'ip'     => request()->ip(),
            'agent'  => (string) request()->userAgent(),
            'url'    => (string) request()->fullUrl(),
            'method' => (string) request()->method(),
            // удобно для UI
            'label'  => $this->getAuditLabel(),
        ];

        // merge с уже подготовленными changes (old/attributes)
        $activity->properties = $activity->properties->merge($extra);
    }

    // Заголовок объекта в логе
    protected function getAuditLabel(): string
    {
        $titleKeys = ['name','title','full_name','slug','code','email'];
        $val = collect($titleKeys)->map(fn($k)=>$this->{$k} ?? null)->first(fn($v)=>filled($v));
        return trim(class_basename(static::class).' #'.$this->getKey().($val ? ' — '.$val : ''));
    }
}
