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
        .badge { padding: 2px 6px; border-radius: 8px; font-size: 9px; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fef2f2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pronails Fidelity — Liste des clientes</h1>
        <p>Genere le {{ date('d/m/Y H:i') }} &bull; {{ $clientes->count() }} clientes</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Telephone</th>
                <th>Email</th>
                <th>Lieu</th>
                <th>Taux</th>
                <th>N° Carte</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                <td><strong>{{ $c->nom }}</strong></td>
                <td>{{ $c->prenom }}</td>
                <td>{{ $c->telephone }}</td>
                <td>{{ $c->email ?? '—' }}</td>
                <td>{{ $c->lieu_enregistrement }}</td>
                <td>{{ intval($c->taux_reduction) }}%</td>
                <td style="font-family:monospace;font-size:9px;">{{ $c->numero_carte }}</td>
                <td><span class="badge {{ $c->active ? 'badge-active' : 'badge-inactive' }}">{{ $c->active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Pronails Abidjan &bull; Programme de fidelite</div>
</body>
</html>
