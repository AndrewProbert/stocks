<?php
ini_set('max_execution_time', 1000);

// Function to fetch stock data using Yahoo Finance
function getStockData($symbol) {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d";

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check if cURL request was successful
    if ($response === false) {
        return null;
    }

    // Close cURL connection
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Extract the desired data (closing price)
    if (isset($data['chart']['result'][0]['indicators']['quote'][0]['close'])) {
        $closeData = $data['chart']['result'][0]['indicators']['quote'][0]['close'];
        $price = end($closeData);
        return $price;
    } else {
        return null;
    }
}

// Function to calculate historical performance score
function calculateHistoricalPerformanceScore($symbol) {
    // Fetch historical data for the symbol using Yahoo Finance or your preferred data source
    // Implement your logic to calculate the score based on the historical data
    return mt_rand(1, 100); // Random score for demonstration purposes
}

// Function to calculate analyst recommendations score
function calculateAnalystRecommendationsScore($symbol) {
    // Fetch analyst recommendations for the symbol using Yahoo Finance or your preferred data source
    // Implement your logic to calculate the score based on the recommendations
    return mt_rand(1, 100); // Random score for demonstration purposes
}

// Function to calculate price momentum score
function calculatePriceMomentumScore($symbol) {
    // Fetch price history or use real-time data to analyze price momentum
    // Implement your logic to calculate the score based on price momentum indicators
    return mt_rand(1, 100); // Random score for demonstration purposes
}

// Fetch the list of symbols from the USE_20230608.csv file
$csvFile = 'USE_20230608.csv';
$symbols = [];

if (($handle = fopen($csvFile, "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if ($data[0] !== 'Symbol' && !strpos($data[0], '-') && !strpos($data[0], '.')) {
            $symbols[] = $data[0];
        }
    }
    fclose($handle);
} else {
    echo "Error: Unable to open CSV file.";
    exit;
}


// Calculate scores for each symbol
$symbolScores = [];
foreach ($symbols as $symbol) {
    $stockData = getStockData($symbol);

    if ($stockData !== null) {
        $historicalPerformanceScore = calculateHistoricalPerformanceScore($symbol);
        $analystRecommendationsScore = calculateAnalystRecommendationsScore($symbol);
        $priceMomentumScore = calculatePriceMomentumScore($symbol);

        // Calculate the overall score by averaging the individual scores
        $overallScore = ($historicalPerformanceScore + $analystRecommendationsScore + $priceMomentumScore) / 3;

        $symbolScores[$symbol][] = $overallScore;
    } else {
        echo "Error: Unable to fetch stock data for symbol {$symbol}.";
    }
}

// Sort the symbols by their overall scores
arsort($symbolScores);
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

    <table>
        <tr>
            <th>Symbol</th>
            <th>Entry Point</th>
            <th>Stop Loss</th>
            <th>Profit Target</th>
            <th>Scores</th>
        </tr>

        <?php foreach ($symbolScores as $symbol => $scores): ?>
            <?php $price = getStockData($symbol); ?>
            <?php if ($price !== null): ?>
                <?php
                $entry = $price;
                $stopLoss = $entry - ($entry * 0.02);
                $profitTarget = $entry + ($entry * 0.03);
                ?>

                <tr>
                <td><?php echo $symbol; ?></td>
                    <td><?php echo number_format($entry, 5); ?></td>
                    <td><?php echo number_format($stopLoss, 5); ?></td>
                    <td><?php echo number_format($profitTarget, 5); ?></td>
                    <td>
                        <?php foreach ($scores as $score): ?>
                            <?php echo number_format($score, 2); ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
</body>
</html>
