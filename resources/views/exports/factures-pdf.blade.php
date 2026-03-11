<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #c4226e; padding-bottom: 10px; }
        .header h1 { color: #c4226e; font-size: 18px; margin: 0; }
        .header p { margin: 4px 0 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #c4226e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #fdf2f8; }
        .footer { text-align: center; margin-top: 20px; color: #999; font-size: 9px; }
        .text-right { text-align: right; }
        .total-row { background: #fdf2f8; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pronails Fidelity — Historique des factures</h1>
        <p>Genere le {{ date('d/m/Y H:i') }} &bull; {{ $factures->count() }} factures</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Client</th>
                <th class="text-right">Total</th>
                <th class="text-right">Remise</th>
                <th class="text-right">Net</th>
                <th>Paiement</th>
                <th>Lieu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factures as $f)
            <tr>
                <td>{{ $f->id }}</td>
                <td>{{ $f->date_facture }}</td>
                <td>{{ $f->nom_client ?? '—' }}</td>
                <td class="text-right">{{ number_format($f->montant_total, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ $f->taux_remise ?? 0 }}%</td>
                <td class="text-right">{{ number_format($f->montant_net ?? $f->montant_total, 0, ',', ' ') }} F</td>
                <td>{{ $f->mode_paiement }}</td>
                <td>{{ $f->lieu_prestation ?? '' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td class="text-right">{{ number_format($factures->sum('montant_total'), 0, ',', ' ') }} F</td>
                <td></td>
                <td class="text-right">{{ number_format($factures->sum('montant_net') ?: $factures->sum('montant_total'), 0, ',', ' ') }} F</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">Pronails Abidjan &bull; Programme de fidelite</div>
</body>
</html>
