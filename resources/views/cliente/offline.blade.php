<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne — Prestige by ProNails</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f9c6df 0%, #b3c8f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', -apple-system, sans-serif;
            padding: 20px;
        }
        .offline-card {
            background: #fff;
            border-radius: 20px;
            padding: 48px 32px;
            text-align: center;
            max-width: 360px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        }
        .offline-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(196, 34, 110, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
        }
        h1 { font-size: 1.3rem; color: #333; margin-bottom: 8px; font-weight: 700; }
        p { font-size: 0.9rem; color: #777; line-height: 1.5; margin-bottom: 24px; }
        .btn-retry {
            background: #c4226e;
            color: #fff;
            border: none;
            padding: 12px 32px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-retry:active { transform: scale(0.97); }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">📡</div>
        <h1>Vous etes hors ligne</h1>
        <p>Verifiez votre connexion internet et reessayez. Votre carte de fidelite reste accessible.</p>
        <button class="btn-retry" onclick="location.reload()">Reessayer</button>
    </div>
</body>
</html>
