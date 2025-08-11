<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = ['name','slug','description','parent_id'];

    public function parent()   { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function values()   { return $this->hasMany(AttributeValue::class, 'attribute_id')->orderBy('sort_order'); }
}
