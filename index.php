<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="utf-8" lang="english">
    <link rel="stylesheet" type="text/css" href="style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<header>
    <nav>
      <div class="logo">
        <a href="index.php"> <img src="gloptionW.png" alt="Logo"></a>
        <form method="POST" action="" target="">
        <input type="text"  name="symbol" placeholder="Enter stock symbol" >
        <input type="submit" name="search" value="Search">
    </form>
      </div>
      <ul class="navbar">
        <li><a href="intraday.php">Track</a></li>
        <li><a href="options.php">Options</a></li>
        <li><a href="http://localhost/gld/index.php">Gold Scanner</a></li>
        <li><a href="news.php">News</a></li>
        <li><a href="weighting.php">Weighting Tool</a></li>

      </ul>
    </nav>
  </header>
  <br>


</body>
</html>

<?php
    
/*




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




    // Check if the form is submitted
if (isset($_POST['search'])) {
    // Retrieve the symbol entered by the user
    $symbol = strtoupper($_POST['symbol']);

    // Redirect the user to the stock_info.php page with the symbol as a parameter
    header("Location: stock_info.php?symbol={$symbol}");
    exit;
}


    ?>
    

