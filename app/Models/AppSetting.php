<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;


class AppSetting extends Model
{
    use Auditable;

    protected $fillable = ['key','value'];
    protected $casts = ['value' => 'array'];

    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function put(string $key, $value): self
    {
        return static::query()->updateOrCreate(['key'=>$key], ['value'=>$value]);
    }
}
