<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ico',
        'company_name',
        'address',
        'city',
        'zip_code',
        'country',
        'dic',
        'note',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoices for the customer.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the full address attribute.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->zip_code}, {$this->country}";
    }
}
