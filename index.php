<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>Flight Search</title>
</head>

<body>
    <div class=container-fluid>
        <div class="row-fluid map-height">
            <div class="col-md-8 col-xs-12 map-height" id="map-container">
                <input id="pac-input" class="controls" type="text" placeholder="Search Box">
                <div id="map"></div>
                <div id="Parameters"></div>
            </div>
            <div class="col-md-4 col-xs-12">
                <div id="flight-form-container">

                    <label for="originInput">Origin:</label>
                    <div id="originText" class="flight-input">Please choose an airport from the map</div>
                    <input type="text" id="originInput" list="airportList" class="form-control flight-input display-none" />

                    <label for="destination">Destination:</label>
                    <input type="text" id="destination" list="airportList" class="form-control flight-input" />
                    <datalist id="airportList"></datalist>

                    <label for="departDate">Departure date:</label>
                    <input type="date" id="departDate" class="form-control flight-input" />
<!--
                    <label for="returnDate">Return date:</label>
                    <input type="date" id="returnDate" class="form-control flight-input" />
-->
                    <button id="submitFlightForm" class="btn btn-primary">Get Flights</button>
                </div>
            </div>
        </div>
        <hr>
        <div id="flight-info-loading" class="banana-plane">
        </div>
        <div class="row-fluid" id="flight-info-panels">
            <div class="col-md-12 col-xs-12">
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Current airport</h3>
                        </div>
                        <div id="currentAirport" class="panel-body">
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Other airports</h3>
                        </div>
                        <div class="panel-body">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAv9-kUFeQ3e3Oh_YL-7kyoIORu0RX35Ag&libraries=places&callback=getLocation" async defer></script>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $("#flight-form-container").hide();
        $("#flight-info-panels").hide();
        $("#flight-info-loading").hide();

        //$("#map-container").hide();
        //$("#map").hide();
        //$("#pac-input").hide();

        $( document ).ready(function() {
            //$("#map-container").fadeIn();
            $("#flight-form-container").fadeIn();
        });


        // Global origin variable
        var origin = "";
        // Keep track of the last info window so only one marker is open at a time
        var lastInfoWindow = null;

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {};
                    pos.lat = position.coords.latitude;
                    pos.lng = position.coords.longitude;
                    initAutocomplete(pos);
                });
            }
        }

        function initAutocomplete(pos) {

            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: pos.lat,
                    lng: pos.lng
                },
                zoom: 13,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
/*
            google.maps.event.addListenerOnce(map, 'idle', function(){
                $("#map-container").fadeIn();
                $("#map").fadeIn();
                $("#pac-input").fadeIn();
            });*/

            // Create the search box and link it to the UI element.
            var input = document.getElementById('pac-input');
            var searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            // Bias the SearchBox results towards current map's viewport.
            map.addListener('bounds_changed', function() {
                searchBox.setBounds(map.getBounds());
            });

            var markers = [];
            // [START region_getplaces]
            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
            searchBox.addListener('places_changed', function() {
                var places = searchBox.getPlaces();
                if (places.length == 0) {
                    return;
                }

                // Clear out the old markers.
                markers.forEach(function(marker) {
                    marker.setMap(null);
                });
                markers = [];

                // For each place, get the icon, name and location.
                var bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    var icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25)
                    };

                    // Create a marker for each place.
                    markers.push(new google.maps.Marker({
                        map: map,
                        icon: icon,
                        title: place.name,
                        position: place.geometry.location
                    }));

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                // Loop and add event listeners to markers
                markers.forEach(function(marker) {
                    marker.addListener('click', function() {
                        //map.setZoom(8);
                        var coords = marker.getPosition();
                        var lat = coords.H;
                        var lng = coords.L;
                        var center = new google.maps.LatLng(lat, lng);

                        // Bring marker to center
                        map.panTo(center);

                        // Check if the airport is in the airport list
                        var exists = false;
                        $.getJSON("js/airportInfo.json", function(data) {})
                        .done(function(data) {
                            $(data.collection1).each(function(idx, airport) {
                                if (airport.airportName.text == marker.title) {
                                    exists = true;
                                }
                            });
                        })
                        .always(function(data) {
                            if (exists) {
                                if (lastInfoWindow != null) {
                                    lastInfoWindow.close();
                                }
                                parseOrigin(marker);
                            }
                            else {
                                var data = 'Sorry, you cannot fly from here!';

                                // Close the last marker open
                                if (lastInfoWindow != null) {
                                    lastInfoWindow.close();
                                }

                                // Bring up an info window
                                var infowindow = new google.maps.InfoWindow({
                                    content: data
                                });
                                infowindow.open(map, marker);
                                lastInfoWindow = infowindow;
                            }

                        });
                    });
                });

                map.fitBounds(bounds);

            });
            // [END region_getplaces]
        }

        function parseOrigin(marker) {
            if (!$("#originText").hasClass('display-none')) {
                $("#originText").addClass('display-none');
            }
            if ($("#originInput").hasClass('display-none')) {
                $("#originInput").removeClass('display-none');
                $("#originInput").attr('disabled', true);
            }

            // Loop and find the code according to airport name
            $("#airportList > option").each(function(idx, data) {
                if ($(this).val() == marker.title) {
                    origin = $(this).text();
                    $("#originInput").val(marker.title);
                    $("#originInput").text(origin);
                }
            });
        }

        function getAirports() {
            $.getJSON("js/airportInfo.json", function(data) {
                //populate the cars datalist
                $(data.collection1).each(function(idx, airport) {
                    airportOption = "<option value=\"" + airport.airportName.text + "\">" + airport.code + "</option>";
                    $('#airportList').append(airportOption);
                });
            });
        }

        // This can probably return an object with other airlines names
        // then create a function called showOtherFlights with the parameter being the airline company
        // nevermind
        function showFlights(data) {
            var tripOptions = data.trips.tripOption;
            var duration, price, newPrice, stops, departureTime, arrivalTime, index, durationText, minutes, hours, html, newDepartureTime, stopWord, stopValue;

            for (var i = 0; i < tripOptions.length; i++) {

                departureTime = "";
                arrivalTime = "";

                duration = tripOptions[i].slice[0].duration;
                price = tripOptions[i].saleTotal;

                // Conditional stop(s)

                stopWord = (tripOptions[i].slice[0].segment.length - 1 > 1) ? " stops" : " stop";
                stopValue = tripOptions[i].slice[0].segment.length - 1;
                // If there are no stops, display Nonstop
                stops = (tripOptions[i].slice[0].segment.length - 1 > 0) ? stopValue + stopWord : "Nonstop";

                // Get the first departure time and the last arrival time
                departureTime = tripOptions[i].slice[0].segment[0].leg[0].departureTime;

                departureTime = departureTime.substr(11, 5);

                index = tripOptions[i].slice[0].segment.length - 1;
                arrivalTime = tripOptions[i].slice[0].segment[index].leg[0].arrivalTime;

                arrivalTime = arrivalTime.substr(11, 5);

                // Some parsing
                newPrice = Math.ceil(price.substr(3));
                hours = Math.floor(duration / 60);
                minutes = duration % 60;

                durationText = hours + "h " + minutes + "m";

                html = '<div class="row-fluid flight-info-row">' +
                            '<div class="col-xs-2">$' + newPrice + '</div>' +
                            '<div class="col-xs-4">' + departureTime + " &mdash; " + arrivalTime + '</div>' +
                            '<div class="col-xs-3">' + durationText + '</div>' +
                            '<div class="col-xs-3">' + stops + '</div>' +
                        '</div>';

                // Remove old html and append new
                $("#currentAirport").remove(".flight-info-row");
                $("#currentAirport").append(html);
            }
        }

        // Initialize the data list
        getAirports();

        // Form submit code
        $('#submitFlightForm').on('click', function(e) {
            e.preventDefault();
            // Get inputs

            var destination = "";

            // Loop and find the code according to airport name
            $("#airportList > option").each(function(idx, data) {
                if ($(this).val() == $("#destination").val()) {
                    destination = $(this).text();
                }
            });

            var departDate = $("#departDate").val();
            //var returnDate = $("#returnDate").val();

            var data = {
                "request": {
                    "slice": [{
                        "origin": origin,
                        "destination": destination,
                        "date": departDate
                    }],
                    "passengers": {
                        "adultCount": 1,
                        "infantInLapCount": 0,
                        "infantInSeatCount": 0,
                        "childCount": 0,
                        "seniorCount": 0
                    },
                    "solutions": 20,
                    "refundable": false
                }
            };

            // Show loading image
            $("#flight-info-loading").addClass("banana-plane-rotate");
            $("#flight-info-loading").fadeIn();

            // send an ajax call to flights api
            $.ajax({
                    method: "POST",
                    url: "https://www.googleapis.com/qpxExpress/v1/trips/search?key=AIzaSyAv9-kUFeQ3e3Oh_YL-7kyoIORu0RX35Ag",
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify(data)
                })
                .done(function(data) {
                    // Add the fly-away class once the plane has fully rotated
                    $(".banana-plane-rotate").one('animationiteration webkitAnimationIteration', function() {
                        $("#flight-info-loading").removeClass("banana-plane-rotate");
                        $("#flight-info-loading").addClass("banana-plane-fly-away");
                        $("#flight-info-loading").fadeOut("slow", function() {
                            showFlights(data);
                            $("#flight-info-panels").fadeIn();
                        });
                    });
                })
                .fail(function(data) {
                    console.log(data);
                })
                .always(function(data) {
                    // After the plane is done rotating and the json is done loading, the plane flys away
                    /*$(".banana-plane-rotate").one("animationiteration webkitAnimationIteration", function() {
                        $("#flight-info-loading").removeClass("banana-plane-rotate");
                        $("#flight-info-loading").addClass("banana-plane-fly-away");
                        $("#flight-info-loading").fadeOut();
                    });*/

                    // When the plane flys away, stop displaying it
                    /*$(".banana-plane-fly-away").one("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function() {
                        $("#flight-info-loading").removeClass("banana-plane-fly-away");
                        //$("#flight-info-loading").addClass("display-none");
                    });*/

                });
        });

    </script>
</body>

</html>
