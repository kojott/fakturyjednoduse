@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Upravit zákazníka') }}</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ico" class="form-label required">{{ __('IČO') }}</label>
                                    <input type="text" name="ico" id="ico" class="form-control @error('ico') is-invalid @enderror" value="{{ old('ico', $customer->ico) }}" required>
                                    @error('ico')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Při změně IČO se systém pokusí dohledat aktuální údaje v ARES.') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dic" class="form-label">{{ __('DIČ') }}</label>
                                    <input type="text" name="dic" id="dic" class="form-control @error('dic') is-invalid @enderror" value="{{ old('dic', $customer->dic) }}">
                                    @error('dic')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label required">{{ __('Název společnosti') }}</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $customer->company_name) }}" required>
                                    @error('company_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label required">{{ __('Adresa') }}</label>
                                    <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $customer->address) }}" required>
                                    @error('address')
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
                                    <label for="city" class="form-label required">{{ __('Město') }}</label>
                                    <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $customer->city) }}" required>
                                    @error('city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zip_code" class="form-label required">{{ __('PSČ') }}</label>
                                    <input type="text" name="zip_code" id="zip_code" class="form-control @error('zip_code') is-invalid @enderror" value="{{ old('zip_code', $customer->zip_code) }}" required>
                                    @error('zip_code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="country" class="form-label">{{ __('Země') }}</label>
                                    <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $customer->country) }}">
                                    @error('country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="note" class="form-label">{{ __('Poznámka') }}</label>
                                    <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note', $customer->note) }}</textarea>
                                    @error('note')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Uložit změny') }}
                                </button>
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
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
        const icoInput = document.getElementById('ico');
        const originalIco = '{{ $customer->ico }}';
        const companyNameInput = document.getElementById('company_name');
        const addressInput = document.getElementById('address');
        const cityInput = document.getElementById('city');
        const zipCodeInput = document.getElementById('zip_code');
        const icoFeedback = icoInput.nextElementSibling.nextElementSibling;
        
        // Add loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'spinner-border spinner-border-sm text-primary ms-2 d-none';
        loadingIndicator.setAttribute('role', 'status');
        loadingIndicator.innerHTML = '<span class="visually-hidden">Načítání...</span>';
        icoInput.parentNode.insertBefore(loadingIndicator, icoInput.nextSibling);
        
        // ARES lookup function using AJAX
        let timeout = null;
        icoInput.addEventListener('input', function() {
            // Clear any existing timeout
            if (timeout) {
                clearTimeout(timeout);
            }
            
            // Set a new timeout to prevent too many requests
            timeout = setTimeout(function() {
                const ico = icoInput.value.trim();
                
                // Only validate if ICO has changed from original value and is 8 digits
                if (ico !== originalIco && ico.length === 8) {
                    // Show loading indicator
                    loadingIndicator.classList.remove('d-none');
                    icoFeedback.textContent = 'Ověřuji IČO v systému ARES...';
                    
                    // Make AJAX call to validate ICO
                    fetch('{{ route('customers.validate-ico') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ico: ico })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loading indicator
                        loadingIndicator.classList.add('d-none');
                        
                        if (data.success) {
                            // Success - ask user if they want to update fields
                            icoFeedback.textContent = 'IČO bylo úspěšně ověřeno v systému ARES.';
                            icoFeedback.className = 'form-text text-success';
                            
                            if (confirm('Byly nalezeny aktuální údaje v systému ARES. Chcete aktualizovat údaje zákazníka?')) {
                                // Update fields with returned data
                                if (data.data.name) {
                                    companyNameInput.value = data.data.name;
                                }
                                
                                if (data.data.address) {
                                    addressInput.value = data.data.address;
                                }
                                
                                if (data.data.city) {
                                    cityInput.value = data.data.city;
                                }
                                
                                if (data.data.zip_code) {
                                    zipCodeInput.value = data.data.zip_code;
                                }
                            }
                        } else {
                            // Error - display error message
                            icoFeedback.textContent = data.message || 'Nepodařilo se ověřit IČO v systému ARES.';
                            icoFeedback.className = 'form-text text-danger';
                        }
                    })
                    .catch(error => {
                        // Hide loading indicator
                        loadingIndicator.classList.add('d-none');
                        
                        // Display error message
                        icoFeedback.textContent = 'Došlo k chybě při komunikaci se serverem.';
                        icoFeedback.className = 'form-text text-danger';
                        console.error('Error:', error);
                    });
                } else if (ico === originalIco) {
                    // Reset feedback if ICO is the original value
                    icoFeedback.textContent = 'Při změně IČO se systém pokusí dohledat aktuální údaje v ARES.';
                    icoFeedback.className = 'form-text text-muted';
                } else if (ico.length !== 8) {
                    // Reset feedback if ICO is not 8 digits
                    icoFeedback.textContent = 'Při změně IČO se systém pokusí dohledat aktuální údaje v ARES.';
                    icoFeedback.className = 'form-text text-muted';
                }
            }, 500); // 500ms delay to prevent too many requests
        });
    });
</script>
@endpush
@endsection