<?php

// Function to calculate the OBV score for a given stock symbol
function calculateOBVScore($symbol) {
    // Set the Yahoo Finance API endpoint
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";

    // Retrieve data from Yahoo Finance API
    $response = @file_get_contents($url);

    if (!$response) {
        // Error handling if API request fails
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data["chart"]["result"][0]["indicators"]["quote"][0]["volume"])) {
        // Error handling if required data is missing
        return false;
    }

    // Extract the OBV values from the API response
    $obvData = $data["chart"]["result"][0]["indicators"]["quote"][0]["volume"];

    if (empty($obvData)) {
        // Error handling if OBV data is empty
        return false;
    }

    // Calculate the OBV score
    $latestOBV = end($obvData);
    $minOBV = min($obvData);
    $maxOBV = max($obvData);

    // Scale the OBV value to a score between 0 and 100
    $score = ($latestOBV - $minOBV) / ($maxOBV - $minOBV) * 100;

    // Round the score to two decimal places
    $score = round($score, 2);

    // Return the OBV score
    return $score;
}



if ($obvScore !== false) {
    echo "OBV score for {$stockSymbol}: {$obvScore}";
} else {
    echo "Error occurred while retrieving OBV score for {$stockSymbol}.";
}


// Function to calculate the ADL score for a given stock symbol
function calculateADLScore($symbol) {
    // Set the Yahoo Finance API endpoint
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";

    // Retrieve data from Yahoo Finance API
    $response = @file_get_contents($url);

    if (!$response) {
        // Error handling if API request fails
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data["chart"]["result"][0]["indicators"]["adjclose"][0])) {
        // Error handling if required data is missing
        return false;
    }

    // Extract the ADL values from the API response
    $adlData = $data["chart"]["result"][0]["indicators"]["adjclose"][0];

    if (empty($adlData)) {
        // Error handling if ADL data is empty
        return false;
    }

    // Calculate the ADL score
    $latestADL = end($adlData);
    $minADL = min($adlData);
    $maxADL = max($adlData);

    // Scale the ADL value to a score between 0 and 100
    $score = ($latestADL - $minADL) / ($maxADL - $minADL) * 100;

    // Round the score to two decimal places
    $score = round($score, 2);

    // Return the ADL score
    return $score;
}

if ($adlScore !== false) {
    echo "ADL score for {$stockSymbol}: {$adlScore}";
} else {
    echo "Error occurred while retrieving ADL score for {$stockSymbol}.";
}


// Function to calculate the ADX score for a given stock symbol
function calculateADXScore($symbol) {
    // Set the Yahoo Finance API endpoint
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";

    // Retrieve data from Yahoo Finance API
    $response = @file_get_contents($url);

    if (!$response) {
        // Error handling if API request fails
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data["chart"]["result"][0]["indicators"]["adx"][0]["adx"])) {
        // Error handling if required data is missing
        return false;
    }

    // Extract the ADX values from the API response
    $adxData = $data["chart"]["result"][0]["indicators"]["adx"][0]["adx"];

    if (empty($adxData)) {
        // Error handling if ADX data is empty
        return false;
    }

    // Calculate the ADX score
    $latestADX = end($adxData);
    $minADX = min($adxData);
    $maxADX = max($adxData);

    // Scale the ADX value to a score between 0 and 100
    $score = ($latestADX - $minADX) / ($maxADX - $minADX) * 100;

    // Round the score to two decimal places
    $score = round($score, 2);

    // Return the ADX score
    return $score;
}


if ($adxScore !== false) {
    echo "ADX score for {$stockSymbol}: {$adxScore}";
} else {
    echo "Error occurred while retrieving ADX score for {$stockSymbol}.";
}


// Function to calculate the Aroon score for a given stock symbol
function calculateAroonScore($symbol) {
    // Set the Yahoo Finance API endpoint
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";

    // Retrieve data from Yahoo Finance API
    $response = @file_get_contents($url);

    if (!$response) {
        // Error handling if API request fails
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data["chart"]["result"][0]["indicators"]["aroon"][0]["aroonUp"]) ||
        !isset($data["chart"]["result"][0]["indicators"]["aroon"][0]["aroonDown"])
    ) {
        // Error handling if required data is missing
        return false;
    }

    // Extract the Aroon values from the API response
    $aroonUpData = $data["chart"]["result"][0]["indicators"]["aroon"][0]["aroonUp"];
    $aroonDownData = $data["chart"]["result"][0]["indicators"]["aroon"][0]["aroonDown"];

    if (empty($aroonUpData) || empty($aroonDownData)) {
        // Error handling if Aroon data is empty
        return false;
    }

    // Calculate the Aroon score
    $latestAroonUp = end($aroonUpData);
    $latestAroonDown = end($aroonDownData);
    $score = ($latestAroonUp + $latestAroonDown) / 2;

    // Scale the Aroon value to a score between 0 and 100
    $score = ($score / 100) * 100;

    // Round the score to two decimal places
    $score = round($score, 2);

    // Return the Aroon score
    return $score;
}


if ($aroonScore !== false) {
    echo "Aroon score for {$stockSymbol}: {$aroonScore}";
} else {
    echo "Error occurred while retrieving Aroon score for {$stockSymbol}.";
}


// Function to calculate the Stochastic Oscillator score for a given stock symbol
function calculateStochasticScore($symbol) {
    // Set the Yahoo Finance API endpoint
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1y";

    // Retrieve data from Yahoo Finance API
    $response = @file_get_contents($url);

    if (!$response) {
        // Error handling if API request fails
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data["chart"]["result"][0]["indicators"]["stoch"][0]["k"]) ||
        !isset($data["chart"]["result"][0]["indicators"]["stoch"][0]["d"])
    ) {
        // Error handling if required data is missing
        return false;
    }

    // Extract the Stochastic Oscillator values from the API response
    $stochasticData = $data["chart"]["result"][0]["indicators"]["stoch"][0];

    if (empty($stochasticData["k"]) || empty($stochasticData["d"])) {
        // Error handling if Stochastic Oscillator data is empty
        return false;
    }

    // Calculate the Stochastic Oscillator score
    $latestK = end($stochasticData["k"]);
    $latestD = end($stochasticData["d"]);

    // Scale the Stochastic Oscillator values to a score between 0 and 100
    $score = ($latestK + $latestD) / 2;

    // Round the score to two decimal places
    $score = round($score, 2);

    // Return the Stochastic Oscillator score
    return $score;
}



if ($stochasticScore !== false) {
    echo "Stochastic Oscillator score for {$stockSymbol}: {$stochasticScore}";
} else {
    echo "Error occurred while retrieving Stochastic Oscillator score for {$stockSymbol}.";
}

// Function to calculate the overall score for a given stock symbol
function calculateOverallScore($symbol) {
    // Define weights for each indicator (adjust as needed)
    $weights = [
        "obv" => 0.15,
        "adl" => 0.15,
        "adx" => 0.15,
        "aroon" => 0.15,
        "stochastic" => 0.15,
        "rsi" => 0.15,
        "macd" => 0.20
    ];

    // Calculate scores for each indicator
    $obvScore = calculateOBVScore($symbol);
    $adlScore = calculateADLScore($symbol);
    $adxScore = calculateADXScore($symbol);
    $aroonScore = calculateAroonScore($symbol);
    $stochasticScore = calculateStochasticScore($symbol);
    $rsiScore = calculateRSIScore($symbol);
    $macdScore = calculateMACDScore($symbol);

    // Calculate the overall score
    $overallScore = ($weights["obv"] * $obvScore)
        + ($weights["adl"] * $adlScore)
        + ($weights["adx"] * $adxScore)
        + ($weights["aroon"] * $aroonScore)
        + ($weights["stochastic"] * $stochasticScore)
        + ($weights["rsi"] * $rsiScore)
        + ($weights["macd"] * $macdScore);

    // Normalize the overall score to a range between 0 and 100
    $overallScore = ($overallScore / array_sum($weights)) * 100;

    // Round the overall score to two decimal places
    $overallScore = round($overallScore, 2);

    // Return the overall score
    return $overallScore;
}


?>


