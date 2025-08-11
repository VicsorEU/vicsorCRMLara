<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use Auditable;

    protected $fillable = [
        'is_variable','name','slug','sku','barcode','price_regular','price_sale',
        'weight','length','width','height','short_description','description',
    ];

    protected $casts = [
        'is_variable'   => 'boolean',
        'price_regular' => 'decimal:2',
        'price_sale'    => 'decimal:2',
        'weight'        => 'decimal:3',
        'length'        => 'decimal:3',
        'width'         => 'decimal:3',
        'height'        => 'decimal:3',
    ];

    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }

    public function variations(): HasMany { return $this->hasMany(ProductVariation::class); }

    /** many-to-many с attribute_values через product_attribute_value */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_attribute_value',
            'product_id',
            'attribute_value_id'
        );
    }
}
