<?php
// Set maximum execution time to 5 minutes (300 seconds)
ini_set('max_execution_time', 1000);

// Rest of your code ...
require_once 'simple_html_dom.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Day Trades</title>
    <style>
        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Live Day Trades</h1>

    <?php
    // Function to fetch stock data using Alpha Vantage API
    function getStockData($symbol) {
        $api_key = '9G7BKUZ6GCSUCBUC';
        $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$symbol}&apikey={$api_key}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // Function to calculate historical performance score
    function calculateHistoricalPerformanceScore($symbol) {
        // Fetch historical data for the symbol using Alpha Vantage API
        $api_key = '9G7BKUZ6GCSUCBUC';
        $url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol={$symbol}&apikey={$api_key}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $timeSeries = isset($data['Time Series (Daily)']) ? $data['Time Series (Daily)'] : null;

        if ($timeSeries) {
            // Calculate the percentage change in closing price over a defined period
            $percentageChange = [];
            $days = 30; // Consider the last 30 days for historical performance

            $closingPrices = array_slice($timeSeries, 0, $days, true);
            $closingPrices = array_column($closingPrices, '4. close');
            $closingPrices = array_reverse($closingPrices);

            $previousPrice = null;
            foreach ($closingPrices as $closingPrice) {
                if ($previousPrice !== null) {
                    $change = (($closingPrice - $previousPrice) / $previousPrice) * 100;
                    $percentageChange[] = $change;
                }
                $previousPrice = $closingPrice;
            }

            // Calculate the average percentage change
            $averageChange = array_sum($percentageChange) / count($percentageChange);

            // Normalize the average change to a score between 1 and 100
            $score = ($averageChange + 100) / 2;

            return $score;
        }

        return 0; // Return a default score if data is not available
    }

    // Function to calculate analyst recommendations score
    function calculateAnalystRecommendationsScore($symbol) {
        // Fetch analyst recommendations for the symbol using your preferred data source or API
        // Implement your logic to calculate the score based on the recommendations
        return mt_rand(1, 100); // Random score for demonstration purposes
    }

    // Function to calculate price momentum score
    function calculatePriceMomentumScore($symbol) {
        // Fetch price history or use real-time data to analyze price momentum
        // Implement your logic to calculate the score based on price momentum indicators
        return mt_rand(1, 100); // Random score for demonstration purposes
    }

    // Fetch the list of symbols from the companylist.csv file
    $csvFile = 'USE_20230608.csv';
    $symbols = [];

    if (($handle = fopen($csvFile, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($data[0] !== 'Symbol') {
                $symbols[] = $data[0];

            }
        }
        fclose($handle);
    }

    // Calculate scores for each symbol
    $symbolScores = [];
    foreach ($symbols as $symbol) {
        $historicalPerformanceScore = calculateHistoricalPerformanceScore($symbol);
        $analystRecommendationsScore = calculateAnalystRecommendationsScore($symbol);
        $priceMomentumScore = calculatePriceMomentumScore($symbol);

        // Calculate the overall score by averaging the individual scores
        $overallScore = ($historicalPerformanceScore + $analystRecommendationsScore + $priceMomentumScore) / 3;

        $symbolScores[$symbol][] = $overallScore;
    }

    ?>

    <table>
        <tr>
            <th>Symbol</th>
            <th>Entry Point</th>
            <th>Stop Loss</th>
            <th>Profit Target</th>
            <th>Scores</th>
        </tr>

        <?php foreach ($symbolScores as $symbol => $scores): ?>
            <?php $stockData = getStockData($symbol); ?>
            <?php if (isset($stockData['Global Quote']) && isset($stockData['Global Quote']['05. price'])): ?>
                <?php
                $entry = $stockData['Global Quote']['05. price'];
                $stopLoss = $entry - ($entry * 0.02);
                $profitTarget = $entry + ($entry * 0.03);
                ?>

                <tr>
                    <td><?php echo $symbol; ?></td>
                    <td><?php echo $entry; ?></td>
                    <td><?php echo $stopLoss; ?></td>
                    <td><?php echo $profitTarget; ?></td>
                    <td>
                        <?php foreach ($scores as $score): ?>
                            <?php echo $score; ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
</body>
</html>
