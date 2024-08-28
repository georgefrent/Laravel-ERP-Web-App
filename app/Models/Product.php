<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Define the table name (optional if it follows Laravel's naming conventions)
    protected $table = 'products';

    // Define the primary key (optional if it follows Laravel's naming conventions)
    protected $primaryKey = 'product_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'product_name',
        'category_name',
        'brand',
        'model',
        'price',
        'quantity_in_stock',
        'description',
        'specifications',
        'entered_at',
    ];

    // Specify the fields that should be cast to native types
    protected $casts = [
        'specifications' => 'array',
        'entered_at' => 'datetime',
    ];

    // Define the relationship with the Category model
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_name', 'category_name');
    }
}
