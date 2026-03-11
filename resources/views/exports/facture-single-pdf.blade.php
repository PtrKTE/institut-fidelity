<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; margin: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .brand { color: #c4226e; font-size: 22px; font-weight: bold; }
        .brand-sub { color: #666; font-size: 10px; }
        .facture-title { text-align: right; }
        .facture-title h2 { margin: 0; color: #c4226e; font-size: 18px; }
        .facture-title p { margin: 2px 0; color: #666; font-size: 11px; }
        .info-grid { margin-bottom: 25px; }
        .info-grid table { width: 100%; }
        .info-grid td { padding: 4px 8px; vertical-align: top; }
        .info-label { color: #999; font-size: 10px; text-transform: uppercase; }
        .info-value { font-size: 12px; font-weight: 500; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #c4226e; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .items-table tr:nth-child(even) { background: #fdf2f8; }
        .text-right { text-align: right; }
        .totals { float: right; width: 280px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 10px; }
        .totals .total-net { font-size: 16px; font-weight: bold; color: #c4226e; border-top: 2px solid #c4226e; }
        .footer { clear: both; text-align: center; margin-top: 50px; padding-top: 15px; border-top: 1px solid #eee; color: #999; font-size: 9px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 15px 0; }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td>
                <div class="brand">ProNails</div>
                <div class="brand-sub">Ongles &amp; Cils — Abidjan</div>
            </td>
            <td style="text-align:right;">
                <div style="color:#c4226e;font-size:18px;font-weight:bold;">FACTURE #{{ $facture->id }}</div>
                <div style="color:#666;font-size:11px;">{{ $facture->date_facture }}</div>
                @if($facture->lieu_prestation)
                <div style="color:#666;font-size:11px;">{{ $facture->lieu_prestation }}</div>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">

    <table width="100%" style="margin-bottom:20px;">
        <tr>
            <td width="50%">
                <div class="info-label">Client</div>
                <div class="info-value">{{ $facture->nom_client ?? ($cliente ? $cliente->prenom . ' ' . $cliente->nom : '—') }}</div>
                @if($cliente)
                <div style="font-size:10px;color:#666;">{{ $cliente->telephone }}</div>
                @endif
            </td>
            <td width="50%">
                <div class="info-label">Paiement</div>
                <div class="info-value">{{ $facture->mode_paiement }}</div>
                <div class="info-label" style="margin-top:8px;">Caissiere</div>
                <div class="info-value">{{ $facture->caissiere ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Prestation</th>
                <th class="text-right">Tarif</th>
                <th class="text-right">Qte</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prestations as $p)
            <tr>
                <td>{{ $p->libelle }}</td>
                <td class="text-right">{{ number_format($p->tarif, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ $p->quantite ?? 1 }}</td>
                <td class="text-right">{{ number_format(($p->tarif * ($p->quantite ?? 1)), 0, ',', ' ') }} F</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Sous-total</td>
                <td class="text-right">{{ number_format($facture->montant_total, 0, ',', ' ') }} F</td>
            </tr>
            @if($facture->taux_remise > 0)
            <tr>
                <td>Remise ({{ $facture->taux_remise }}%)</td>
                <td class="text-right">-{{ number_format($facture->montant_remise ?? 0, 0, ',', ' ') }} F</td>
            </tr>
            @endif
            <tr>
                <td class="total-net">NET A PAYER</td>
                <td class="total-net text-right">{{ number_format($facture->montant_net ?? $facture->montant_total, 0, ',', ' ') }} F</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Pronails Abidjan &bull; Programme de fidelite Pronails Fidelity<br>
        Merci pour votre confiance !
    </div>
</body>
</html>
