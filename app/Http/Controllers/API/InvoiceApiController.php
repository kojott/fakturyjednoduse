<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->invoices()
                  ->with('customer')
                  ->orderBy('created_at', 'desc');
        
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
        
        $invoices = $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function show(Invoice $invoice)
    {
        // Check if invoice belongs to authenticated user
        if ($invoice->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice',
            ], 403);
        }
        
        $invoice->load('customer', 'items');
        
        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Ensure customer belongs to authenticated user
        $customer = Customer::findOrFail($request->customer_id);
        
        if ($customer->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected customer does not belong to authenticated user',
            ], 403);
        }
        
        // Create invoice
        $invoice = Invoice::create([
            'user_id' => auth()->id(),
            'customer_id' => $request->customer_id,
            'type' => $request->type,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'currency' => $request->currency,
            'note' => $request->note,
            'payment_method' => $request->payment_method,
            'bank_account' => $request->bank_account,
        ]);
        
        // Create invoice items
        foreach ($request->items as $item) {
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
        if ($request->payment_method === 'bank_transfer') {
            $invoice->generateQrPaymentCode();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice successfully created',
            'data' => $invoice->load('customer', 'items'),
        ], 201);
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Check if invoice belongs to authenticated user
        if ($invoice->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice',
            ], 403);
        }
        
        // Cannot edit paid or cancelled invoices
        if ($invoice->status !== 'new') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit paid or cancelled invoice',
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date|after_or_equal:issue_date',
            'currency' => 'sometimes|required|string|size:3',
            'note' => 'nullable|string',
            'payment_method' => 'sometimes|required|string',
            'bank_account' => 'required_if:payment_method,bank_transfer|nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|in:0,10,15,21',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Update invoice basic info
        $updateData = $request->only([
            'customer_id',
            'issue_date',
            'due_date',
            'currency',
            'note',
            'payment_method',
            'bank_account',
        ]);
        
        // Ensure customer belongs to authenticated user if provided
        if (isset($updateData['customer_id'])) {
            $customer = Customer::findOrFail($updateData['customer_id']);
            
            if ($customer->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected customer does not belong to authenticated user',
                ], 403);
            }
        }
        
        $invoice->update($updateData);
        
        // Update items if provided
        if ($request->has('items')) {
            // Get current item IDs
            $currentItemIds = $invoice->items->pluck('id')->toArray();
            $updatedItemIds = [];
            
            foreach ($request->items as $itemData) {
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
        }
        
        // Regenerate QR payment code if bank transfer
        if ($invoice->payment_method === 'bank_transfer') {
            $invoice->generateQrPaymentCode();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice successfully updated',
            'data' => $invoice->fresh()->load('customer', 'items'),
        ]);
    }

    public function markAsPaid(Invoice $invoice)
    {
        // Check if invoice belongs to authenticated user
        if ($invoice->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice',
            ], 403);
        }
        
        if ($invoice->status === 'new') {
            $invoice->markAsPaid();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid',
                'data' => $invoice,
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invoice cannot be marked as paid',
        ], 400);
    }
}