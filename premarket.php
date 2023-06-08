<!DOCTYPE html>
<html>
<head>
    <title>Premarket Stocks</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Premarket Stocks</h1>

    <?php
    // Define your Alpha Vantage API key
    $apiKey = '9G7BKUZ6GCSUCBUC';

    // Function to fetch premarket stock data
    function getPremarketStockData() {
        global $apiKey;

        // Make a request to the Alpha Vantage API
        $url = "https://www.alphavantage.co/query?function=SECTOR&apikey=$apiKey";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        // Check if the response was successful
        if (isset($data['Rank F: Year Performance'])) {
            $sectorData = $data['Rank F: Year Performance'];
            
            // Sort the sector data by performance
            arsort($sectorData);

            // Get the top 5 performing sectors
            $topSectors = array_slice($sectorData, 0, 5);

            // Fetch the most active and highest gaining stocks in each sector
            $stockData = array();
            foreach ($topSectors as $sector => $performance) {
                $url = "https://www.alphavantage.co/query?function=SECTOR&sector=$sector&apikey=$apiKey";
                $response = file_get_contents($url);
                $data = json_decode($response, true);

                if (isset($data['Rank D: 5 Day Performance'])) {
                    $stocks = $data['Rank D: 5 Day Performance'];
                    arsort($stocks);

                    // Get the top 3 most active stocks
                    $topActiveStocks = array_slice($stocks, 0, 3);
                    $stockData[$sector]['active'] = $topActiveStocks;

                    // Get the top 3 highest gaining stocks
                    $topGainingStocks = array_slice($stocks, 0, 3, true);
                    $stockData[$sector]['gaining'] = $topGainingStocks;
                }
            }

            return $stockData;
        }

        return null;
    }

    // Fetch premarket stock data
    $premarketData = getPremarketStockData();

    // Display the premarket stock data
    if ($premarketData) {
        foreach ($premarketData as $sector => $stockInfo) {
            echo "<h2>$sector</h2>";

            echo "<h3>Most Active Stocks:</h3>";
            echo "<table>";
            echo "<tr><th>Symbol</th><th>Last Price</th><th>Volume</th></tr>";
            foreach ($stockInfo['active'] as $symbol => $info) {
                echo "<tr><td>$symbol</td><td>{$info['4. close']}</td><td>{$info['5. volume']}</td></tr>";
            }
            echo "</table>";

            echo "<h3>Highest Gaining Stocks:</h3>";
            echo "<table>";
            echo "<tr><th>Symbol</th><th>Last Price</th><th>Change Percent</th></tr>";
            foreach ($stockInfo['gaining'] as $symbol => $info) {
                echo "<tr><td>$symbol</td><td>{$info['4. close']}</td><td>{$info['10. change percent']}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>Unable to fetch premarket stock data. Please try again later.</p>";
    }
    ?>
</body>
</html>
