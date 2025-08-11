<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerEmail extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id','value','label'];

    public function customer(){ return $this->belongsTo(Customer::class); }
}
