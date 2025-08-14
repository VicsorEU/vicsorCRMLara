<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\Auditable;


class Attribute extends Model
{
    use Auditable;

    protected $table = 'attributes';

    protected $fillable = ['name', 'slug', 'sort_order'];

    public $timestamps = false;

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}
