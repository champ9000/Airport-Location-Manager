<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>Places Searchbox</title>
</head>

<body>
    <div class=container-fluid>
        <div class="row-fluid">
            <div class="col-md-8" style="height: 100vh;">
                <input id="pac-input" class="controls" type="text" placeholder="Search Box">
                <div id="map"></div>
                <div id="Parameters"></div>
            </div>
            <div class="col-md-4">
                <form id="flightForm">

                    <label for="originInput">Origin:</label>
                    <div id="originText" class="flight-input">Choose an airport from the map</div>
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
                </form>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAv9-kUFeQ3e3Oh_YL-7kyoIORu0RX35Ag&libraries=places&callback=getLocation" async defer></script>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // This example adds a search box to a map, using the Google Place Autocomplete
        // feature. People can enter geographical searches. The search box will return a
        // pick list containing a mix of places and predicted search terms.

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
                    console.log(origin);
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

        getAirports();

        $('form').on('submit', function(e) {
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
                        "origin": "LAX",
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

            // send an ajax call to flights api
            $.ajax({
                    method: "POST",
                    url: "https://www.googleapis.com/qpxExpress/v1/trips/search?key=AIzaSyAv9-kUFeQ3e3Oh_YL-7kyoIORu0RX35Ag",
                    contentType: "application/json",
                    dataType: "json",
                    data: JSON.stringify(data)
                })
                .done(function(data) {
                    console.log(data);
                })
                .fail(function(data) {
                    console.log(data);
                })
                .always(function(data) {
                    //console.log(data);
                });
        });
    </script>
</body>

</html>
