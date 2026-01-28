<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulation Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
        }
        h1, h2 {
            margin: 0 0 10px 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header img {
            height: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        ul {
            margin: 0 0 20px 20px;
            padding: 0;
        }
    </style>
</head>
<body>
    <!-- Header with branding -->
    <div class="header">
        <h1>Simulation Report</h1>
        <img src="{{ asset('storage/' . 'Images/logo.png') }}" alt="Municipal Logo">
    </div>

    <!-- Metadata -->
    <p><strong>Exported by:</strong> {{ $user->name ?? 'Unknown' }}</p>
    <p><strong>Export date:</strong> {{ $exportedAt->format('Y-m-d H:i') }}</p>

    <!-- Map Image -->
    @if($mapImage)
        <h2>City Map</h2>
        <img src="{{ $mapImage }}" style="width:100%; height:auto;">
    @endif

    <!-- Quality of Life Scores -->
    <h2>Quality of Life Scores</h2>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scores as $metric => $value)
                <tr>
                    <td>{{ $metric }}</td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Active Events -->
    @if(!empty($events))
        <h2>Active Events</h2>
        <ul>
            @foreach($events as $event)
                <li>{{ $event['name'] ?? 'Unnamed Event' }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
