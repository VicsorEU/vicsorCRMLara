<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerChannel extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id','kind','value'];

    public const ALLOWED = ['telegram','viber','whatsapp','instagram','facebook'];

    public function customer(){ return $this->belongsTo(Customer::class); }
}
