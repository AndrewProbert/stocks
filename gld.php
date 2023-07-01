<?php

// Function to make API request and retrieve data
function fetchData($symbol, $startDate, $endDate) {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?symbol={$symbol}&period1={$startDate}&period2={$endDate}&interval=1d";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['chart']['result'][0]['indicators']['quote'][0];
}

// Function to calculate RSI
function calculateRSI($closePrices, $period = 14) {
    $changes = array();
    $gainSum = 0;
    $lossSum = 0;
    $prevClose = null;

    foreach ($closePrices as $close) {
        if ($prevClose !== null) {
            $change = $close - $prevClose;
            $changes[] = $change;

            if ($change > 0) {
                $gainSum += $change;
            } else {
                $lossSum -= $change;
            }
        }

        $prevClose = $close;
    }

    $avgGain = $gainSum / $period;
    $avgLoss = $lossSum / $period;
    $rs = ($avgGain > 0 && $avgLoss > 0) ? ($avgGain / $avgLoss) : 0;
    $rsi = 100 - (100 / (1 + $rs));

    return $rsi;
}

// Function to analyze RSI and suggest option type with confidence rating
function analyzeRSI($symbol, $startDate, $endDate, $rsiThreshold = 70) {
    $data = fetchData($symbol, $startDate, $endDate);
    $closePrices = $data['close'];
    $rsi = calculateRSI($closePrices);

    $confidence = 0; // Initialize confidence rating

    if ($rsi >= $rsiThreshold) {
        $confidence = ($rsi - $rsiThreshold) / (100 - $rsiThreshold); // Calculate confidence based on RSI deviation
        return ['action' => 'Buy Put Options', 'confidence' => $confidence];
    } elseif ($rsi <= (100 - $rsiThreshold)) {
        $confidence = ((100 - $rsi) - $rsiThreshold) / $rsiThreshold; // Calculate confidence based on RSI deviation
        return ['action' => 'Buy Call Options', 'confidence' => $confidence];
    } else {
        return ['action' => 'No Position', 'confidence' => $confidence];
    }
}

// Usage example
$symbol = 'GLD'; // Gold ETF symbol
$startDate = strtotime('-30 days');
$endDate = time();
$rsiThreshold = 70;

$analysis = analyzeRSI($symbol, $startDate, $endDate, $rsiThreshold);
$result = $analysis['action'];
$confidence = $analysis['confidence'];

echo "Based on RSI analysis, you should: {$result} (Confidence: {$confidence})";

?>
