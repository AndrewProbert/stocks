<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="utf-8" lang="english">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <h1>Home</h1>
    <p>Select Scanner Type</p>
    <a href="premarket.php">Pre-Market</a>
    <a href="intraday.php">Intraday</a>
    <a href="options.php">Options</a>



<form method="GET" action="">
    <label for="search">Search Ticker:</label>
    <input type="text" id="search" name="search" required>
    <button type="submit">Search</button>
</form>
<div>
        <h2>Stock Information</h2>
        <p id="symbol"></p>
        <p id="price"></p>
        <p id="change"></p>
        <p id="changePercent"></p>
    </div>

    <canvas id="chart"></canvas>

    <script>
        // Define your Alpha Vantage API key
        const apiKey = '9G7BKUZ6GCSUCBUC';

        // Get the search query from the URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');

        // Function to retrieve stock information
        function getStockInformation() {
            fetch(`https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=${searchQuery}&apikey=${apiKey}`)
                .then(response => response.json())
                .then(data => {
                    if (data['Global Quote']) {
                        const symbol = data['Global Quote']['01. symbol'];
                        const price = parseFloat(data['Global Quote']['05. price']).toFixed(2);
                        const change = parseFloat(data['Global Quote']['09. change']).toFixed(2);
                        const changePercent = data['Global Quote']['10. change percent'];

                        // Update the stock information on the page
                        document.getElementById('symbol').textContent = `Symbol: ${symbol}`;
                        document.getElementById('price').textContent = `Price: $${price}`;
                        document.getElementById('change').textContent = `Change: $${change}`;
                        document.getElementById('changePercent').textContent = `Change Percent: ${changePercent}`;

                        // Fetch full-day historical stock data
                        const today = new Date().toISOString().split('T')[0];
                        const url = `https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=${searchQuery}&apikey=${apiKey}`;
                        return fetch(url);
                    } else {
                        // Display an error message if the request failed
                        document.getElementById('symbol').textContent = `Error retrieving stock information for ${searchQuery}.`;
                        document.getElementById('price').textContent = '';
                        document.getElementById('change').textContent = '';
                        document.getElementById('changePercent').textContent = '';
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data['Time Series (Daily)']) {
                        const timeSeries = data['Time Series (Daily)'];
                        const prices = Object.values(timeSeries).reverse().map(entry => parseFloat(entry['4. close']).toFixed(2));
                        const dates = Object.keys(timeSeries).reverse();

                        // Update the chart data
                        updateChartData(dates, prices);
                    }
                })
                .catch(error => console.error(error));
        }

        // Function to update the chart with historical data
        function updateChartData(dates, prices) {
            const chart = Chart.getChart('chart');
            chart.data.labels = dates;
            chart.data.datasets[0].data = prices;
            chart.update();
        }

        // Function to create and initialize the chart
        function createChart() {
            const ctx = document.getElementById('chart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Stock Price',
                        data: [],
                        fill: false,
                        borderColor: 'blue',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Price'
                            }
                        }
                    }
                }
            });
            return chart;
        }

        // Initialize the chart
        const chart = createChart();

        // Set the search query if provided
        if (searchQuery) {
            document.getElementById('search').value = searchQuery;
            getStockInformation();
        }

        // Add event listener to the form submission
        document.querySelector('form').addEventListener('submit', event => {
            event.preventDefault();
            const newSearchQuery = document.getElementById('search').value;
            window.location.search = `?search=${newSearchQuery}`;
        });

        // Auto-update the stock information and chart every 5 seconds
        setInterval(getStockInformation, 5000);
    </script>
</body>
</html>

<?php
    
/*




    // Define your Alpha Vantage API key
    $apiKey = '9G7BKUZ6GCSUCBUC';
    
    // Check if a search query is provided
if (isset($_GET['search'])) {
    // Get the search query
    $searchQuery = $_GET['search'];

    // Make a request to the Alpha Vantage API
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$searchQuery&apikey=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Check if the response was successful
    if (isset($data['Global Quote'])) {
        $symbol = $data['Global Quote']['01. symbol'];
        $price = $data['Global Quote']['05. price'];
        $change = $data['Global Quote']['09. change'];
        $changePercent = $data['Global Quote']['10. change percent'];

        // Display the stock ticker information
        echo "Symbol: $symbol<br>";
        echo "Price: $price<br>";
        echo "Change: $change<br>";
        echo "Change Percent: $changePercent<br>";
    } else {
        // Display an error message if the request failed
        echo "Error retrieving stock information for $searchQuery.";
    }
}

    /*Note: Make sure you have the PHP cURL extension enabled on your server or hosting environment to use the file_get_contents() function. 
    Alternatively, you can use the cURL library to make the API request. */




    ?>
    

