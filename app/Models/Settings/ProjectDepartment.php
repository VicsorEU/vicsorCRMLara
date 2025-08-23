<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProjectDepartment extends Model
{
    protected $table = 'settings_project_departments';
    protected $fillable = ['name', 'color', 'position'];
}

