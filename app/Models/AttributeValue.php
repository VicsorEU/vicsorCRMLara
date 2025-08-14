<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Concerns\Auditable;


class AttributeValue extends Model
{
    use Auditable;

    protected $table = 'attribute_values';

    protected $fillable = ['attribute_id', 'name', 'slug', 'sort_order'];

    public $timestamps = false;

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /** Значения, привязанные к простому товару */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_attribute_value',
            'attribute_value_id',
            'product_id'
        );
    }

    /** Значения, привязанные к вариациям */
    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariation::class,
            'variation_attribute_value',
            'attribute_value_id',
            'variation_id'
        );
    }
}
