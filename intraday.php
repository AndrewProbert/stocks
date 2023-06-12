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
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";
    
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
    
    // Extract the desired data (closing prices)
    if (isset($data['chart']['result'][0]['indicators']['quote'][0]['close'])) {
        $closeData = $data['chart']['result'][0]['indicators']['quote'][0]['close'];
        
        // Calculate the average price change and positive performance
        $priceChanges = [];
        $positivePerformanceCount = 0;
        $previousPrice = null;
        foreach ($closeData as $price) {
            if ($previousPrice !== null) {
                $priceChange = ($price - $previousPrice) / $previousPrice;
                $priceChanges[] = $priceChange;
                
                if ($priceChange > 0) {
                    $positivePerformanceCount++;
                }
            }
            $previousPrice = $price;
        }
        
        // Calculate the score based on the average price change and positive performance
        if (!empty($priceChanges)) {
            $averagePriceChange = array_sum($priceChanges) / count($priceChanges);
            
            // Assign higher scores for positive price changes and consistent positive performance
            $positivePerformanceScore = ($positivePerformanceCount / count($priceChanges)) * 100;
            $score = ($averagePriceChange * 0.7 + $positivePerformanceScore * 0.3) * 100;
            // Cap the score at 100
            $score = min($score, 100);
            return $score;
        }
    }
    
    return null;
}


// Function to calculate analyst recommendations score
function calculateAnalystRecommendationsScore($symbol) {
    // Fetch analyst recommendations for the symbol using Yahoo Finance or your preferred data source
    $url = "https://query2.finance.yahoo.com/v10/finance/quoteSummary/{$symbol}?modules=recommendationTrend";

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

    // Extract the desired data (analyst recommendations)
    if (isset($data['quoteSummary']['result'][0]['recommendationTrend']['trend'])) {
        $recommendations = $data['quoteSummary']['result'][0]['recommendationTrend']['trend'];

        // Calculate the score based on the recommendations
        $totalRecommendations = count($recommendations);
        $positiveRecommendations = 0;
        $negativeRecommendations = 0;

        foreach ($recommendations as $recommendation) {
            $strongBuy = $recommendation['strongBuy'] ?? 0;
            $buy = $recommendation['buy'] ?? 0;
            $strongSell = $recommendation['strongSell'] ?? 0;
            $sell = $recommendation['sell'] ?? 0;

            // Assign scores based on the level of recommendation
            $positiveRecommendations += $strongBuy + $buy;
            $negativeRecommendations += $strongSell + $sell;
        }

        // Calculate the score as a weighted average of positive and negative recommendations
        $totalScore = $positiveRecommendations + $negativeRecommendations;

        // Check if there are recommendations to avoid division by zero
        if ($totalScore === 0) {
            return 0; // No recommendations, assign a score of 0
        }

        $positiveScore = ($positiveRecommendations / $totalScore) * 100;
        $negativeScore = ($negativeRecommendations / $totalScore) * 100;

        // Assign a higher score for positive recommendations
        $score = ($positiveScore * 0.7) - ($negativeScore * 0.3);

        // Cap the score between 0 and 100
        $score = max(0, min($score, 100));
      //  echo "Analyst Recommendations Score: {$score}\n";
        return $score;
    }

    return null;
}


// neeed work here!!
// Function to calculate price momentum score using AI, MACD, and RSI
function calculatePriceMomentumScore($symbol) {
    // Fetch price history for the symbol using Yahoo Finance or your preferred data source
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

    // Extract the desired data (closing prices)
    if (isset($data['chart']['result'][0]['indicators']['quote'][0]['close'])) {
        $closeData = $data['chart']['result'][0]['indicators']['quote'][0]['close'];

        // Calculate price momentum score based on the closing prices
        $priceChangePercentage = ($closeData[0] - $closeData[1]) / $closeData[1] * 100;

        // Calculate MACD and RSI scores
        $macdScore = calculateMACDScore($symbol);
        $rsiScore = calculateRSIScore($symbol);

        // Combine the MACD, RSI, and price change percentage to calculate the final score
        $score = ($macdScore + $rsiScore + $priceChangePercentage) / 3;

        // Normalize the score to a range of 0 to 100
        $score = max(0, min(100, $score));

        return $score;
    }

    return null;
}

// Function to calculate MACD score
function calculateMACDScore($symbol) {
    // Fetch historical price data for the symbol using Yahoo Finance or your preferred data source
    // Replace this example code with your actual implementation to fetch historical data

    // Placeholder data for demonstration purposes
    $priceData = [100, 110, 120, 130, 140, 150, 160, 170, 180, 190];

    // Calculate MACD based on the price data
    // Replace this example code with your actual MACD calculation logic

    // Placeholder values for demonstration purposes
    $macdLine = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    $signalLine = [0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];
    $histogram = [0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];

    // Calculate the MACD score
    // You can use different logic based on the MACD values to assign a score
    $macdScore = end($macdLine) - end($signalLine);

    return $macdScore;
}

// Function to calculate RSI score
function calculateRSIScore($symbol) {
    // Fetch historical price data for the symbol using Yahoo Finance or your preferred data source
    // Replace this example code with your actual implementation to fetch historical data

    // Placeholder data for demonstration purposes
    $priceData = [100, 110, 120, 130, 140, 150, 160, 170, 180, 190];

    // Calculate RSI based on the price data
    // Replace this example code with your actual RSI calculation logic

    // Placeholder value for demonstration purposes
    $rsi = 60;

    return $rsi;
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

// Pagination
$perPage = 100;
$totalSymbols = count($symbols);
$totalPages = ceil($totalSymbols / $perPage);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startIndex = ($page - 1) * $perPage;
$endIndex = min($startIndex + $perPage, $totalSymbols);
$symbolsPerPage = array_slice($symbols, $startIndex, $endIndex - $startIndex);

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
    curl_close($handle);
}

// Loop through the symbols on the current page and create cURL handles
foreach ($symbolsPerPage as $symbol) {
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

// Display the table
?>
<?php
// Check if a sorting option is selected
$sortingOption = isset($_GET['sort']) ? $_GET['sort'] : 'symbol';

// Sort the symbols based on the selected option
if ($sortingOption === 'score') {
    uasort($symbolScores, function($a, $b) {
        return $b['scores'] - $a['scores'];
    });
} elseif ($sortingOption === 'entry') {
    uasort($symbolScores, function($a, $b) {
        return $a['price'] - $b['price'];
    });
} else {
    ksort($symbolScores);
}

// Display the table
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

    <form action="" method="GET">
        <label for="sort">Sort By:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="symbol" <?php echo ($sortingOption === 'symbol') ? 'selected' : ''; ?>>Symbol</option>
            <option value="score" <?php echo ($sortingOption === 'score') ? 'selected' : ''; ?>>Score</option>
            <option value="entry" <?php echo ($sortingOption === 'entry') ? 'selected' : ''; ?>>Entry Price</option>
        </select>
    </form>

    <table>
        <tr>
            <th>Symbol</th>
            <th>Entry Point</th>
            <th>Stop Loss</th>
            <th>Profit Target</th>
            <th>Scores</th>
        </tr>
        <?php foreach ($symbolScores as $symbol => $data): ?>
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

    <?php
    // Pagination links
    for ($i = 1; $i <= $totalPages; $i++) {
        echo "<a href='?page={$i}&sort={$sortingOption}'>Page {$i}</a> | ";
    }
    ?>
</body>
</html>


