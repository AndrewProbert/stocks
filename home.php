<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="utf-8" lang="english">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Home</h1>
    <p>Select Scanner Type</p>
    <a href="premarket.php">Pre-Market</a>
    <a href="intraday.php">Intraday</a>
    <a href="afterhours.php">After Hours</a>



<form method="GET" action="">
    <label for="search">Search Ticker:</label>
    <input type="text" id="search" name="search" required>
    <button type="submit">Search</button>
</form>
</body>
</html>

<?php
    





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
    

