<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\Auditable;


class TaskTimer extends Model
{
    use Auditable;

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'stopped_at',
        'manual',
        'duration_sec',
    ];

    // Оставляем только boolean-каст, время обрабатываем своими аксессорами/мутаторами,
    // чтобы избежать автоконверта таймзоны со стороны Eloquent.
    protected $casts = [
        'manual' => 'bool',
    ];

    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * Всегда храним started_at в UTC.
     * - Если пришла строка без зоны — считаем её в app.timezone и переводим в UTC.
     * - Если есть зона/это Carbon — приводим к UTC.
     * Возвращаем Carbon в UTC.
     */
    protected function startedAt(): Attribute
    {
        $appTz = config('app.timezone') ?: 'UTC';

        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                // значение из БД трактуем как UTC
                return Carbon::parse($value, 'UTC');
            },
            set: function ($value) use ($appTz) {
                if (!$value) return null;

                if ($value instanceof Carbon) {
                    return $value->clone()->utc();
                }

                if (is_numeric($value)) {
                    // timestamp в секундах
                    return Carbon::createFromTimestamp((int)$value, $appTz)->utc();
                }

                $s = (string)$value;

                // Если строка с явно указанной зоной (Z или +/-hh:mm) — парсим и приводим к UTC
                if (preg_match('/(Z|[+\-]\d\d:\d\d)$/', $s)) {
                    return Carbon::parse($s)->utc();
                }

                // Наивная строка → считаем, что это локальное app.timezone, и переводим в UTC
                return Carbon::parse($s, $appTz)->utc();
            }
        );
    }

    /**
     * Аналогично для stopped_at — всегда UTC в хранилище, Carbon(UTC) на чтении.
     */
    protected function stoppedAt(): Attribute
    {
        $appTz = config('app.timezone') ?: 'UTC';

        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                return Carbon::parse($value, 'UTC');
            },
            set: function ($value) use ($appTz) {
                if (!$value) return null;

                if ($value instanceof Carbon) {
                    return $value->clone()->utc();
                }

                if (is_numeric($value)) {
                    return Carbon::createFromTimestamp((int)$value, $appTz)->utc();
                }

                $s = (string)$value;

                if (preg_match('/(Z|[+\-]\d\d:\d\d)$/', $s)) {
                    return Carbon::parse($s)->utc();
                }

                return Carbon::parse($s, $appTz)->utc();
            }
        );
    }

    /**
     * Удобные “локальные” представления для отображения в интерфейсе.
     * Возвращают Carbon в таймзоне приложения.
     */
    public function getStartedAtLocalAttribute(): ?Carbon
    {
        return $this->started_at ? $this->started_at->copy()->tz(config('app.timezone') ?: 'UTC') : null;
    }

    public function getStoppedAtLocalAttribute(): ?Carbon
    {
        return $this->stopped_at ? $this->stopped_at->copy()->tz(config('app.timezone') ?: 'UTC') : null;
    }

    /**
     * Мягкая остановка текущего таймера прямо сейчас (UTC) с пересчётом длительности.
     */
    public function stopNow(): void
    {
        if ($this->stopped_at) return;

        $this->stopped_at   = now()->utc();
        $this->duration_sec = (int) max(0, $this->started_at->diffInSeconds($this->stopped_at));
        $this->save();
    }

    /**
     * Вычисляемая длительность (в секундах), если обе даты заданы.
     * Здесь оба Carbon уже в UTC, поэтому diff корректный.
     */
    public function getDurationSecAttribute(): int
    {
        if ($this->started_at && $this->stopped_at) {
            return (int) $this->started_at->diffInSeconds($this->stopped_at);
        }
        return 0;
    }
}
