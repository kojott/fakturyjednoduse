@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Faktury') }}</span>
                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">{{ __('Vytvořit fakturu') }}</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Nezaplacené faktury') }}</h5>
                                    <p class="card-text display-6">
                                        {{ number_format($stats['total_unpaid'], 2, ',', ' ') }} Kč
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Zaplacené faktury') }}</h5>
                                    <p class="card-text display-6">
                                        {{ number_format($stats['total_paid'], 2, ',', ' ') }} Kč
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Po splatnosti') }}</h5>
                                    <p class="card-text display-6">
                                        {{ $stats['overdue_count'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">{{ __('Filtrování') }}</div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('invoices.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">{{ __('Stav') }}</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="">{{ __('Všechny') }}</option>
                                                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('Nezaplacené') }}</option>
                                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Zaplacené') }}</option>
                                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Stornované') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_from" class="form-label">{{ __('Datum od') }}</label>
                                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_to" class="form-label">{{ __('Datum do') }}</label>
                                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="mb-3 w-100">
                                            <button type="submit" class="btn btn-primary w-100">{{ __('Filtrovat') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Číslo faktury') }}</th>
                                    <th>{{ __('Zákazník') }}</th>
                                    <th>{{ __('Datum vystavení') }}</th>
                                    <th>{{ __('Datum splatnosti') }}</th>
                                    <th>{{ __('Částka') }}</th>
                                    <th>{{ __('Stav') }}</th>
                                    <th>{{ __('Akce') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
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
                                    <td>{{ $invoice->customer->company_name }}</td>
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
                                                <form action="{{ route('invoices.mark-as-paid', $invoice) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('{{ __('Opravdu chcete označit fakturu jako zaplacenou?') }}')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('invoices.export-pdf', $invoice) }}" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('Žádné faktury nebyly nalezeny') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $invoices->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection