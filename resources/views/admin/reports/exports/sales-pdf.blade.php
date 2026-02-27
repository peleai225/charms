<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des ventes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .totals { margin-top: 20px; font-weight: bold; }
        .meta { color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Rapport des ventes</h1>
    <p class="meta">Période : {{ $startDate }} au {{ $endDate }}</p>
    <p class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Période</th>
                <th>Commandes</th>
                <th>Chiffre d'affaires</th>
                <th>Réductions</th>
                <th>Panier moyen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
            <tr>
                <td>{{ $row[0] }}</td>
                <td>{{ $row[1] }}</td>
                <td>{{ $row[2] }} F</td>
                <td>{{ $row[3] }} F</td>
                <td>{{ $row[4] }} F</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total commandes : {{ $data['totals']['orders'] }}</p>
        <p>Total chiffre d'affaires : {{ $data['totals']['revenue'] }} F CFA</p>
        <p>Total réductions : {{ $data['totals']['discounts'] }} F</p>
        <p>Panier moyen : {{ $data['totals']['average'] }} F</p>
    </div>
</body>
</html>
