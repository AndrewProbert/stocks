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
    // Function to fetch premarket stock data
    function getPremarketStockData() {
        // Make a request to MarketWatch
        $url = "https://www.marketwatch.com/investing/stocks/premarket";
        $response = file_get_contents($url);

        // Extract the premarket stock data from the response
        preg_match_all('/<a href="\/investing\/stock\/(.*?)">(.*?)<\/a>.*?<bg-quote class="value">(.*?)<\/bg-quote>/', $response, $matches);

        if (!empty($matches[1]) && !empty($matches[2]) && !empty($matches[3])) {
            // Combine the matched data into an associative array
            $premarketData = array();
            for ($i = 0; $i < count($matches[1]); $i++) {
                $symbol = $matches[1][$i];
                $name = $matches[2][$i];
                $lastPrice = $matches[3][$i];

                $premarketData[$symbol] = array(
                    'name' => $name,
                    'lastPrice' => $lastPrice
                );
            }

            return $premarketData;
        }

        return null;
    }

    // Fetch premarket stock data
    $premarketData = getPremarketStockData();

    // Display the premarket stock data
    if ($premarketData) {
        echo "<h3>Premarket Stock Data:</h3>";
        echo "<table>";
        echo "<tr><th>Symbol</th><th>Name</th><th>Last Price</th></tr>";
        foreach ($premarketData as $symbol => $stock) {
            $name = $stock['name'];
            $lastPrice = $stock['lastPrice'];
            echo "<tr><td>$symbol</td><td>$name</td><td>$lastPrice</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Unable to fetch premarket stock data. Please try again later.</p>";
    }
    ?>
</body>
</html>
