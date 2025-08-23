<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskPriority extends Model
{
    protected $table = 'settings_project_task_priorities';
    protected $fillable = ['name', 'color', 'position'];
}
