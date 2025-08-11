<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use Auditable;

    protected $fillable = [
        'product_id','variation_id','path','is_primary','sort_order','session_token'
    ];

    protected $casts = ['is_primary'=>'bool'];

    public function product()   { return $this->belongsTo(Product::class); }
    public function variation() { return $this->belongsTo(ProductVariation::class); }

    public function url(): string { return asset('storage/'.$this->path); }
}
