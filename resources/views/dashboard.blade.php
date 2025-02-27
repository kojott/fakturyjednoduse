@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Nezaplacené faktury') }}</h5>
                                    <p class="card-text display-6">
                                        {{ number_format(auth()->user()->invoices()->where('status', 'new')->sum('total_with_vat'), 2, ',', ' ') }} Kč
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Zaplacené faktury') }}</h5>
                                    <p class="card-text display-6">
                                        {{ number_format(auth()->user()->invoices()->where('status', 'paid')->sum('total_with_vat'), 2, ',', ' ') }} Kč
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Po splatnosti') }}</h5>
                                    <p class="card-text display-6">
                                        {{ auth()->user()->invoices()->where('status', 'new')->where('due_date', '<', now())->count() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>{{ __('Poslední faktury') }}</span>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-primary">{{ __('Zobrazit všechny') }}</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Číslo') }}</th>
                                                    <th>{{ __('Zákazník') }}</th>
                                                    <th>{{ __('Částka') }}</th>
                                                    <th>{{ __('Stav') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(auth()->user()->invoices()->with('customer')->latest()->limit(5)->get() as $invoice)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('invoices.show', $invoice) }}">
                                                            {{ $invoice->invoice_number }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $invoice->customer->company_name }}</td>
                                                    <td>{{ number_format($invoice->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                                    <td>
                                                        @if($invoice->status === 'new')
                                                            @if($invoice->due_date < now())
                                                                <span class="badge bg-danger">{{ __('Po splatnosti') }}</span>
                                                            @else
                                                                <span class="badge bg-warning">{{ __('Nezaplaceno') }}</span>
                                                            @endif
                                                        @elseif($invoice->status === 'paid')
                                                            <span class="badge bg-success">{{ __('Zaplaceno') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('Stornováno') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>{{ __('Poslední zákazníci') }}</span>
                                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">{{ __('Zobrazit všechny') }}</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Společnost') }}</th>
                                                    <th>{{ __('IČO') }}</th>
                                                    <th>{{ __('Počet faktur') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(auth()->user()->customers()->withCount('invoices')->latest()->limit(5)->get() as $customer)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('customers.show', $customer) }}">
                                                            {{ $customer->company_name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $customer->ico }}</td>
                                                    <td>{{ $customer->invoices_count }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col text-center">
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> {{ __('Vytvořit novou fakturu') }}
                            </a>
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-user-plus"></i> {{ __('Přidat zákazníka') }}
                            </a>
                        </div>
                    </div>
                </div>
    </div>
</div>
@endsection
