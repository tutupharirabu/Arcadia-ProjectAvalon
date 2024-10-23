<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status ESP32</title>
</head>
<body>
    <h1>Status ESP32</h1>

    @if ($data['status'] === 'Data Lost')
        <!-- Jika data hilang -->
        <p style="color:red;">No data received from ESP32.</p>
    @else
        <!-- Tampilkan data jika diterima -->
        <p>Status: {{ $data['status'] }}</p>
        <p>Temperature: {{ $data['temperature'] }} °C</p>
        <p>Humidity: {{ $data['humidity'] }} %</p>
    @endif
</body>
</html>

