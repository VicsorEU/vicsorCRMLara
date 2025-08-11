<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Contact extends Model
{
    use HasFactory, SoftDeletes;
    use Auditable;

    protected $fillable = [
        'first_name','last_name','email','phone','position','company_id','notes','owner_id'
    ];

    public function owner(){ return $this->belongsTo(User::class, 'owner_id'); }
    public function company(){ return $this->belongsTo(Company::class); }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
