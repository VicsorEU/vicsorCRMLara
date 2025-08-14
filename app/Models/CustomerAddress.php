<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\Auditable;


class CustomerAddress extends Model
{
    use Auditable;

    use HasFactory;

    protected $fillable = [
        'customer_id','label','country','region','city','street','house','apartment','postal_code','is_default'
    ];

    public function customer(){ return $this->belongsTo(Customer::class); }

    public function oneLine(): string
    {
        return trim(collect([$this->country,$this->region,$this->city,$this->street,$this->house,$this->apartment,$this->postal_code])
            ->filter()->implode(', '));
    }
}
