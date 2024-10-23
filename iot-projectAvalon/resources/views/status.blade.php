<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status ESP32</title>
    <style>
        /* Buat tampilan responsif */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
        }

        .error {
            color: red;
        }

        /* Responsif untuk perangkat mobile */
        @media (max-width: 600px) {
            .container {
                width: 90%;
            }
        }

        @media (min-width: 601px) {
            .container {
                width: 50%;
            }
        }
    </style>

    <!-- Tambahkan jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        // Fungsi untuk mengambil data dari server Node.js dan memperbarui tampilan
        function fetchData() {
            $.get('/api/status', function(data) {
                // Jika data ditemukan dan diterima
                if (data.status === 'Data Lost') {
                    $('#status').text('No data received from ESP32 within the last 60 seconds.');
                    $('#temperature').text('N/A');
                    $('#humidity').text('N/A');
                    $('#soilMoisture').text('N/A');
                } else {
                    // Perbarui elemen HTML dengan data yang diterima
                    $('#status').text(data.status);
                    $('#temperature').text(data.temperature + " °C");
                    $('#humidity').text(data.humidity + " %");
                    $('#soilMoisture').text(data.soilMoisture + " %");
                }
            }).fail(function() {
                // Jika terjadi kesalahan
                $('#status').text('Error fetching data from Node.js.');
                $('#temperature').text('N/A');
                $('#humidity').text('N/A');
                $('#soilMoisture').text('N/A');
            });
        }

        // Ambil data setiap 1 detik
        setInterval(fetchData, 1000);

        // Panggil fetchData() pertama kali ketika halaman dimuat
        $(document).ready(function() {
            fetchData();
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Status ESP32</h1>

        <!-- Tampilkan status data dari Node.js -->
        <p>Status: <span id="status">Loading...</span></p>
        <p>Temperature: <span id="temperature">Loading...</span></p>
        <p>Humidity: <span id="humidity">Loading...</span></p>
        <p>Soil Moisture: <span id="soilMoisture">Loading...</span></p>
    </div>
</body>
</html>
