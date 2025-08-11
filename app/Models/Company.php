<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    use Auditable;

    protected $fillable = [
        'name','email','phone','website','tax_number','city','country','address','notes','owner_id'
    ];

    public function owner(){ return $this->belongsTo(User::class, 'owner_id'); }
    public function contacts(){ return $this->hasMany(Contact::class); }
}
