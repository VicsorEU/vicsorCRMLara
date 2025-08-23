<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class ProjectRandlabel extends Model
{
    protected $table = 'settings_project_randlables';
    protected $fillable = ['name', 'color', 'position'];
    public $timestamps = true; // если в таблице есть created_at/updated_at
}
