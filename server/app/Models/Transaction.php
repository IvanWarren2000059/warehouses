<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'inventory_id',
        'transaction_type',
        'in_value',
        'out_value',
        'status',
        'order_date',
        'transaction_date',
    ];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
