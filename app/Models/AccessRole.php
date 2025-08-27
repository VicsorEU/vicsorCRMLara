<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    protected $table = 'access_roles';

    protected $fillable = ['name','slug','abilities','system'];

    protected $casts = [
        'abilities' => 'array',
        'system'    => 'boolean',
    ];

    // нормализация прав, чтобы всегда был предсказуемый набор
    public function getAbilitiesAttribute($value)
    {
        $base = ['settings_edit'=>false, 'projects'=>'none'];
        $arr  = is_array($value) ? $value : (json_decode($value, true) ?: []);
        return array_merge($base, $arr);
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'access_role_id');
    }
}
