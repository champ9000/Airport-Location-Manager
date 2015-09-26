<?php

    function getPlace() {
        $key = "AIzaSyDqZgX5tM6JinlOXqs64yvBsxfOSwvdj-w";
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=-33.8670522,151.1957362&radius=500&types=food&name=cruise&key=" . $key;

        $obj = file_get_contents($url);
        $places = json_decode($obj);

        return $url;
    }

    $test = getPlace();
    echo $test;
