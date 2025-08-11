<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\Auditable;

class Warehouse extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'name','code','description','parent_id','manager_id','phone',
        'country','region','city','street','house','postal_code',
        'is_active','allow_negative_stock','sort_order'
    ];

    protected $casts = [
        'is_active'=>'bool',
        'allow_negative_stock'=>'bool',
        'sort_order'=>'int',
    ];

    public function parent()   { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name'); }
    public function manager()  { return $this->belongsTo(User::class, 'manager_id'); }

    public function oneLineAddress(): string
    {
        return trim(collect([$this->country,$this->region,$this->city,$this->street,$this->house,$this->postal_code])
            ->filter()->implode(', '));
    }
}
