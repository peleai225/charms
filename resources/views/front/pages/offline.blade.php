<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors connexion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; color: #334155; }
        .container { text-align: center; max-width: 420px; }
        .icon { width: 120px; height: 120px; margin: 0 auto 2rem; }
        .icon svg { width: 100%; height: 100%; }
        h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; color: #1e293b; }
        p { font-size: 1rem; line-height: 1.6; color: #64748b; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.75rem 2rem; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; border-radius: 0.75rem; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg fill="none" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                <circle cx="60" cy="60" r="56" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="2"/>
                <path d="M60 30c-16.569 0-30 10-30 22.5S43.431 75 60 75s30-10 30-22.5S76.569 30 60 30z" fill="#e2e8f0"/>
                <path d="M38 48l44 24M82 48L38 72" stroke="#94a3b8" stroke-width="3" stroke-linecap="round"/>
                <circle cx="60" cy="60" r="8" fill="#94a3b8"/>
            </svg>
        </div>
        <h1>Vous êtes hors connexion</h1>
        <p>Vérifiez votre connexion internet et réessayez. Certaines pages déjà visitées restent disponibles.</p>
        <button class="btn" onclick="window.location.reload()">Réessayer</button>
    </div>
</body>
</html>
