@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Detail zákazníka') }}</span>
                        <div>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ __('Upravit') }}
                            </a>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('Zpět na seznam') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">{{ __('Informace o zákazníkovi') }}</div>
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>{{ __('Název společnosti') }}:</th>
                                            <td>{{ $customer->company_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('IČO') }}:</th>
                                            <td>{{ $customer->ico }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('DIČ') }}:</th>
                                            <td>{{ $customer->dic ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Adresa') }}:</th>
                                            <td>{{ $customer->address }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Město') }}:</th>
                                            <td>{{ $customer->city }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('PSČ') }}:</th>
                                            <td>{{ $customer->zip_code }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Země') }}:</th>
                                            <td>{{ $customer->country }}</td>
                                        </tr>
                                        @if ($customer->note)
                                        <tr>
                                            <th>{{ __('Poznámka') }}:</th>
                                            <td>{{ $customer->note }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">{{ __('Statistika') }}</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-primary text-white mb-3">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ __('Počet faktur') }}</h5>
                                                    <p class="card-text display-6">
                                                        {{ $customer->invoices()->count() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-success text-white mb-3">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ __('Celková částka') }}</h5>
                                                    <p class="card-text display-6">
                                                        {{ number_format($customer->invoices()->sum('total_with_vat'), 2, ',', ' ') }} Kč
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>{{ __('Faktury zákazníka') }}</span>
                            <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> {{ __('Vytvořit fakturu') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Číslo faktury') }}</th>
                                            <th>{{ __('Datum vystavení') }}</th>
                                            <th>{{ __('Datum splatnosti') }}</th>
                                            <th>{{ __('Částka') }}</th>
                                            <th>{{ __('Stav') }}</th>
                                            <th>{{ __('Akce') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->invoices()->orderBy('created_at', 'desc')->get() as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice) }}">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                                @if ($invoice->type !== 'regular')
                                                    <span class="badge bg-info">
                                                        {{ $invoice->type === 'proforma' ? 'Proforma' : 'Zálohová' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $invoice->issue_date->format('d.m.Y') }}</td>
                                            <td>
                                                {{ $invoice->due_date->format('d.m.Y') }}
                                                @if ($invoice->status === 'new' && $invoice->due_date < now())
                                                    <span class="badge bg-danger">{{ __('Po splatnosti') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($invoice->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                            <td>
                                                @if ($invoice->status === 'new')
                                                    <span class="badge bg-warning">{{ __('Nezaplaceno') }}</span>
                                                @elseif ($invoice->status === 'paid')
                                                    <span class="badge bg-success">{{ __('Zaplaceno') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Stornováno') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($invoice->status === 'new')
                                                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('invoices.export-pdf', $invoice) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">{{ __('Žádné faktury nebyly nalezeny') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection