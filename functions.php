<?php
$api_key = "AIzaSyDqZgX5tM6JinlOXqs64yvBsxfOSwvdj-w";

function getPlaces() {
    global $api_key;

    $radius = $_SESSION['radius'];
    $type = 'airports';
    $lat = $_SESSION['latitude'];
    $long = $_SESSION['longitude'];
    $latlong = $lat . "," . $long;

    //echo "<script>console.log('" . print_r($_SESSION) . "')";

    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$latlong."&radius=".$radius."&types=".$type."&key=".$api_key;

    $obj = file_get_contents($url);
    $places = json_decode($obj);

    //echo "<script>console.log('" . print_r($url) . "')";

    return $places;
}
