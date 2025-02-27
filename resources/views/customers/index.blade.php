@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Zákazníci') }}</span>
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">{{ __('Přidat zákazníka') }}</a>
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

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Společnost') }}</th>
                                    <th>{{ __('IČO') }}</th>
                                    <th>{{ __('DIČ') }}</th>
                                    <th>{{ __('Adresa') }}</th>
                                    <th>{{ __('Počet faktur') }}</th>
                                    <th>{{ __('Akce') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                <tr>
                                    <td>
                                        <a href="{{ route('customers.show', $customer) }}">
                                            {{ $customer->company_name }}
                                        </a>
                                    </td>
                                    <td>{{ $customer->ico }}</td>
                                    <td>{{ $customer->dic ?? '-' }}</td>
                                    <td>{{ $customer->city }}, {{ $customer->country }}</td>
                                    <td>{{ $customer->invoices_count ?? $customer->invoices()->count() }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Opravdu chcete smazat tohoto zákazníka?') }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('Žádní zákazníci nebyli nalezeni') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection