<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica';
            font-style: normal;
            font-weight: normal;
            font-size: 8px;
        }
        body {
            padding: 0.4cm 0.3cm;
        }
        header p, footer p {
            text-align: center;
        }

        .identifier {
            margin: 6px 0;
            padding: 2px 0;
            border-top: 1px dashed black;
            border-bottom: 1px dashed black;
        }

        table {
            width: 100%;
        }

        footer {
            margin-top: 6px;
        }

    </style>
</head>
<body>
    <header>
        <p>CANDI - KAB SIDOARJO</p>
        <p>Jl. Raya Sepande No. 72,<br> Kec Candi, Kab. Sidoarjo, 61271</p>
        <div class="identifier">
            <table>
                <tr>
                    <td>
                        {{ now()->format('d.m.Y-H:i') }}
                    </td>
                    <td style="text-align: center">
                        {{ $transaction->type === 'purchase' ? 'Pembelian' : 'Penjualan' }}
                    </td>
                    <td style="text-align: right">
                        {{ str_replace('TRX', 'INV', $transaction->number) }}
                    </td>
                </tr>
            </table>
        </div>
    </header>
    <div class="content">
        <table>
            <tr>
                <th>No.</th>
                <th>Sampah</th>
                <th>Berat (Kg)</th>
                <th style="text-align: right">Sub Total</th>
            </tr>
            @foreach ($transaction->transactionWastes as $transactionWaste)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td>{{ $transactionWaste->waste->name }}</td>
                    <td style="text-align: center">{{ $transactionWaste->qty_in_kg }}</td>
                    <td style="text-align: right">{{ $transactionWaste->sub_total_price }}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td style="font-weight: bold; text-align: center; border-top: 1px dashed black; padding-top:4px">Total</td>
                <td style="font-weight: bold; text-align: right; border-top: 1px dashed black; padding-top:4px">{{ $transaction->total_price }}</td>
            </tr>
        </table>


    </div>
    <footer>
        <p>Layanan Pelanggan 081231193882</p>
    </footer>
</body>
</html>
