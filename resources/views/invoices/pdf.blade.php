<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        .invoice-header {
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-subtitle {
            font-size: 14px;
            color: #555;
        }
        .row {
            display: flex;
            margin-bottom: 20px;
        }
        .col {
            flex: 1;
            padding: 0 10px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ddd;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 30px;
        }
        .summary-row {
            display: flex;
            padding: 5px 0;
        }
        .summary-label {
            flex: 2;
            text-align: right;
            padding-right: 10px;
            font-weight: bold;
        }
        .summary-value {
            flex: 1;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #777;
            text-align: center;
        }
        .qr-code {
            margin-top: 20px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 10px;
            border-radius: 3px;
            color: white;
            background-color: #007bff;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="invoice-title">
            @if ($invoice->type === 'regular')
                Faktura - daňový doklad č. {{ $invoice->invoice_number }}
            @elseif ($invoice->type === 'proforma')
                Proforma faktura č. {{ $invoice->invoice_number }}
            @else
                Zálohová faktura č. {{ $invoice->invoice_number }}
            @endif
        </div>
        
        <div class="invoice-subtitle">
            @if ($invoice->status === 'paid')
                <span class="badge badge-success">ZAPLACENO</span>
            @elseif ($invoice->status === 'cancelled')
                <span class="badge badge-warning">STORNOVÁNO</span>
            @elseif ($invoice->due_date < date('Y-m-d'))
                <span class="badge badge-warning">PO SPLATNOSTI</span>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="section-title">Dodavatel</div>
            <div>{{ $invoice->user->name }}</div>
            <div>{{ config('app.user_address', 'Adresa společnosti') }}</div>
            <div>{{ config('app.user_city', 'Město, PSČ') }}</div>
            <div>IČO: {{ config('app.user_ico', '12345678') }}</div>
            <div>DIČ: {{ config('app.user_dic', 'CZ12345678') }}</div>
        </div>
        <div class="col">
            <div class="section-title">Odběratel</div>
            <div>{{ $invoice->customer->company_name }}</div>
            <div>{{ $invoice->customer->address }}</div>
            <div>{{ $invoice->customer->city }}, {{ $invoice->customer->zip_code }}</div>
            <div>IČO: {{ $invoice->customer->ico }}</div>
            @if ($invoice->customer->dic)
                <div>DIČ: {{ $invoice->customer->dic }}</div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="section-title">Platební údaje</div>
            <div>Datum vystavení: {{ $invoice->issue_date->format('d.m.Y') }}</div>
            <div>Datum splatnosti: {{ $invoice->due_date->format('d.m.Y') }}</div>
            <div>Forma úhrady: 
                @if ($invoice->payment_method === 'bank_transfer')
                    Bankovní převod
                @elseif ($invoice->payment_method === 'cash')
                    Hotovost
                @elseif ($invoice->payment_method === 'card')
                    Platební karta
                @endif
            </div>
            @if ($invoice->payment_method === 'bank_transfer')
                <div>Bankovní účet: {{ $invoice->bank_account }}</div>
                <div>Variabilní symbol: {{ str_replace('/', '', $invoice->invoice_number) }}</div>
            @endif
        </div>
        <div class="col">
            @if ($invoice->note)
                <div class="section-title">Poznámka</div>
                <div>{{ $invoice->note }}</div>
            @endif
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Popis</th>
                <th>Množství</th>
                <th>Jedn. cena</th>
                <th>DPH (%)</th>
                <th>Cena bez DPH</th>
                <th>Cena s DPH</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                    <td>{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td>{{ $item->vat_rate }}%</td>
                    <td class="text-right">{{ number_format($item->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td class="text-right">{{ number_format($item->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Celkem bez DPH:</div>
            <div class="summary-value">{{ number_format($invoice->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">DPH:</div>
            <div class="summary-value">{{ number_format($invoice->total_with_vat - $invoice->total_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label" style="font-size: 16px;">Celkem k úhradě:</div>
            <div class="summary-value" style="font-size: 16px; font-weight: bold;">{{ number_format($invoice->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
    </div>

    @if ($invoice->payment_method === 'bank_transfer' && $invoice->qr_payment_code)
        <div class="qr-code">
            <!-- QR code generation disabled - requires Imagick extension -->
            <div style="border: 1px solid #ddd; padding: 20px; text-align: center; width: 150px; margin: 0 auto;">
                QR kód pro platbu<br>
                <small>(Vyžaduje Imagick extension)</small>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>
            Faktura byla vystavena v souladu s § 26 zákona č. 235/2004 Sb. o dani z přidané hodnoty.
            @if ($invoice->type !== 'regular')
                <br>Toto není daňový doklad.
            @endif
        </p>
        <p>© {{ date('Y') }} Fakturační systém - vytvořeno v Laravel</p>
    </div>
</body>
</html>