<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $fillable = ['name', 'slug', 'sort_order'];

    public $timestamps = false;

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}
