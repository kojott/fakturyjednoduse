<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'vat_rate',
        'total_without_vat',
        'total_with_vat',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate totals
            $item->total_without_vat = $item->quantity * $item->unit_price;
            $item->total_with_vat = $item->total_without_vat * (1 + $item->vat_rate / 100);
        });

        static::created(function ($item) {
            // Update invoice totals
            $invoice = $item->invoice;
            $invoice->total_without_vat = $invoice->items->sum('total_without_vat');
            $invoice->total_with_vat = $invoice->items->sum('total_with_vat');
            $invoice->save();
        });

        static::updated(function ($item) {
            // Update invoice totals
            $invoice = $item->invoice;
            $invoice->total_without_vat = $invoice->items->sum('total_without_vat');
            $invoice->total_with_vat = $invoice->items->sum('total_with_vat');
            $invoice->save();
        });

        static::deleted(function ($item) {
            // Update invoice totals
            $invoice = $item->invoice;
            $invoice->total_without_vat = $invoice->items->sum('total_without_vat');
            $invoice->total_with_vat = $invoice->items->sum('total_with_vat');
            $invoice->save();
        });
    }
}
