<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Define the table name (optional if it follows Laravel's naming conventions)
    protected $table = 'product_categories';

    // Define the primary key (optional if it follows Laravel's naming conventions)
    protected $primaryKey = 'category_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'category_name',
        'category_description',
    ];

    // Define the relationship with the Product model
    public function products()
    {
        return $this->hasMany(Product::class, 'category_name', 'category_name');
    }
}
