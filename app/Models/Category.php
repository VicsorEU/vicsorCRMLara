<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\Auditable;


class Category extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = ['name','slug','description','parent_id','image_path'];

    public function parent()  { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(){ return $this->hasMany(self::class, 'parent_id'); }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }
}
