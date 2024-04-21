
<!--
Client: Professor Michael Oudshoorn
Group Name: Byte Me
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V.
Date: 4/16/2024

File Name: route.php

Description: This file serves as the main interface for querying and 
displaying possible flight routes for Comfort Airlines. It interacts
with a MySQL database to retrieve flight data based on user inputs,
including departure and arrival airports, and a specific date. It 
implements a search algorithm to find and display up to three possible
routes, considering direct and multi-stop flight options. Additional
unctionalities include map visualizations to depict routes and 
significant geographical markers for better spatial understanding.

Input: User-defined criteria for flight routes, including
'from' airport, 'to' airport, and travel date.

Output: Displays a list of up to three possible flight routes in 
both textual and graphical (map) formats. Each route details include 
flight durations, distances, and stopovers if any. The output is 
dynamically adjusted based on the user's input and the availability 
of flight data for the given date.

Functions: 
- getOptions($conn, $column, $selected): Generates HTML option
 tags for form selects based on database entries.
- getAirportCoordinates($conn, $airportCode): Fetches 
geographical coordinates for specified airport codes.
- findThreeRoutesBFS($conn, $from, $to, $date): Implements a
Breadth-First Search algorithm to determine possible routes 
from source to destination considering specified travel date.

Languages Used: PHP for server-side logic, HTML for content 
structure, CSS for styling, and JavaScript for interactive 
elements, particularly for integrating Leaflet.js for map
representations.
-->

<?php
ini_set('memory_limit', '256M'); // Increase the memory limit to 256 MB
error_reporting(E_ALL);
ini_set('display_errors', 1);

// connection to database information
$servername = "db";
$username = "byteme";
$password = "letmein";
$dbname = "comfort_airlines_byteme";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for input values
$from = $to = $date = "";

// Check if HTTP request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from'];
    $to = $_POST['to'];
    $date = $_POST['date'];
}

// function to query and get info for routes from the FLIGHT table in database 
function getOptions($conn, $column, $selected) {
    $sql = "SELECT DISTINCT $column FROM FLIGHT ORDER BY $column"; //specific query 
    $result = $conn->query($sql); // connects the result to file
    $options = "";
    while ($row = $result->fetch_assoc()) {
        $isSelected = ($row[$column] == $selected) ? ' selected' : '';
        $options .= "<option value='" . $row[$column] . "'" . $isSelected . ">" . $row[$column] . "</option>";
    }
    return $options;
}
// function to get the longitude and latitude values from the airports 
function getAirportCoordinates($conn, $airportCode) {
    $sql = "SELECT Latitude, Longitude FROM AIRPORT WHERE AirportCode = '$airportCode'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        return ['lat' => $row['Latitude'], 'lon' => $row['Longitude']]; // fetching the columns along the row
    } else {
        return null; // Return null if no data found
    }
}
/**
 * Finds up to three possible routes using a breadth-first search (BFS) algorithm based on the given departure,
 * destination, and date.
 *
 * @param mysqli $conn The database connection object.
 * @param string $from The departure airport code.
 * @param string $to The destination airport code.
 * @param string $date The departure date.
 * @return array An array of routes found.
 */
function findThreeRoutesBFS($conn, $from, $to, $date) {
    // Initialize a new queue to manage the BFS process.
    $queue = new SplQueue();
    // Initialize an array to store found routes.
    $routesFound = [];
    // Counter to limit the number of routes to three.
    $count = 0;

    // SQL query to fetch initial flights from the departure airport on the specified date.
    $sql = "SELECT *, TIMEDIFF(ScheduledArrivalTime, ScheduledDepartureTime) AS FlightDuration, Distance FROM FLIGHT WHERE DepartureAirport='$from' AND DepartureDate='$date'";
    // Execute the query.
    $initialFlights = $conn->query($sql);
    // Enqueue all initial flights to the queue with their destination airport, flight details, and route number.
    while ($initialFlight = $initialFlights->fetch_assoc()) {
        $queue->enqueue([$initialFlight['DestinationAirport'], [$initialFlight], $initialFlight['RouteNumber']]);
    }

    // Process the queue while it's not empty or until three routes are found.
    while (!$queue->isEmpty() && $count < 3) {
        // Dequeue the front element (current airport, current path, and route number).
        list($currentAirport, $path, $routeNumber) = $queue->dequeue();

        // Check if the current airport is the destination airport.
        if ($currentAirport == $to) {
            // If reached the destination, add the current path to the routes found and increment the counter.
            $routesFound[] = $path;
            $count++;
            continue; // Move to the next iteration.
        }

        // Fetch subsequent flights from the current airport on the same route and date.
        $sql = "SELECT *, TIMEDIFF(ScheduledArrivalTime, ScheduledDepartureTime) AS FlightDuration, Distance FROM FLIGHT WHERE DepartureAirport='$currentAirport' AND DepartureDate='$date' AND RouteNumber='$routeNumber'";
        $result = $conn->query($sql);
        // For each subsequent flight, add the flight to the current path and enqueue the new path with updated details.
        while ($row = $result->fetch_assoc()) {
            $newPath = $path;
            $newPath[] = $row;
            $queue->enqueue([$row['DestinationAirport'], $newPath, $routeNumber]);
        }
    }
    // Return the list of routes found.
    return $routesFound;
}
?>

<!-- into the HTML/styling and outputs -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Route Finder</title>
    <script src="https://kit.fontawesome.com/dd348bbd0a.js" crossorigin="anonymous"></script> <!-- FontAwesome script for icons -->

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color:white;
        }
        .container {
            display: flex;
            height: 90vh; /* Adjusted height to accommodate navbar */
        }
        .search-section {
            width: 33.3%;
            padding: 20px;
        }
        .map-section {
            width: 66.6%;
            height: 100%;
        }
        #map {
            height: 100%;
        }
       
   .topnav {
   overflow: hidden;
   background-color: black;
   color: white;
   margin-top:2.3%;
   height:88px;
 }


 .topnav a {
   float: right;
   text-align: center;
   background-color: black;
   text-decoration: none;
   font-size: 17px;
   color: white;
   font-weight: 400; /* Adjusted to match the other links if needed */


 }
 .topnav a:hover {
   background-color: rgba(0,0,0,0);
   color: yellow;
 }
  .topnav a.active {
   margin-right:8.3%;
 }


 .route-details {
        background-color: #333;
        color: white;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .route-segment {
        margin-bottom: 10px;
        padding: 5px;
        border-bottom: 1px solid #555;
    }
    .route-segment:last-child {
        border-bottom: none;
    }
    .route-header {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .route-stopover {
        color: #FFD700; /* Golden color for highlighting stopovers */
        font-style: italic;
    }
    .route-total {
        font-weight: bold;
        margin-top: 10px;
    }
    .extra-routes {
    display: none; // Hide the extra routes by default
}
    </style>
       <link rel="stylesheet" type="text/css" href="main.css" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
   
   
</head>
<body>
<!-- Main container with a top margin -->
<div style="margin-top:1%">
    <!-- Company logo placed absolutely on the page -->
    <img src="calol.png" width="220" style="margin-top:0%; margin-left:-3%; position:absolute">

    <!-- Navigation bar styled for clarity and visibility -->
    <div class="topnav" style="font-family: 'Inter', sans-serif; font-weight:400; font-size:13px; margin-top:1%">
        <!-- Navigation links for different pages, highlighting the 'Financial Summary' as active -->
        <a class="active" href="calc.php" style="font-weight: 200; font-size:13px; margin-top:3%; margin-left:3%;">Financial Summary</a>
        <a href="repo.php" style="font-family: 'Roboto', sans-serif; font-size:13px; margin-top:3%; margin-left:3%; font-weight:100;">Daily Report</a>
        <a href="route.php" style="font-family: 'Roboto', sans-serif; font-size:13px; margin-top:3%; font-weight: 100; font-size:13px; margin-left:3%; font-weight:200; color:yellow">Routes</a>
        <a href="index.php" style="font-family: 'Roboto', sans-serif; font-size:13px; margin-top:3%; font-weight: 100; font-size:13px; margin-left:1%; font-weight:100;">Airport Timetable</a>
    </div>
</div>
<!-- Container for the map and form -->
<div class="container">
    <!-- Section for input form to generate routes -->
    <div class="search-section">
        <!-- Form to handle flight route requests -->
        <form method="post" style="margin-left:14%">
            <br><br>
            <!-- Dropdown for selecting departure airport -->
            <label for="from">From:</label>
            <select name="from" id="from" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 100px; height: 38px; font-size:14px;">
                <?= getOptions($conn, 'DepartureAirport', $from); ?>
            </select>
            <!-- Button to swap 'from' and 'to' fields -->
            <button type="button" onclick="swapValues()" style="background-color:transparent; border:none; cursor:pointer; color:white;">
                <i class="fas fa-exchange-alt"></i>
            </button>
            <!-- Dropdown for selecting destination airport -->
            <label for="to">To:</label>
            <select name="to" id="to" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 100px; height: 38px; font-size:14px;">
                <?= getOptions($conn, 'DestinationAirport', $to); ?>
            </select>
            <br><br>
            <!-- Date picker for selecting the date of travel -->
            <label for="date">Date:</label>
            <input type="date" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 120px; height: 38px; font-size:14px;" min="2024-08-01" max="2024-08-14" name="date" id="date" value="<?= htmlspecialchars($date) ?>">
            <br><br>
            <!-- Submit button to generate routes -->
            <button type="submit" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 180px; height: 38px; font-size:14px; margin-top: 10px; margin-left:15%;" name="generate">Generate Routes</button>
            <!-- Button to clear the current route search -->
            <button type="button" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 180px; height: 38px; font-size:14px; margin-top: 10px; margin-left:15%;" onclick="window.location.href=window.location.href;">Clear Route</button>
        </form>
        <br><br>
        <!-- Container for displaying route details -->
        <div class="route-details">
    <?php
    if (isset($_POST['generate']) && !empty($from) && !empty($to) && !empty($date)) {
        $foundRoutes = findThreeRoutesBFS($conn, $from, $to, $date);
        if (!empty($foundRoutes)) {
            $fromAirport = htmlspecialchars($_POST['from']);
            $toAirport = htmlspecialchars($_POST['to']);
            $dateObject = new DateTime($date);
            $formattedDate = $dateObject->format('F jS, Y'); // Formats as 'August 1st, 2024'
            echo "<div style='margin-top:-2%; font-size: 20px; color: white; margin-top: 10px;'><strong>{$fromAirport} <span style='color: #FFE933;'> → </span> {$toAirport}  Fastest Routes </strong></div>";
            echo "<h2 style='color:white'>Date: $formattedDate</h2><br>";
            echo "<hr>";
            echo "<br>";
            foreach ($foundRoutes as $routeIndex => $route) {
                $totalDistance = 0;
                $totalDuration = 0;
                $stopovers = [];
                echo "<strong><span style='color:#FFE933'>Route " . ($routeIndex + 1) . ": </span></strong>";
                foreach ($route as $flight) {
                    $flightDuration = strtotime($flight['ScheduledArrivalTime']) - strtotime($flight['ScheduledDepartureTime']);
                    $totalDuration += $flightDuration;
                    $totalDistance += $flight['Distance'];
                    if ($flight['DepartureAirport'] != $from && $flight['DepartureAirport'] != $to) {
                        $stopovers[] = $flight['DepartureAirport'];
                    }
                    $departureTime = new DateTime($flight['ScheduledDepartureTime']);
                    $formattedDepartureTime = $departureTime->format('g:i A'); // 'g:i A' excludes seconds and adds AM/PM
                    $arrivalTime = new DateTime($flight['ScheduledArrivalTime']);
                    $formattedArrivalTime = $arrivalTime->format('g:i A');
                    echo $flight['DepartureAirport'] . " to " . $flight['DestinationAirport'] . "<br><br>";
                    echo "<span style='color:#FFE933;'>Flight Number: </span>" . $flight['FlightNumber'] . "<br>";
                    echo "<span style='color:#FFE933;'>Depart: </span>" . $formattedDate . ", " . $formattedDepartureTime . " <br> <span style='color:#FFE933;'>Arrive:</span> " . $formattedDate . ", " . $formattedArrivalTime . "<br>";
                    echo "<span style='color:#FFE933;'>Flight Duration: </span>" . gmdate("H:i", $flightDuration) . ", Distance: " . $flight['Distance'] . " miles<br><br>";
                }
                echo "<br><span style='color:#FFE933;'>Total Travel Time:</span> " . gmdate("H:i", $totalDuration) . "<br>";
                echo "<span style='color:#FFE933;'>Total Distance:</span> " . $totalDistance . " miles<br>";
                if (!empty($stopovers)) {
                    echo "<span style='color:#FFE933;'>Stopovers:</span> " . implode(", ", array_unique($stopovers)) . "<br><br>";
                } else {
                    echo "Direct Flight, no Stopovers<br><br>";
                }
                echo "<hr>";
                echo "<br>";
            }
        } else {
            echo "<h2>No available routes from " . htmlspecialchars($from) . " to " . htmlspecialchars($to) . " on " . htmlspecialchars($date) . ".</h2>";
        }
    }
    ?>
</div>

    </div>
    <!-- Map section to display route maps -->
    <div id="map" class="map-section" style="width: 67%; height: 800px;"></div>
</div>
<script>
    // Function to swap the values of 'from' and 'to' dropdowns
    function swapValues() {
        var fromSelect = document.getElementById('from');
        var toSelect = document.getElementById('to');
        var temp = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = temp;
    }
    // Initialize the map with Leaflet.js
    function initializeMap() {
        map = L.map('map').setView([37.8, -96], 4); // Set the default view of the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        // If routes were found, plot them on the map
        <?php if (isset($foundRoutes) && !empty($foundRoutes)): ?>
            <?php foreach ($foundRoutes as $routeIndex => $route): ?>
                var routeData = {
                    origin: <?= json_encode(getAirportCoordinates($conn, $route[0]['DepartureAirport'])) ?>,
                    destination: <?= json_encode(getAirportCoordinates($conn, $route[count($route) - 1]['DestinationAirport'])) ?>,
                    stopovers: []
                };
                <?php foreach ($route as $flight): ?>
                    if ('<?= $flight['DepartureAirport'] ?>' !== '<?= $from ?>' && '<?= $flight['DestinationAirport'] ?>' !== '<?= $to ?>') {
                        routeData.stopovers.push({
                            coords: <?= json_encode(getAirportCoordinates($conn, $flight['DepartureAirport'])) ?>,
                            code: '<?= $flight['DepartureAirport'] ?>'
                        });
                    }
                <?php endforeach; ?>
                var polylinePoints = [
                    [routeData.origin.lat, routeData.origin.lon],
                    ...routeData.stopovers.map(function(stop) { return [stop.coords.lat, stop.coords.lon]; }),
                    [routeData.destination.lat, routeData.destination.lon],
                    [routeData.origin.lat, routeData.origin.lon] // Close the loop by adding the origin at the end
                ];
                
                L.polyline(polylinePoints, {color: 'blue'}).addTo(map);
                // Add markers for origin, destination, and stopovers
                L.marker([routeData.origin.lat, routeData.origin.lon]).addTo(map)
                    .bindPopup('Origin: ' + '<?= $route[0]['DepartureAirport'] ?>');
                L.marker([routeData.destination.lat, routeData.destination.lon]).addTo(map)
                    .bindPopup('Destination: ' + '<?= $route[count($route) - 1]['DestinationAirport'] ?>');
                routeData.stopovers.forEach(function(stop) {
                    if (stop) {
                        L.marker([stop.coords.lat, stop.coords.lon]).addTo(map)
                            .bindPopup('Stopover: ' + stop.code);
                    }
                });
            <?php endforeach; ?>
        <?php endif; ?>
    }
    // Ensure the map is initialized when the document is fully loaded
    document.addEventListener('DOMContentLoaded', initializeMap);
</script>
</body>
</html>
