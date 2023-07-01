<!DOCTYPE html>
<html>
<head>
    <title>Live RSI Analysis</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Function to fetch and update the analysis
        function updateAnalysis() {
            var request = $.ajax({
                url: 'gld.php', // PHP script to handle the analysis
                method: 'GET'
            });

            request.done(function(response) {
                $('#analysis').html(response);
            });

            request.fail(function(jqXHR, textStatus) {
                console.error('Request failed:', textStatus);
            });
        }

        // Update the analysis initially and every 10 seconds
        $(document).ready(function() {
            updateAnalysis();
            setInterval(updateAnalysis, 1000); // 10 seconds interval
        });
    </script>
</head>
<body>
    <h1>Live RSI Analysis for GLD ETF</h1>
    <div id="analysis"></div>
</body>
</html>
