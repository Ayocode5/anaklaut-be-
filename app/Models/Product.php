<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'owned_by',
        'name',
        'description',
        'type',
        'weight',
        'price',
        'grosir_price',
        'grosir_min',
        'slug',
        'stock'
    ];

    protected $hidden = [
        'owned_by'
    ];

    public function owner() {
        return $this->belongsTo(Admin::class, 'owned_by', 'id');
    }

    // Relasi ke Model Produk Galleri
    public function product_galleries() {
        return $this->hasMany(ProductGallery::class, 'product_id', 'id');
    }

    // Relasi ke Model Order Detail
    public function order_details() {
        return $this->belongsTo(OrderDetail::class, 'product_id', 'id');
    }

}
