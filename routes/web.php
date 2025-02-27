<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/validate-ico', [CustomerController::class, 'validateIco'])
         ->name('customers.validate-ico');
    
    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])
         ->name('invoices.mark-as-paid');
    Route::post('/invoices/{invoice}/convert-to-regular', [InvoiceController::class, 'convertToRegular'])
         ->name('invoices.convert-to-regular');
    Route::get('/invoices/{invoice}/export-pdf', [InvoiceController::class, 'exportPdf'])
         ->name('invoices.export-pdf');
});

require __DIR__.'/auth.php';
