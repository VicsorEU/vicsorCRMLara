<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProjectGrade extends Model
{
    protected $table = 'settings_project_grades';
    protected $fillable = ['name', 'color', 'position'];
    public $timestamps = true;
}
