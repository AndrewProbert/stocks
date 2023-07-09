<?php
// stock_info.php

// Retrieve the symbol from the query parameter
$symbol = $_GET['symbol'];

// Fetch stock data from Yahoo Finance API
$url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d";
$data = file_get_contents($url);
$jsonData = json_decode($data, true);

// Check if the necessary data is available
if (isset($jsonData['chart']['result'][0]['meta']['regularMarketPrice'])) {
    $price = $jsonData['chart']['result'][0]['meta']['regularMarketPrice'];
    //echo "Live Price: " . $price;
    //echo "<br>";
}

/*if (isset($jsonData['chart']['result'][0]['meta']['chartPreviousClose'])) {
    $chartURL = $jsonData['chart']['result'][0]['meta']['chartPreviousClose'];
    echo "Chart: " . $chartURL;
    echo "<br>";
} */

if (isset($jsonData['chart']['result'][0]['indicators']['quote'][0]['volume'])) {
    $stockVolume = $jsonData['chart']['result'][0]['indicators']['quote'][0]['volume'][0];
    //echo "Stock Volume: " . $stockVolume;
    //echo "<br>";
}
// Function to calculate historical performance score using MACD
function calculateHistoricalPerformanceScore($symbol) {
    // Fetch historical data for the symbol using Yahoo Finance
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

        // Calculate the MACD indicator
        $macdData = calculateMACD($closeData);

        // Calculate the score based on the MACD values
        if (!empty($macdData)) {
            $positiveMACDCount = 0;
            foreach ($macdData as $macd) {
                if ($macd > 0) {
                    $positiveMACDCount++;
                }
            }

            // Calculate the score based on the positive MACD values
            $positiveMACDScore = ($positiveMACDCount / count($macdData)) * 100;
            $score = $positiveMACDScore;
            // Cap the score at 100
            $score = min($score, 100);

            return $score;
        }
    }

    return null;
}

// Function to calculate MACD values
function calculateMACD($data) {
    $ema12 = calculateEMA($data, 12);
    $ema26 = calculateEMA($data, 26);

    $macdLine = array_map(function ($ema12Value, $ema26Value) {
        return $ema12Value - $ema26Value;
    }, $ema12, $ema26);

    return $macdLine;
}

// Function to calculate Exponential Moving Average (EMA)
function calculateEMA($data, $period) {
    $multiplier = 2 / ($period + 1);
    $ema = [];

    // Calculate the initial SMA as the first value
    $sma = array_slice($data, 0, $period);
    $ema[] = array_sum($sma) / $period;

    // Calculate EMA for the remaining values
    for ($i = $period; $i < count($data); $i++) {
        $ema[] = ($data[$i] - $ema[$i - $period]) * $multiplier + $ema[$i - $period];
    }

    return $ema;
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
        $score = min($score, 100);
       // echo "Analyst Recommendations Score: {$score}\n";
        return $score;
    }

    // Remove symbol from the file if there are no recommendations
    $csvFile = "USE_20230608.csv";
    $lines = file($csvFile);
    $output = [];
    foreach ($lines as $line) {
        if (strpos($line, $symbol) === false) {
            $output[] = $line;
        }
    }
    file_put_contents($csvFile, implode('', $output));

    return null;
}

// Function to calculate price momentum score based on RSI
function calculatePriceMomentumScore($symbol) {
    // Fetch price history for the symbol using Yahoo Finance or your preferred data source
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1mo";

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

        // Calculate the RSI (Relative Strength Index)
        $rsi = calculateRSI($closeData);

        // Calculate the price momentum score based on RSI
        $score = calculateScoreFromRSI($rsi);
        //echo "Price Momentum Score (RSI-based): {$score}\n";
        return $score;
    }

    return null;
}

// Function to calculate RSI (Relative Strength Index)
function calculateRSI($closeData) {
    // Set the time period for RSI calculation
    $timePeriod = 14;

    // Calculate the price changes
    $priceChanges = [];
    $previousPrice = null;
    foreach ($closeData as $price) {
        if ($previousPrice !== null) {
            $priceChange = $price - $previousPrice;
            $priceChanges[] = $priceChange;
        }
        $previousPrice = $price;
    }

    // Calculate the gains and losses
    $gains = [];
    $losses = [];
    foreach ($priceChanges as $priceChange) {
        if ($priceChange > 0) {
            $gains[] = $priceChange;
            $losses[] = 0;
        } else {
            $gains[] = 0;
            $losses[] = abs($priceChange);
        }
    }

    // Calculate the average gains and losses
    $avgGain = array_sum(array_slice($gains, 0, $timePeriod)) / $timePeriod;
    $avgLoss = array_sum(array_slice($losses, 0, $timePeriod)) / $timePeriod;

    // Calculate the initial RSI
    if ($avgLoss == 0) {
        $rsi = 100;
    } else {
        $rs = $avgGain / $avgLoss;
        $rsi = 100 - (100 / (1 + $rs));
    }

    // Calculate the subsequent RSI values
    $dataCount = count($closeData);
    for ($i = $timePeriod; $i < $dataCount; $i++) {
        $priceChange = $priceChanges[$i - 1];
        if ($priceChange > 0) {
            $gain = $priceChange;
            $loss = 0;
        } else {
            $gain = 0;
            $loss = abs($priceChange);
        }

        $avgGain = (($avgGain * ($timePeriod - 1)) + $gain) / $timePeriod;
        $avgLoss = (($avgLoss * ($timePeriod - 1)) + $loss) / $timePeriod;

        if ($avgLoss == 0) {
            $rsiValue = 100;
        } else {
            $rs = $avgGain / $avgLoss;
            $rsiValue = 100 - (100 / (1 + $rs));
        }

        $rsi = round($rsiValue, 2);
    }

    return $rsi;
}

// Function to calculate the score based on RSI
function calculateScoreFromRSI($rsi) {
    // Adjust the RSI score based on a desired scale
    $score = ($rsi - 50) * 2;
    $score = max(min($score, 100), 0);
    return $score;
}

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
$historicalPerformanceScore = calculateHistoricalPerformanceScore($symbol);
$analystRecommendationsScore = calculateAnalystRecommendationsScore($symbol);
$priceMomentumScore = calculatePriceMomentumScore($symbol);
$overallScore = ($historicalPerformanceScore + $analystRecommendationsScore + $priceMomentumScore) / 3;

// Display stock symbol
/*
echo "Stock Symbol: " . $symbol;
echo "<br>";
echo $historicalPerformanceScore;
echo "<br>";

echo $analystRecommendationsScore;
echo "<br>";

echo $priceMomentumScore;
echo "<br>";

echo $overallScore;
echo "<br>";
*/

// Check if the form is submitted
if (isset($_POST['search'])) {
    // Retrieve the symbol entered by the user
    $symbol = strtoupper($_POST['symbol']);

    // Redirect the user to the stock_info.php page with the symbol as a parameter
    header("Location: stock_info.php?symbol={$symbol}");
    exit;
}


?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $symbol; ?> Stock Analysis</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    
<body>
<header>
    <nav>
      <div class="logo">
        <a href="index.php"> <img src="gloptionW.png" alt="Logo"></a>
        <form method="POST" action="" target="">
        <input type="text" name="symbol" placeholder="Enter stock symbol" >
        <input type="submit" name="search" value="Search">
    </form>
      </div>
      <ul class="navbar">
        <li><a href="intraday.php">Track</a></li>
        <li><a href="options.php">Options</a></li>
        <li><a href="http://localhost/gld/index.php">Gold Scanner</a></li>
        <li><a href="news.php">News</a></li>
      </ul>
    </nav>
  </header>
  <br>
    <h1><?php echo $symbol; ?> Analysis</h1>
    <p1><?php echo "Price: " . $price; ?></p1><br>
    <p1><?php echo "Volume: " . $stockVolume; ?></p1><br>

  


    <style>
    .score-low {
        background-color: red;
        color: white;
    }

    .score-1 {
        background-color: #FF3300;
        color: white;
    }

    .score-2 {
        background-color: #FF6600;
        color: white;
    }

    .score-3 {
        background-color: #FF9900;
        color: black;
    }

    .score-4 {
        background-color: #FFCC00;
        color: black;
    }

    .score-5 {
        background-color: #FFFF00;
        color: black;
    }

    .score-6 {
        background-color: #CCFF00;
        color: black;
    }

    .score-7 {
        background-color: #99FF00;
        color: black;
    }

    .score-8 {
        background-color: #66FF00;
        color: black;
    }

    .score-9 {
        background-color: #33FF00;
        color: black;
    }

    .score-10 {
        background-color: #00FF00;
        color: black;
    }
</style>

<table>
    <tr>
        <th>Category</th>
        <th>Score</th>
    </tr>
    <tr>
        <td>Historical Performance</td>
        <td class="<?php echo getScoreClass($historicalPerformanceScore); ?>"><?php echo $historicalPerformanceScore; ?></td>
    </tr>
    <tr>
        <td>Analyst Recommendations</td>
        <td class="<?php echo getScoreClass($analystRecommendationsScore); ?>"><?php echo $analystRecommendationsScore; ?></td>
    </tr>
    <tr>
        <td>Price Momentum</td>
        <td class="<?php echo getScoreClass($priceMomentumScore); ?>"><?php echo $priceMomentumScore; ?></td>
    </tr>
    <tr>
        <td>Overall</td>
        <td class="<?php echo getScoreClass($overallScore); ?>"><?php echo $overallScore; ?></td>
    </tr>
</table>

<?php
function getScoreClass($score)
{
    // Define the score ranges and associated CSS classes
    $scoreClasses = [
        'score-low' => [0, 20],
        'score-1' => [20, 30],
        'score-2' => [30, 40],
        'score-3' => [40, 50],
        'score-4' => [50, 60],
        'score-5' => [60, 70],
        'score-6' => [70, 80],
        'score-7' => [80, 90],
        'score-8' => [90, 95],
        'score-9' => [95, 99],
        'score-10' => [99, 100]
    ];

    // Find the appropriate CSS class for the given score
    foreach ($scoreClasses as $class => $range) {
        if ($score >= $range[0] && $score <= $range[1]) {
            return $class;
        }
    }

    return '';
}
?>
<br><br>
<?php
session_start();
$_POST['stock_symbol'] = $symbol;
echo '<h2>Notes for ' . $_POST['stock_symbol'] . '</h2>';

if (isset($_POST['stock_symbol']) && isset($_POST['note'])) {
    $_SESSION[$_POST['stock_symbol']] = $_POST['note'];
}
?>

<form method="post">
    <textarea name="note" placeholder="Note"><?php if (isset($_SESSION[$_POST['stock_symbol']])) { echo $_SESSION[$_POST['stock_symbol']]; } ?></textarea>
    <button type="submit">Save Note</button>
</form>

<?php
if (isset($_SESSION[$_POST['stock_symbol']])) {
    echo '<h2>Review Notes for ' . $_POST['stock_symbol'] . '</h2>';
    echo '<p>' . $_SESSION[$_POST['stock_symbol']] . '</p>';
}
?>



</body>
</html>




