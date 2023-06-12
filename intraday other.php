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

// Prepare the cURL multi-handle
$multiHandle = curl_multi_init();

// Initialize an array to store the cURL handles
$curlHandles = [];

// Create a function to process the completed cURL request
function processCurlResponse($handle, $symbol, &$symbolScores) {
    $response = curl_multi_getcontent($handle);
    $stockData = json_decode($response, true);

    if (isset($stockData['chart']['result'][0]['indicators']['quote'][0]['close'])) {
        $closeData = $stockData['chart']['result'][0]['indicators']['quote'][0]['close'];
        $price = end($closeData);

        $historicalPerformanceScore = calculateHistoricalPerformanceScore($symbol);
        $analystRecommendationsScore = calculateAnalystRecommendationsScore($symbol);
        $priceMomentumScore = calculatePriceMomentumScore($symbol);

        // Calculate the overall score by averaging the individual scores
        $overallScore = ($historicalPerformanceScore + $analystRecommendationsScore + $priceMomentumScore) / 3;

        $symbolScores[$symbol] = [
            'price' => $price,
            'scores' => $overallScore
        ];
    } else {
        echo "Error: Unable to fetch stock data for symbol {$symbol}.\n";
    }

    // Close the cURL handle
   // curl_multi_remove_handle($multiHandle, $handle);
    curl_close($handle);
}

// Loop through the symbols and create cURL handles
foreach ($symbols as $symbol) {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d";
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    // Add the cURL handle to the multi-handle
    curl_multi_add_handle($multiHandle, $handle);

    $curlHandles[$symbol] = $handle;
}

// Execute the multi-handle requests
$running = null;
do {
    curl_multi_exec($multiHandle, $running);
} while ($running > 0);

// Process the completed cURL responses
$symbolScores = [];
foreach ($curlHandles as $symbol => $handle) {
    processCurlResponse($handle, $symbol, $symbolScores);
}

// Sort the symbols by their overall scores
asort($symbolScores);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 100;
$totalSymbols = count($symbolScores);
$totalPages = ceil($totalSymbols / $perPage);

$start = ($page - 1) * $perPage;
$end = $start + $perPage;
$topScores = array_slice($symbolScores, $start, $perPage);

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
    <?php 
    // Sort the symbols by their scores in descending order (highest scores first)
    usort($topScores, function($a, $b) {
        return $b['scores'] <=> $a['scores'];
    });
    ?>

    <?php foreach ($topScores as $symbol => $data): ?>
        <?php $entry = $data['price']; ?>
        <?php $stopLoss = $entry - ($entry * 0.02); ?>
        <?php $profitTarget = $entry + ($entry * 0.03); ?>

        <tr>
            <td><?php echo $symbol; ?></td>
            <td><?php echo number_format($entry, 5); ?></td>
            <td><?php echo number_format($stopLoss, 5); ?></td>
            <td><?php echo number_format($profitTarget, 5); ?></td>
            <td><?php echo number_format($data['scores'], 2); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if ($totalPages > 1): ?>
    <div>
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
                <strong><?php echo $i; ?></strong>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</body>
</html>

