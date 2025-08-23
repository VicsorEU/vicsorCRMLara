<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskType extends Model
{
    protected $table = 'settings_project_task_types';
    protected $fillable = ['name', 'color', 'position'];
}
