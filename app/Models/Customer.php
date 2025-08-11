<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    use Auditable;

    protected $fillable = [
        'full_name','manager_id','note','birth_date'
    ];

    protected $casts = ['birth_date' => 'date'];

    public function manager(){ return $this->belongsTo(User::class, 'manager_id'); }
    public function channels(){ return $this->hasMany(CustomerChannel::class); }
    public function addresses(){ return $this->hasMany(CustomerAddress::class); }
    public function defaultAddress(){ return $this->hasOne(CustomerAddress::class)->where('is_default', true); }

    public function phones(){ return $this->hasMany(CustomerPhone::class); }
    public function emails(){ return $this->hasMany(CustomerEmail::class); }
}
