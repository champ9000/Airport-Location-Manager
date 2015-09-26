<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['radius'] = $_POST['radius'];
    $_SESSION['latitude'] = $_POST['latitude'];
    $_SESSION['longitude'] = $_POST['longitude'];

    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>Get places</title>
    </head>
    <body>
        <h1>Search for places!</h1>
        <form action="places.php" method="post">
            <input type="text" name="radius" placeholder="Radius" />
            <input type="hidden" name="longitude" id="longitude" />
            <input type="hidden" name="latitude" id="latitude" />
            <input type="submit" value="submit" />
        </form>
        <script>
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                }
                else {
                    x.innerHTML="Geolocation is not supported by this browser.";
                }
            }
            function showPosition(position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;
                document.getElementById("longitude").value = longitude;
                document.getElementById("latitude").value = latitude;
             }
             getLocation();
          </script>
    </body>
</html>
