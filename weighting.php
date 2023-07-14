<?php

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
    <title>Weighting Tool</title>
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
  <br><br> 
  
  
  <h1>Investment Weighting Tool</h1>

<form method="post" action="">
    <label for="investment_amount">Investment Amount:</label>
    <input type="number" step="0.01" name="investment_amount" id="investment_amount" required><br><br>

    <label for="stocks">Stocks (comma-separated symbols):</label>
    <input type="text" name="stocks" id="stocks" required><br><br>

    <input type="radio" name="weighting_option" value="equal" checked>
    <label for="weighting_option">Equal Weighting</label>
    <input type="radio" name="weighting_option" value="price">
    <label for="weighting_option">Weight by Price</label><br><br>

    <input type="submit" value="Distribute">
</form>

<?php
// Function to fetch stock price from Yahoo Finance API
function getStockPrice($symbol)
{
    $url = "https://query1.finance.yahoo.com/v10/finance/quoteSummary/$symbol?modules=price";
    $data = file_get_contents($url);
    $data = json_decode($data, true);
    return $data['quoteSummary']['result'][0]['price']['regularMarketPrice']['raw'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investmentAmount = $_POST['investment_amount'];
    $stocks = explode(',', $_POST['stocks']);

    // Remove whitespace and sanitize symbols
    $stocks = array_map('trim', $stocks);
    $stocks = array_map('strtoupper', $stocks);

    // Fetch stock prices
    $stockPrices = array();
    foreach ($stocks as $stock) {
        $price = getStockPrice($stock);
        if ($price) {
            $stockPrices[$stock] = $price;
        }else {
            echo "Could not fetch $stock price";
        }
    }

    // Calculate weight based on the selected option
    $weightingOption = $_POST['weighting_option'];
    if ($weightingOption === 'equal') {
        $stockCount = count($stockPrices);
        $equalWeight = 1 / $stockCount;
        $stockWeights = array_fill_keys(array_keys($stockPrices), $equalWeight);
    } else {
        $totalPrice = array_sum($stockPrices);
        $stockWeights = array();
        foreach ($stockPrices as $symbol => $price) {
            $weight = $price / $totalPrice;
            $stockWeights[$symbol] = $weight;
        }
    }

    // Calculate number of shares and display results
    echo "<h2>Investment Distribution:</h2>";
    echo "<table>";
    echo "<tr><th>Stock Symbol</th><th>Stock Price</th><th>Weight</th><th>Investment Amount</th><th>Shares</th></tr>";
    foreach ($stockPrices as $symbol => $price) {
        $weight = $stockWeights[$symbol];
        $amount = $investmentAmount * $weight;
        $shares = $amount / $price;
        echo "<tr>";
        echo "<td>$symbol</td>";
        echo "<td>$price</td>";
        echo "<td>" . round($weight * 100, 2) . "%</td>";
        echo "<td>" . round($amount, 2) . "</td>";
        echo "<td>" . round($shares, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>


</body>
</html>