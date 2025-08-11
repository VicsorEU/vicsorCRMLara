<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles; // раскомментируешь, когда подключим роли

class User extends Authenticatable
{
    use HasFactory, Notifiable; // , HasRoles;

    protected $fillable = ['name', 'email', 'phone', 'company', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedTasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'user_id');
    }

    public function taskComments()
    {
        return $this->hasMany(\App\Models\TaskComment::class, 'user_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(\App\Models\TaskTimeEntry::class, 'user_id');
    }
}
