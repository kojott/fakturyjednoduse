@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Vytvořit fakturu') }}</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('invoices.store') }}" id="invoice-form">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>{{ __('Základní informace') }}</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label required">{{ __('Zákazník') }}</label>
                                    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="">{{ __('Vyberte zákazníka') }}</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->company_name }} ({{ $customer->ico }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label required">{{ __('Typ faktury') }}</label>
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="regular" {{ old('type') == 'regular' ? 'selected' : '' }}>{{ __('Běžná faktura') }}</option>
                                        <option value="proforma" {{ old('type') == 'proforma' ? 'selected' : '' }}>{{ __('Proforma faktura') }}</option>
                                        <option value="advance" {{ old('type') == 'advance' ? 'selected' : '' }}>{{ __('Zálohová faktura') }}</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="issue_date" class="form-label required">{{ __('Datum vystavení') }}</label>
                                    <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                    @error('issue_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label required">{{ __('Datum splatnosti') }}</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required>
                                    @error('due_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label required">{{ __('Způsob platby') }}</label>
                                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bankovní převod') }}</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>{{ __('Hotovost') }}</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>{{ __('Platební karta') }}</option>
                                    </select>
                                    @error('payment_method')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 bank-account-group">
                                    <label for="bank_account" class="form-label required">{{ __('Bankovní účet') }}</label>
                                    <input type="text" name="bank_account" id="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account') }}" placeholder="123456789/0100">
                                    @error('bank_account')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label required">{{ __('Měna') }}</label>
                                    <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                        <option value="CZK" {{ old('currency') == 'CZK' ? 'selected' : '' }}>{{ __('CZK - Česká koruna') }}</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>{{ __('EUR - Euro') }}</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>{{ __('USD - Americký dolar') }}</option>
                                    </select>
                                    @error('currency')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="note" class="form-label">{{ __('Poznámka') }}</label>
                                    <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note') }}</textarea>
                                    @error('note')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 mt-4">
                            <div class="col-md-12">
                                <h5>{{ __('Položky faktury') }}</h5>
                            </div>
                        </div>

                        <div class="invoice-items">
                            <div class="card mb-3 invoice-item" data-index="0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="items[0][description]" class="form-label required">{{ __('Popis') }}</label>
                                                <input type="text" name="items[0][description]" id="items[0][description]" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="items[0][quantity]" class="form-label required">{{ __('Množství') }}</label>
                                                <input type="number" name="items[0][quantity]" id="items[0][quantity]" class="form-control item-quantity" step="0.01" min="0.01" value="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="items[0][unit_price]" class="form-label required">{{ __('Cena/ks') }}</label>
                                                <input type="number" name="items[0][unit_price]" id="items[0][unit_price]" class="form-control item-price" step="0.01" min="0" value="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="items[0][vat_rate]" class="form-label required">{{ __('Sazba DPH %') }}</label>
                                                <select name="items[0][vat_rate]" id="items[0][vat_rate]" class="form-select item-vat" required>
                                                    <option value="0">0%</option>
                                                    <option value="10">10%</option>
                                                    <option value="15">15%</option>
                                                    <option value="21" selected>21%</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <!-- Placeholder for additioanl fields if needed -->
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Cena bez DPH') }}</label>
                                                <div class="item-total-without-vat">0,00</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Cena s DPH') }}</label>
                                                <div class="item-total-with-vat">0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-item" disabled>
                                                <i class="fas fa-trash"></i> {{ __('Odstranit položku') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success add-item">
                                    <i class="fas fa-plus"></i> {{ __('Přidat položku') }}
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8 text-end">
                                                <strong>{{ __('Celkem bez DPH:') }}</strong>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <strong id="total-without-vat">0,00</strong> <span class="currency">CZK</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 text-end">
                                                <strong>{{ __('DPH:') }}</strong>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <strong id="total-vat">0,00</strong> <span class="currency">CZK</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 text-end">
                                                <strong>{{ __('Celkem s DPH:') }}</strong>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <strong id="total-with-vat">0,00</strong> <span class="currency">CZK</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Vytvořit fakturu') }}
                                </button>
                                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                                    {{ __('Zrušit') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide bank account field based on payment method
        const paymentMethodSelect = document.getElementById('payment_method');
        const bankAccountGroup = document.querySelector('.bank-account-group');
        
        function toggleBankAccountField() {
            if (paymentMethodSelect.value === 'bank_transfer') {
                bankAccountGroup.style.display = 'block';
                document.getElementById('bank_account').setAttribute('required', 'required');
            } else {
                bankAccountGroup.style.display = 'none';
                document.getElementById('bank_account').removeAttribute('required');
            }
        }
        
        paymentMethodSelect.addEventListener('change', toggleBankAccountField);
        toggleBankAccountField();
        
        // Currency selector
        const currencySelect = document.getElementById('currency');
        const currencyLabels = document.querySelectorAll('.currency');
        
        currencySelect.addEventListener('change', function() {
            currencyLabels.forEach(label => {
                label.textContent = currencySelect.value;
            });
        });
        
        // Add new item
        const addItemButton = document.querySelector('.add-item');
        const invoiceItems = document.querySelector('.invoice-items');
        
        let itemIndex = 1;
        
        addItemButton.addEventListener('click', function() {
            const itemTemplate = document.querySelector('.invoice-item').cloneNode(true);
            
            // Update indices
            itemTemplate.dataset.index = itemIndex;
            
            const inputs = itemTemplate.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${itemIndex}]`));
                    input.setAttribute('id', name.replace(/\[\d+\]/, `[${itemIndex}]`));
                }
            });
            
            // Enable remove button
            itemTemplate.querySelector('.remove-item').disabled = false;
            
            // Clear values
            itemTemplate.querySelector('input[name$="[description]"]').value = '';
            itemTemplate.querySelector('input[name$="[quantity]"]').value = '1';
            itemTemplate.querySelector('input[name$="[unit_price]"]').value = '0';
            
            // Add to DOM
            invoiceItems.appendChild(itemTemplate);
            
            // Increment index
            itemIndex++;
            
            // Re-attach event listeners
            attachItemListeners();
            updateTotals();
        });
        
        // Remove item
        function attachItemListeners() {
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const item = this.closest('.invoice-item');
                    item.remove();
                    updateTotals();
                });
            });
            
            document.querySelectorAll('.item-quantity, .item-price, .item-vat').forEach(input => {
                input.addEventListener('input', function() {
                    updateItemTotals(this.closest('.invoice-item'));
                    updateTotals();
                });
                
                // Initialize values
                if (input.classList.contains('item-quantity') || input.classList.contains('item-price')) {
                    const item = input.closest('.invoice-item');
                    updateItemTotals(item);
                }
            });
        }
        
        // Update item totals
        function updateItemTotals(item) {
            const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
            const unitPrice = parseFloat(item.querySelector('.item-price').value) || 0;
            const vatRate = parseFloat(item.querySelector('.item-vat').value) || 0;
            
            const totalWithoutVat = quantity * unitPrice;
            const totalWithVat = totalWithoutVat * (1 + vatRate / 100);
            
            item.querySelector('.item-total-without-vat').textContent = totalWithoutVat.toFixed(2).replace('.', ',');
            item.querySelector('.item-total-with-vat').textContent = totalWithVat.toFixed(2).replace('.', ',');
        }
        
        // Update invoice totals
        function updateTotals() {
            let totalWithoutVat = 0;
            let totalWithVat = 0;
            
            document.querySelectorAll('.invoice-item').forEach(item => {
                const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
                const unitPrice = parseFloat(item.querySelector('.item-price').value) || 0;
                const vatRate = parseFloat(item.querySelector('.item-vat').value) || 0;
                
                const itemTotalWithoutVat = quantity * unitPrice;
                const itemTotalWithVat = itemTotalWithoutVat * (1 + vatRate / 100);
                
                totalWithoutVat += itemTotalWithoutVat;
                totalWithVat += itemTotalWithVat;
            });
            
            const totalVat = totalWithVat - totalWithoutVat;
            
            document.getElementById('total-without-vat').textContent = totalWithoutVat.toFixed(2).replace('.', ',');
            document.getElementById('total-vat').textContent = totalVat.toFixed(2).replace('.', ',');
            document.getElementById('total-with-vat').textContent = totalWithVat.toFixed(2).replace('.', ',');
        }
        
        // Initialize
        attachItemListeners();
        updateTotals();
    });
</script>
@endpush
@endsection