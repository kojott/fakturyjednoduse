<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'invoice_number',
        'type',
        'issue_date',
        'due_date',
        'total_without_vat',
        'total_with_vat',
        'currency',
        'status',
        'note',
        'payment_method',
        'bank_account',
        'qr_payment_code',
        'is_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'is_sent' => 'boolean',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer that owns the invoice.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items for the invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Mark the invoice as paid.
     *
     * @return bool
     */
    public function markAsPaid()
    {
        $this->status = 'paid';
        return $this->save();
    }

    /**
     * Convert the invoice to a regular invoice.
     *
     * @return bool
     */
    public function convertToRegular()
    {
        if ($this->type !== 'regular') {
            $this->type = 'regular';
            // Generate new invoice number for converted invoice
            $this->invoice_number = $this->generateInvoiceNumber();
            return $this->save();
        }
        
        return false;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = $invoice->generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate a new invoice number.
     *
     * @return string
     */
    public function generateInvoiceNumber()
    {
        // Example format: YYYY/XXXX where XXXX is a sequential number
        $year = date('Y');
        $latestInvoice = self::where('invoice_number', 'like', "$year/%")->latest()->first();
        
        if ($latestInvoice) {
            $lastNumber = intval(explode('/', $latestInvoice->invoice_number)[1]);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . '/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a QR payment code for the invoice.
     *
     * @return string
     */
    public function generateQrPaymentCode()
    {
        // Simplified QR code generation (in real impl would use QR library)
        // Format: SPD*1.0*ACC:CZ123456789/0100*AM:1000.00*CC:CZK*...
        $account = str_replace(' ', '', $this->bank_account);
        $amount = number_format($this->total_with_vat, 2, '.', '');
        $qrData = "SPD*1.0*ACC:{$account}*AM:{$amount}*CC:{$this->currency}*MSG:{$this->invoice_number}";
        
        $this->qr_payment_code = $qrData;
        $this->save();
        
        return $qrData;
    }
}
