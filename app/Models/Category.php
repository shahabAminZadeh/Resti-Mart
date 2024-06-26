<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=[];
    public function sub_category()
    {
        return $this->hasMany(Category::class,'parent_id');

    }
    public function parent_category()
    {
        return $this->belongsTo(Category::class,'parent_id');

    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
