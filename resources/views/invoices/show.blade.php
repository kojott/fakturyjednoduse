@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            {{ __('Detail faktury') }} {{ $invoice->invoice_number }}
                            @if ($invoice->type !== 'regular')
                                <span class="badge bg-info">
                                    {{ $invoice->type === 'proforma' ? 'Proforma' : 'Zálohová' }}
                                </span>
                            @endif
                        </span>
                        <div>
                            @if ($invoice->status === 'new')
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> {{ __('Upravit') }}
                                </a>
                                <form action="{{ route('invoices.mark-as-paid', $invoice) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('{{ __('Opravdu chcete označit fakturu jako zaplacenou?') }}')">
                                        <i class="fas fa-check"></i> {{ __('Označit jako zaplacenou') }}
                                    </button>
                                </form>
                            @endif
                            
                            @if ($invoice->type !== 'regular' && $invoice->status === 'paid')
                                <form action="{{ route('invoices.convert-to-regular', $invoice) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info" onclick="return confirm('{{ __('Opravdu chcete převést na běžnou fakturu?') }}')">
                                        <i class="fas fa-exchange-alt"></i> {{ __('Převést na běžnou fakturu') }}
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('invoices.export-pdf', $invoice) }}" class="btn btn-secondary">
                                <i class="fas fa-file-pdf"></i> {{ __('Stáhnout PDF') }}
                            </a>
                            
                            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
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

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">{{ __('Dodavatel') }}</div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>{{ auth()->user()->name }}</strong></p>
                                    <p class="mb-1">{{ config('app.user_address', 'Adresa společnosti') }}</p>
                                    <p class="mb-1">{{ config('app.user_city', 'Město, PSČ') }}</p>
                                    <p class="mb-1">IČO: {{ config('app.user_ico', '12345678') }}</p>
                                    <p class="mb-1">DIČ: {{ config('app.user_dic', 'CZ12345678') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">{{ __('Odběratel') }}</div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>{{ $invoice->customer->company_name }}</strong></p>
                                    <p class="mb-1">{{ $invoice->customer->address }}</p>
                                    <p class="mb-1">{{ $invoice->customer->city }}, {{ $invoice->customer->zip_code }}</p>
                                    <p class="mb-1">{{ $invoice->customer->country }}</p>
                                    <p class="mb-1">IČO: {{ $invoice->customer->ico }}</p>
                                    @if ($invoice->customer->dic)
                                        <p class="mb-1">DIČ: {{ $invoice->customer->dic }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">{{ __('Informace o faktuře') }}</div>
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>{{ __('Číslo faktury') }}:</th>
                                            <td>{{ $invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Datum vystavení') }}:</th>
                                            <td>{{ $invoice->issue_date->format('d.m.Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Datum splatnosti') }}:</th>
                                            <td>
                                                {{ $invoice->due_date->format('d.m.Y') }}
                                                @if ($invoice->status === 'new' && $invoice->due_date < now())
                                                    <span class="badge bg-danger">{{ __('Po splatnosti') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Způsob platby') }}:</th>
                                            <td>
                                                @if ($invoice->payment_method === 'bank_transfer')
                                                    {{ __('Bankovní převod') }}
                                                @elseif ($invoice->payment_method === 'cash')
                                                    {{ __('Hotovost') }}
                                                @elseif ($invoice->payment_method === 'card')
                                                    {{ __('Platební karta') }}
                                                @endif
                                            </td>
                                        </tr>
                                        @if ($invoice->payment_method === 'bank_transfer')
                                            <tr>
                                                <th>{{ __('Bankovní účet') }}:</th>
                                                <td>{{ $invoice->bank_account }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Variabilní symbol') }}:</th>
                                                <td>{{ str_replace('/', '', $invoice->invoice_number) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th>{{ __('Stav') }}:</th>
                                            <td>
                                                @if ($invoice->status === 'new')
                                                    <span class="badge bg-warning">{{ __('Nezaplaceno') }}</span>
                                                @elseif ($invoice->status === 'paid')
                                                    <span class="badge bg-success">{{ __('Zaplaceno') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Stornováno') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if ($invoice->note)
                                <div class="card mb-3">
                                    <div class="card-header">{{ __('Poznámka') }}</div>
                                    <div class="card-body">
                                        <p>{{ $invoice->note }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if ($invoice->payment_method === 'bank_transfer' && $invoice->qr_payment_code)
                                <div class="card">
                                    <div class="card-header">{{ __('QR Platba') }}</div>
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            {!! QrCode::size(150)->generate($invoice->qr_payment_code) !!}
                                        </div>
                                        <small class="text-muted">{{ __('Naskenujte QR kód pro rychlou platbu') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">{{ __('Položky faktury') }}</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Popis') }}</th>
                                            <th class="text-end">{{ __('Množství') }}</th>
                                            <th class="text-end">{{ __('Cena/ks') }}</th>
                                            <th class="text-end">{{ __('DPH %') }}</th>
                                            <th class="text-end">{{ __('Cena bez DPH') }}</th>
                                            <th class="text-end">{{ __('Cena s DPH') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->items as $item)
                                            <tr>
                                                <td>{{ $item->description }}</td>
                                                <td class="text-end">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                                                <td class="text-end">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                                <td class="text-end">{{ $item->vat_rate }}%</td>
                                                <td class="text-end">{{ number_format($item->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                                <td class="text-end">{{ number_format($item->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">{{ __('Celkem bez DPH:') }}</th>
                                            <th class="text-end">{{ number_format($invoice->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-end">{{ __('DPH:') }}</th>
                                            <th class="text-end">{{ number_format($invoice->total_with_vat - $invoice->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-end">{{ __('Celkem s DPH:') }}</th>
                                            <th colspan="2" class="text-end">{{ number_format($invoice->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</th>
                                        </tr>
                                    </tfoot>
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