<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    // Specify the table name if it is not the plural form of the model name
    protected $table = 'inventory';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'product_name',
        'description',
        'stock_level',
        'price',
    ];
}
