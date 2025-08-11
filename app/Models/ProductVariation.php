<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariation extends Model
{
    use Auditable;

    protected $fillable = [
        'product_id','sku','barcode','price_regular','price_sale',
        'weight','length','width','height','description'
    ];

    protected $casts = [
        'price_regular'=>'decimal:2',
        'price_sale'=>'decimal:2',
        'weight'=>'decimal:3',
        'length'=>'decimal:3',
        'width'=>'decimal:3',
        'height'=>'decimal:3',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    /** many-to-many с attribute_values через variation_attribute_value */
    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'variation_attribute_value',
            'variation_id',
            'attribute_value_id'
        );
    }

    public function image(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'variation_id');
    }
}
