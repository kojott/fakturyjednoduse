<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->invoices()
                  ->with('customer');
        
        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')
                    ->paginate(15);
                    
        // Statistics for dashboard
        $stats = [
            'total_unpaid' => auth()->user()->invoices()->where('status', 'new')->sum('total_with_vat'),
            'total_paid' => auth()->user()->invoices()->where('status', 'paid')->sum('total_with_vat'),
            'overdue_count' => auth()->user()->invoices()
                               ->where('status', 'new')
                               ->where('due_date', '<', now())
                               ->count(),
        ];
        
        return view('invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = auth()->user()->customers()->orderBy('company_name')->get();
        return view('invoices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:regular,proforma,advance',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'currency' => 'required|string|size:3',
            'note' => 'nullable|string',
            'payment_method' => 'required|string',
            'bank_account' => 'required_if:payment_method,bank_transfer|nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|in:0,10,15,21',
        ]);
        
        // Ensure customer belongs to authenticated user
        $customer = Customer::findOrFail($validated['customer_id']);
        
        if ($customer->user_id !== auth()->id()) {
            return back()->withErrors(['customer_id' => 'Vybraný zákazník nepatří přihlášenému uživateli.']);
        }
        
        // Create invoice
        $invoice = Invoice::create([
            'user_id' => auth()->id(),
            'customer_id' => $validated['customer_id'],
            'type' => $validated['type'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'currency' => $validated['currency'],
            'note' => $validated['note'],
            'payment_method' => $validated['payment_method'],
            'bank_account' => $validated['bank_account'],
        ]);
        
        // Create invoice items
        foreach ($validated['items'] as $item) {
            $totalWithoutVat = $item['quantity'] * $item['unit_price'];
            $totalWithVat = $totalWithoutVat * (1 + $item['vat_rate'] / 100);
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'total_without_vat' => $totalWithoutVat,
                'total_with_vat' => $totalWithVat,
            ]);
        }
        
        // Generate QR payment code if bank transfer
        if ($validated['payment_method'] === 'bank_transfer') {
            $invoice->generateQrPaymentCode();
        }
        
        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'Faktura byla úspěšně vytvořena.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load('customer', 'items');
        
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        // Cannot edit paid or cancelled invoices
        if ($invoice->status !== 'new') {
            return back()->withErrors(['edit' => 'Nelze upravovat již zaplacenou nebo stornovanou fakturu.']);
        }
        
        $invoice->load('customer', 'items');
        $customers = auth()->user()->customers()->orderBy('company_name')->get();
        
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        // Cannot edit paid or cancelled invoices
        if ($invoice->status !== 'new') {
            return back()->withErrors(['edit' => 'Nelze upravovat již zaplacenou nebo stornovanou fakturu.']);
        }
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'currency' => 'required|string|size:3',
            'note' => 'nullable|string',
            'payment_method' => 'required|string',
            'bank_account' => 'required_if:payment_method,bank_transfer|nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|in:0,10,15,21',
        ]);
        
        // Ensure customer belongs to authenticated user
        $customer = Customer::findOrFail($validated['customer_id']);
        
        if ($customer->user_id !== auth()->id()) {
            return back()->withErrors(['customer_id' => 'Vybraný zákazník nepatří přihlášenému uživateli.']);
        }
        
        // Update invoice basic info
        $invoice->update([
            'customer_id' => $validated['customer_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'currency' => $validated['currency'],
            'note' => $validated['note'],
            'payment_method' => $validated['payment_method'],
            'bank_account' => $validated['bank_account'],
        ]);
        
        // Get current item IDs
        $currentItemIds = $invoice->items->pluck('id')->toArray();
        $updatedItemIds = [];
        
        // Update/create items
        foreach ($validated['items'] as $itemData) {
            $totalWithoutVat = $itemData['quantity'] * $itemData['unit_price'];
            $totalWithVat = $totalWithoutVat * (1 + $itemData['vat_rate'] / 100);
            
            $itemAttributes = [
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'vat_rate' => $itemData['vat_rate'],
                'total_without_vat' => $totalWithoutVat,
                'total_with_vat' => $totalWithVat,
            ];
            
            if (isset($itemData['id']) && !empty($itemData['id'])) {
                // Update existing item
                $item = InvoiceItem::findOrFail($itemData['id']);
                
                // Check if item belongs to the invoice
                if ($item->invoice_id !== $invoice->id) {
                    continue;
                }
                
                $item->update($itemAttributes);
                $updatedItemIds[] = $item->id;
            } else {
                // Create new item
                $item = $invoice->items()->create(array_merge($itemAttributes, [
                    'invoice_id' => $invoice->id,
                ]));
                $updatedItemIds[] = $item->id;
            }
        }
        
        // Delete items that were not updated or created
        $itemsToDelete = array_diff($currentItemIds, $updatedItemIds);
        InvoiceItem::whereIn('id', $itemsToDelete)->delete();
        
        // Regenerate QR payment code if bank transfer
        if ($validated['payment_method'] === 'bank_transfer') {
            $invoice->generateQrPaymentCode();
        }
        
        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'Faktura byla úspěšně aktualizována.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        
        // Cannot delete paid invoices
        if ($invoice->status === 'paid') {
            return back()->withErrors(['delete' => 'Nelze smazat již zaplacenou fakturu.']);
        }
        
        // Change status to cancelled instead of deleting
        $invoice->status = 'cancelled';
        $invoice->save();
        
        return redirect()->route('invoices.index')
                        ->with('success', 'Faktura byla stornována.');
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'new') {
            $invoice->markAsPaid();
            return redirect()->route('invoices.show', $invoice)
                            ->with('success', 'Faktura byla označena jako zaplacená.');
        }
        
        return back()->withErrors(['status' => 'Fakturu nelze označit jako zaplacenou.']);
    }

    /**
     * Convert the invoice to a regular invoice.
     */
    public function convertToRegular(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->type !== 'regular' && $invoice->status === 'paid') {
            $newInvoice = $invoice->replicate();
            $newInvoice->type = 'regular';
            $newInvoice->status = 'new';
            $newInvoice->invoice_number = null; // Will be autogenerated
            $newInvoice->save();
            
            // Copy invoice items
            foreach ($invoice->items as $item) {
                $newItem = $item->replicate();
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }
            
            return redirect()->route('invoices.show', $newInvoice)
                            ->with('success', 'Zálohová faktura byla převedena na běžnou fakturu.');
        }
        
        return back()->withErrors(['convert' => 'Fakturu nelze převést na běžnou fakturu.']);
    }

    /**
     * Export the invoice to PDF.
     */
    public function exportPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load('customer', 'items', 'user');
        
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        
        // Sanitize the filename by replacing slashes with hyphens
        $sanitizedInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);
        
        return $pdf->download('faktura-' . $sanitizedInvoiceNumber . '.pdf');
    }
}
