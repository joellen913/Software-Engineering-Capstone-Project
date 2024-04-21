<?php 
ob_start();  // Turn on output buffering
session_start(); // Start the session to store language selection

/*
Client: Professor Michael Oudshoorn 
Group Name: Byte Me
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V. 
Date: 04/16/2024
File Name: index.php


Description: This file serves as the backend and frontend interface for
displaying airport flight information, including arrivals and departures.
It connects to a database for retrieving flight and airport data, handles
user input from a dropdown menu to select an airport, and displays formatted
flight information based on the selection. The main functionalities include
querying the database for arrivals and departures data, formatting and
displaying this data in an HTML structure, and allowing the user to
toggle between viewing arrivals or departures.


Input: User-selected airport code from a dropdown menu.


Output: Displays flight information for arrivals and departures including
flight number, number of passengers, destination or departure airport,
scheduled and actual arrival or departure times, and the aircraft's tail
number. It also displays the flight status (e.g., On-Time, Delayed, Cancelled, Early).


Functions: formatField (for HTML formatting of data), database query
preparation and execution, user input handling, and dynamic content
display based on user selection.


Languages Used: PHP for backend logic and data handling, HTML for
structure, CSS for styling, and JavaScript for dynamic content display.
*/



// Check if language form has been submitted
if(isset($_POST['language'])) {
    $_SESSION['language'] = $_POST['language']; // Store selected language in session
}

// Set default language to English if no language is set
if(!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

$flightNumber = '';

// Before attempting to access `$flightDetails[$flightNumber]`, check if it exists
if (isset($flightDetails[$flightNumber])) {
    $ticketPrice = $flightDetails[$flightNumber]['ticketPrice'] ?? 0; // Use null coalescing operator to provide a default value
} else {
    $ticketPrice = 0; // Default value if not set
}

// Check for null before formatting the number
if ($ticketPrice !== null) {
    $formattedPrice = number_format($ticketPrice, 2);
} else {
    $formattedPrice = number_format(0, 2);
}


// Function to translate English to French
function translate($text, $language) {
    $translations = [ //all the translations 
        'Flight Number' => ['en' => 'Flight Number', 'fr' => 'Numéro de vol'],
        'Number of Passengers' => ['en' => 'Number of Passengers', 'fr' => 'Nombre de passagers'],
        'Flight Date' => ['en' => 'Flight Date', 'fr' => 'Date du vol'],
        'hours' => ['en' => 'hours', 'fr' => 'heures'],
        'No. Of Passengers' => ['en' => 'No. Of Passengers', 'fr' => 'No. de passagers'],
        'Departure Airport' => ['en' => 'Departure Airport', 'fr' => 'Aéroport de départ'],
        'Arrival Airport' => ['en' => 'Arrival Airport', 'fr' => 'Aéroport de darrivée'],
        'Scheduled Depart Time' => ['en' => 'Scheduled Depart Time', 'fr' => 'Heure de départ prévue'],
        'Scheduled Arrival Time' => ['en' => 'Scheduled Arrival Time', 'fr' => 'Heure darrivée prévue'],
        'Actual Depart Time' => ['en' => 'Actual Depart Time', 'fr' => 'Heure de départ réelle'],
        'Actual Arrival Time' => ['en' => 'Actual Arrive Time', 'fr' => 'Heure de départ réelle'],
        'Tail Number' => ['en' => 'Tail Number', 'fr' => 'Numéro de queue'],
        'Status' => ['en' => 'Status', 'fr' => 'Statut'],
        'Flight Distance' => ['en' => 'Flight Distance', 'fr' => 'Distance de vol'],
        'Departures' => ['en' => 'Departures', 'fr' => 'Départs'],
        'Arrivals' => ['en' => 'Arrivals', 'fr' => 'Arrivées'],
        'Flight' => ['en' => 'Flight', 'fr' => 'Vol'],
        '$' => ['en' => '$', 'fr' => '€'],
        'Departure Airport' => ['en' => 'Departure Airport', 'fr' => 'Aéroport de départ'],
    'Destination Airport' => ['en' => 'Destination Airport', 'fr' => 'Aéroport de destination'],
    'Show Flights' => ['en' => 'Show Flights', 'fr' => 'Afficher les vols'],
        'Switch Airports' => ['en' => 'Switch Airports', 'fr' => 'Changer Daéroport'],
        'Show Flights' => ['en' => 'Show Flights', 'fr' => 'Afficher les vols'],
        'Ticket Price' => ['en' => 'Ticket Price', 'fr' => 'Prix du billet'],
        'Flight Duration' => ['en' => 'Flight Duration', 'fr' => 'Durée du vol'],
        'Passenger Density' => ['en' => 'Passenger Density', 'fr' => 'Densité des passagers'],
        'No Flights Selected' => ['en' => 'No Flights Selected', 'fr' => 'Aucun vol sélectionné'],
    'No flights due to 150 mile radius minimum' => ['en' => 'No flights due to 150 mile radius minimum', 'fr' => 'Pas de vols en raison d’un rayon minimal de 150 miles'],
    'No flights available for the selected route and date.' => ['en' => 'No flights available for the selected route and date.', 'fr' => 'Aucun vol disponible pour l’itinéraire et la date sélectionnés.'],
    
        'miles' => ['en' => 'miles', 'fr' => 'km']
    ];

    return $translations[$text][$language] ?? $text;
}



// Define a constant for the USD to EUR exchange rate
define('USD_TO_EUR_RATE', 0.93);

// Function to convert an amount from USD to euros
function convertCurrencyToEuro($amount) {
    return round($amount * USD_TO_EUR_RATE, 2); // Convert to euros and round to two decimal places
}

// Function to convert distance from miles to kilometers
function convertDistance($miles) {
    // Access global session within the function
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en';  // Default to English
    }
    // Check the session language and convert distance accordingly
    // If the language is French, convert miles to kilometers
    // Otherwise, return the distance as it is (in miles)
    return $_SESSION['language'] == 'fr' ? round($miles * 1.60934, 2)  : $miles ;
}

    
// Function to format currency by replacing the dollar sign with the euro symbol
function formatCurrency($amount) {
    return str_replace('$', '€', $amount); // to replace dollar sign with euro symbol
}

function formatTimeField($time, $maxChars) {
    // Convert to 12-hour format with AM/PM
    if (empty($time) || $time == '00:00:00') {
        $formattedTime = 'N/A'; // Handle cases where time is not set
    } else {
        $dateTime = DateTime::createFromFormat('H:i:s', $time);
        $formattedTime = $dateTime ? $dateTime->format('g:i A') : 'Invalid Time';
    }
    // Initialize an empty string to start building the formatted output
    $formatted = '';
    // Ensure $formattedTime is treated as a string, even if null is passed
    $formattedTime = (string) $formattedTime;

    // Get the actual length of the input string
    $length = strlen($formattedTime);

    // Determine the lesser of the actual length or the specified maximum characters to handle strings shorter than $maxChars
    $minLength = min($length, $maxChars); 

    // Iterate over the string up to minLength and wrap each character in a span for formatting
    for ($i = 0; $i < $minLength; $i++) {
        // Use htmlspecialchars to prevent XSS attacks by escaping HTML entities
        $formatted .= "<span class='char-box'>" . htmlspecialchars($formattedTime[$i], ENT_QUOTES, 'UTF-8') . "</span>";
    }

    // If the string is shorter than maxChars, fill the remaining space with non-breaking spaces inside spans
    for ($i = $length; $i < $maxChars; $i++) {
        $formatted .= "<span class='char-box'>&nbsp;</span>"; // Non-breaking space for empty box
    }

    // Return the fully formatted string
    return $formatted;
}




// end of for language //

// output errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$host = 'db'; // or 'localhost'
$user = 'byteme';
$password = 'letmein';
$database = 'comfort_airlines_byteme';

// Establishing a connection to the database
$connect = mysqli_connect($host, $user, $password, $database);

// Check connection using assert
assert($connect, new AssertionError("Connection failed: " . mysqli_connect_error()));




// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

/**
 
 * @param string $string The input string to be formatted.
 * @param int $maxChars The maximum number of characters to be displayed.
 * @return string A formatted string where each character up to $maxChars is wrapped in a `<span>` tag. If the string is shorter than $maxChars, non-breaking spaces are added to maintain the length.
 */
function formatField($string, $maxChars) {
   // Initialize an empty string to start building the formatted output
   $formatted = '';

   // Ensure $string is treated as a string, even if null is passed
   $string = (string) $string;

   // Get the actual length of the input string
   $length = strlen($string);

   // Determine the lesser of the actual length or the specified maximum characters to handle strings shorter than $maxChars
   $minLength = min($length, $maxChars);

   // Iterate over the string up to minLength and wrap each character in a span for formatting
   for ($i = 0; $i < $minLength; $i++) {
       // Use htmlspecialchars to prevent XSS attacks by escaping HTML entities
       $formatted .= "<span class='char-box'>" . htmlspecialchars($string[$i], ENT_QUOTES, 'UTF-8') . "</span>";
   }

   // If the string is shorter than maxChars, fill the remaining space with non-breaking spaces inside spans
   for ($i = $length; $i < $maxChars; $i++) {
       $formatted .= "<span class='char-box'>&nbsp;</span>"; // Non-breaking space for empty box
   }

   // Return the fully formatted string
   return $formatted;
}


function formatFieldtwo($string, $maxChars, $extractLast = 0) {
    // Initialize the variable to hold the formatted string.
    $formatted = '';

    // Ensure the input is treated as a string.
    $string = (string) $string;

    // If $extractLast is greater than 0, truncate the string to the specified number of characters from the end.
    if ($extractLast > 0) {
        $string = substr($string, -$extractLast);
    }

    // Calculate the length of the string and determine the minimum length to process based on $maxChars.
    $length = strlen($string);
    $minLength = min($length, $maxChars);

    // Loop through the string up to $minLength and wrap each character in a span.
    for ($i = 0; $i < $minLength; $i++) {
        // Use htmlspecialchars to prevent XSS and ensure proper encoding of characters in HTML.
        $formatted .= "<span class='char-box'>" . htmlspecialchars($string[$i], ENT_QUOTES, 'UTF-8') . "</span>";
    }

    // If the string is shorter than $maxChars, add placeholders to maintain the layout.
    for ($i = $length; $i < $maxChars; $i++) {
        // Use a non-breaking space as a placeholder.
        $formatted .= "<span class='char-box'>&nbsp;</span>";
    }

    // Return the formatted string.
    return $formatted;
}

 // Initialization of the noFlightsMessage with a default value
 $noFlightsMessage = translate("No Flights Selected", $_SESSION['language']);


// Check if both departure and destination airports have been selected by the user
if (!empty($_POST['departureAirportCode']) && !empty($_POST['destinationAirportCode'])) {
    $selectedDepartureAirportCode = $_POST['departureAirportCode'];
    $selectedDestinationAirportCode = $_POST['destinationAirportCode'];

    // Set a specific message for certain airport combinations within 150 mile radius
    $shortDistanceAirports = [
        ['SAN', 'LAX'],
        ['LAX', 'SAN'],
        ['PHL', 'JFK'],
        ['JFK', 'PHL'],
        ['TPA', 'MCO'],
        ['MCO', 'TPA'],
        ['PHL', 'DCA'],
        ['DCA', 'PHL']
    ];

    // CHECKS if there is a minimum for the shortest distance airports, pairs them to number, also translates to french 
    foreach ($shortDistanceAirports as $pair) {
        if (($selectedDepartureAirportCode == $pair[0] && $selectedDestinationAirportCode == $pair[1]) ||
            ($selectedDepartureAirportCode == $pair[1] && $selectedDestinationAirportCode == $pair[0])) {
                $noFlightsMessage = translate("No flights due to 150 mile radius minimum", $_SESSION['language']);
                break;
        }
    }

    // Execute SQL only if departure and destination codes are valid
    if ($noFlightsMessage == "No Flights Selected") {
        // SQL query for departures including the flight date from the AIRCRAFT table
        $departuresQuery = "SELECT F.FlightNumber, F.NumberOfPassengers,
         F.DepartureAirport, F.DestinationAirport, F.ScheduledDepartureTime,
          F.ScheduledArrivalTime, F.ActualDepartureTime,  F.ActualArrivalTime, 
          F.TailNumber, F.ArrivalDate, F.DepartureDate, F.Distance, F.TimeZoneDepart, F.TimeZoneArrival FROM FLIGHT F 
          INNER JOIN AIRCRAFT A ON F.TailNumber = A.TailNumber WHERE
           F.DepartureAirport = ? AND F.DestinationAirport = ? AND
            F.DepartureDate = ? ORDER BY F.ScheduledDepartureTime;";

        // Prepare and execute the departures query
        $stmt = mysqli_prepare($connect, $departuresQuery);
        mysqli_stmt_bind_param($stmt, "sss", $selectedDepartureAirportCode, 
        $selectedDestinationAirportCode, $_POST['departureDate']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $departures = [];
        $flightsFound = false;  // Flag to check if any flights were found
        while ($row = mysqli_fetch_assoc($result)) {
            $row['FormattedDistance'] = convertDistance($row['Distance'], $_SESSION['language']);
    $departures[] = $row; // Assuming you're preparing an array of departures

            }
        // checks if no flights are found 
        if (!$flightsFound) {
            $noFlightsMessage = translate("No flights available for the selected 
            route and date.", $_SESSION['language']);
        }
    }
}

// Retrieve departure and destination airport codes from POST data
$selectedDepartureAirportCode = isset($_POST['departureAirportCode']) ? $_POST['departureAirportCode'] : '';
$selectedDestinationAirportCode = isset($_POST['destinationAirportCode']) ? $_POST['destinationAirportCode'] : '';
$selectedDepartureDate = isset($_POST['departureDate']) ? $_POST['departureDate'] : '';


// SQL query for departures including the flight date from the AIRCRAFT table
$departuresQuery = 
"SELECT F.FlightNumber, F.NumberOfPassengers, F.DepartureAirport, F.DestinationAirport, F.ScheduledDepartureTime, F.ScheduledArrivalTime, F.ActualDepartureTime,  F.ActualArrivalTime, F.TailNumber, F.ArrivalDate, F.DepartureDate, F.Distance, F.TimeZoneDepart, F.TimeZoneArrival
FROM FLIGHT F 
INNER JOIN AIRCRAFT A 
ON F.TailNumber = A.TailNumber 
WHERE F.DepartureAirport = ? 
AND F.DestinationAirport = ? 
AND F.DepartureDate = ? 
ORDER BY F.ScheduledDepartureTime;";

// Prepare and execute the departures query
$stmt = mysqli_prepare($connect, $departuresQuery);
mysqli_stmt_bind_param($stmt, "sss", $selectedDepartureAirportCode, $selectedDestinationAirportCode, $selectedDepartureDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$departures = [];
$flightsFound = false;  // Flag to check if any flights were found

// Iterate through each row fetched from the result set until there are no more rows
while($row = $result->fetch_assoc()) {
    
    $flightDuration = 'N/A'; // Default value if times are not set
    assert(isset($row['FlightNumber']) && isset($row['NumberOfPassengers']), new AssertionError("Data integrity check failed for departures"));

    if (!empty($row['ActualDepartureTime']) && !empty($row['ActualArrivalTime'])) {
        // Assuming the timezone needs to be set

        
        $departureTime = $row['ActualDepartureTime']; // Assuming the format is 'H:i:s'
        $arrivalTime = $row['ActualArrivalTime']; // Assuming the format is 'H:i:s'

        // Convert departure time and arrival time to DateTime objects
        $departureTime = new DateTime($departureTime);
        $arrivalTime = new DateTime($arrivalTime);

        // Retrieve time zone information from the row
        $departureTimeZone = $row['TimeZoneDepart'];
        $arrivalTimeZone = $row['TimeZoneArrival'];

        // Calculate the time zone offset between departure and arrival time zones
        $timeZoneOffset = $departureTimeZone - $arrivalTimeZone;
    
        // This will reflect the timezone difference
        $interval = $departureTime->diff($arrivalTime);
        $interval->h += $timeZoneOffset; // Adjust the hours based on the timezone difference

        // Retrieve the translation for the word 'hours' based on the current language stored in the session
        $hoursTranslation = translate('hours', $_SESSION['language']);

        // Format the interval with the translated text
        $flightDuration = $interval->format("%h $hoursTranslation %i minutes");

    }

    // Assign the calculated flight duration to the 'FlightDuration' key in the $row array
    $row['FlightDuration'] = $flightDuration;

    // Add teh modified $row to the $departure array
    $departures[] = $row;
    $flightsFound = true;  // Set to true if any row is fetched

}





// SQL query for arrivals including the flight date from the AIRCRAFT table
$arrivalsQuery = "SELECT F.FlightNumber, F.NumberOfPassengers, F.DepartureAirport, F.DestinationAirport, F.ScheduledDepartureTime, F.ScheduledArrivalTime, F.ActualArrivalTime, F.ActualDepartureTime, F.TailNumber, F.DepartureDate, F.ArrivalDate, F.Distance FROM FLIGHT F INNER JOIN AIRCRAFT A ON F.TailNumber = A.TailNumber WHERE F.DepartureAirport = ? AND F.DestinationAirport = ? AND F.DepartureDate = ? ORDER BY F.ScheduledDepartureTime;";

// Prepare and execute the arrivals query
$stmt = mysqli_prepare($connect, $arrivalsQuery);
mysqli_stmt_bind_param($stmt, "sss", $selectedDepartureAirportCode, $selectedDestinationAirportCode, $selectedDepartureDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// loop through each row feteched from teh database result set
$arrivals = [];
while($row = mysqli_fetch_assoc($result)) {
    $arrivals[] = $row;
}

// Fetch all airports for dropdown options
$airportsQuery = 'SELECT AirportCode FROM AIRPORT ORDER BY AirportCode';
$airportsResult = mysqli_query($connect, $airportsQuery);

// loop through each row fetched from the database result set
$airports = [];
while($row = mysqli_fetch_assoc($airportsResult)) {
    $airports[] = $row;
}


// Initialize $flightDetails array
$flightDetails = [];


// Output the ticket price

//  function calls to populate $flightDetails
foreach (array_merge($departures, $arrivals) as $flight) {
    $flightNumber = $flight['FlightNumber'];
    $distance = isset($flight['Distance']) ? $flight['Distance'] : 'N/A';

     // Convert distance based on selected language
     $convertedDistance = convertDistance($distance, $_SESSION['language']);
     $formattedDistance = $convertedDistance . ($_SESSION['language'] == 'fr' ? ' km' : ' miles');
 
    // Sample calculations 
    $passengerDensity = $flight['NumberOfPassengers']; //  direct assignment
    $distance = isset($flight['Distance']) ? $flight['Distance'] : 'N/A'; 
    $travelTime = isset($flight['FlightTime']) ? $flight['FlightTime'] : 'NA'; 
    $ticketPrice = calculateTicketPrice($flight); 

    // Populate flight details
    $flightDetails[$flightNumber] = [
        'passengerDensity' => $passengerDensity,
        'flightDistance' => $distance,
        'travelTime' => $travelTime,
        'ticketPrice' => $ticketPrice,
    ];
}


// function to calculate ticket price
function calculateTicketPrice($flight) {
    // Extract necessary details from the flight
    $departureAirport = $flight['DepartureAirport'];
    $destinationAirport = $flight['DestinationAirport'];
    $numberOfPassengers = $flight['NumberOfPassengers'];
    $distance = isset($flight['Distance']) ? $flight['Distance'] : 0;
    $tailNumber = $flight['TailNumber'];
    
    // Determine gallons per mile based on tail number prefix
    $prefix = explode('-', $tailNumber)[0];
    $gallonsPerMile = match($prefix) {
        'B7376' => 2.07711521,
        'B7378' => 1.95235582,
        'AB1' => 1.70554916,
        'AB3' => 1.57378012,
        'B7474' => 25.980827,
        default => 2, // Fallback value if prefix does not match
    };

    // Determine leasing fee based on tail number prefix
    $leasingFee = match($prefix) {
        'B7376' => 8049.41,
        'B7378' => 8870.78,
        'AB1' => 6308.11,
        'AB3' => 7490.88,
        'B7474' => 9856.42, // 300k divided by avg days in a month 
        default => 8000, // Fallback value if prefix does not match
    };

    // Calculate fuel fee
    // Update fuel fee for flights related to CDG
    if ($departureAirport == 'CDG' || $destinationAirport == 'CDG') {
        $fuelFee = $distance * $gallonsPerMile * 2.10; // Euro 1.97 per liter
    } else {
        $fuelFee = $distance * $gallonsPerMile * 6.19; // Default fuel fee in USD per gallon
    }

    // Terminal fee is static: $2000 for takeoff and $2000 for landing
    // Update takeoff and landing fees for flights related to CDG
    if ($departureAirport == 'CDG' || $destinationAirport == 'CDG') {
        $terminalFee = 4484; // Euro 2100 for CDG to USD and to account for both takeoff and landing
    } else {
        $terminalFee = 4000; // Default terminal fee in USD and to account for both takeoff and landing
    }

    // Calculate total operational costs
    $operationalCosts = $fuelFee + $terminalFee + $leasingFee;

    // Calculate ticket price per passenger
    // Ensure number of passengers is greater than 0 to avoid division by zero
    $ticketPrice = $numberOfPassengers > 0 ? (($operationalCosts / $numberOfPassengers)*1.02) : 0;

    assert($ticketPrice >= 0, new AssertionError("Ticket price calculation error for flight " . $flight['FlightNumber']));
    return round($ticketPrice, 2); // Returning the ticket price rounded to 2 decimal places
}

// Assign the selected departure ariport code $departureAirportName
$departureAirportName = $selectedDepartureAirportCode; 
// Assign the selected destination airport code to $destinationAirportName
$destinationAirportName = $selectedDestinationAirportCode; 


// Define paths or URLs to the flag images
$flagImages = [
    'en' => 'americanflag.webp',  
    'fr' => 'frenchflag.png'
];

// Check if the session language is set and output the corresponding flag
if (isset($_SESSION['language'])) {
    $flagUrl = $flagImages[$_SESSION['language']];
    echo " <img src='" . htmlspecialchars($flagUrl) . "' alt='Flag' style='width: 30px; margin-left:90%; margin-top:4%'>";
} else {
    // Default to English flag if no session language is set
    echo "<img src='" . htmlspecialchars($flagImages['en']) . "' alt='Flag' style='width: 30px;margin-left:90%; margin-top:4% '>";
}


// Check the session language and convert the price if necessary
if ($_SESSION['language'] == 'fr') {
    $ticketPrice = convertCurrencyToEuro($ticketPrice); // Convert the price to euros
    $currencySymbol = '€';
} else {
    $currencySymbol = '$';
}

// Generate HTML content for the modal dialog box
$modalContent = "
<div style='text-align: center; margin-top:10%'>
    <strong>" . translate('Ticket Price', $_SESSION['language']) . ": " . $currencySymbol . number_format($ticketPrice, 2) . "</strong><br>
</div>";


// Close the database connection
mysqli_close($connect);
ob_end_flush(); // Send the output buffer and turn off output buffering

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Airport Flight Information</title> <!-- The title of the webpage, shown in the browser's title bar or tab -->
   <script src="https://kit.fontawesome.com/dd348bbd0a.js" crossorigin="anonymous"></script> <!-- FontAwesome script for icons -->
   <!-- Multiple Google font styles are imported below for use throughout the webpage. These include various families, weights, and styles to enhance textual presentation. -->
   <link href="https://fonts.googleapis.com/css?family=Fira+Sans&display=swap" rel="stylesheet">
   <!-- Preconnect to Google's font domains for performance improvement -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <!-- Noto Sans and Raleway fonts in different weights and styles for diverse textual aesthetics -->
   <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <!-- Additional font imports for Quantico, Kode Mono, Roboto, and more, providing a wide range of typographical options -->
   <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Kode+Mono:wght@400..700&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
   <!-- Custom CSS file link for site-wide styling rules -->
   <link rel="stylesheet" type="text/css" href="main.css" />
   <!-- Inline CSS for specific style rules directly applied within the HTML document, including body background, text color, table styling, and more -->
   <style type="text/css">
       body{color:white; background-color:black; font-family: 'Courier New', monospace; font-weight: 397; white-space:nowrap}
       td, th {border: 1px solid white; }
       tr:nth-child(even) {background-color: rgba(255, 255, 255, 0.5);} /* Alternating row coloring for better readability */
       .topnav {
           overflow: hidden;
           background-color: rgba(0,0,0,0);
           color: white;
           margin-top:2.3%;
       }


/* Responsive design adjustments and specific element styles follow, focusing on navigation, button appearances, and special class effects like .char-box for individual character styling */


/* Style for modal background */
.modal-bg {
 display: none;
 position: fixed;
 z-index: 1;
 left: 0;
 top: 0;
 color:black;
 width: 100%;
 height: 120%;
 overflow: auto;
 background-color: rgba(0,0,0,0.4);
}


/* Style for modal content */
.modal-content {
 position: relative;
 background-color: #fefefe;
 margin: 15% auto;
 padding: 20px;
 border: 1px solid #888;
 width: 80%;
 max-width: 450px;
 height: 130px;
 text-align: center;
}


.close {
   cursor: pointer;
   position: absolute;
   top: 0;
   right: 10px;
   font-size: 25px;
   font-weight: bold;
   color: #000;
}


/* Blur effect */
.blur-effect {
 filter: blur(5px);
}


/* Blur effect for background */
.blur-effect {
 filter: blur(8px);
}


 body{    transform: scale(1.25);
   transform-origin: top left; /* Adjust as needed */
   width: 79%; /* Compensate for the scaling to avoid horizontal scrollbar */
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


 .row-container {
           display: flex; /* Use flexbox to create a row */
           border-radius: 10px; /* Set the border-radius for the entire row */
           overflow: hidden; /* Ensure that child elements don't exceed the rounded corners */
       }


       /* Style for individual items in the row */
       .row-item {
           margin: 10px; 
           padding: 10px; 
           background-color: #f0f0f0;
           border: 1px solid #ccc; 
           border-radius: 5px; 
       }


       table {
   border-collapse: separate;
   border-spacing: 0 0.5em;}


   tr td:first-of-type {
 border-top-left-radius: 7px;
 border-bottom-left-radius: 7px;
}


tr td:last-of-type {
 border-top-right-radius: 7px;
 border-bottom-right-radius: 7px;
}


.active-button {
           background-color:#FFE933; 
           color:black;
       }


       .button-style {
           border: 0.5px solid white; border-radius:0px; width: 130px;height: 45px;font-size:15px; margin-top:4%;
       }


       .char-box {
    display: inline-block;
    width: 20px; 
    height: 20px;
    line-height: 20px; 
    border: 1px solid #ccc; /* Box border */
    text-align: center;
    margin-right: -1px; /*  space between inline-block elements */
    background-color: black; /* Box background */
    color: white; /* Text color */
    vertical-align: middle; /* Aligns all char-box elements vertically */
}


.fa-border {
   border: 3px solid #000; /*  the border color */
   border-radius: 5px; /* for rounded corners */
}
 /* end of navigation */


       .close-btn {
           position: absolute;
           top: 10px;
           right: 10px;
           cursor: pointer;
       }


       /* CSS for blur effect */


   </style>


   <script>





function changeLanguage() {
    document.getElementById('languageForm').submit();
}


document.addEventListener('DOMContentLoaded', () => {
   document.querySelectorAll('.flight-number').forEach(item => {
       item.addEventListener('click', function(e) {
        
           e.preventDefault();


          
           // Extract flight details from data attributes
           const departureAirport = this.dataset.departureAirport;
           const destinationAirport = this.dataset.destinationAirport;
           const flightNumber = this.dataset.flightNumber;
           const passengerDensity = this.dataset.passengerDensity;
           const flightDistance = this.dataset.flightDistance;
           const flightDuration = this.dataset.travelTime;
           const ticketPrice = this.dataset.ticketPrice;
           const tailNumber = this.dataset.tailNumber; //  this represents the Airbus number
           const distanceInKilometers = (parseFloat(flightDistance) * 1.60934).toFixed(2);

           


           // Extract the prefix from the tail number
           let tailNumberPrefix = tailNumber.split('-')[0]; // for tail number format thats different


           // Determine the aircraft model based on the tail number prefix
           let aircraftModel;
           switch (tailNumberPrefix) {
               case 'B7376':
                   aircraftModel = 'Boeing 737-600';
                   break;
               case 'B7378':
                   aircraftModel = 'Boeing 737-800';
                   break;
               case 'AB1':
                   aircraftModel = 'Airbus A200-100';
                   break;
               case 'AB3':
                   aircraftModel = 'Airbus A220-300';
                   break;
               case 'B7474':
                   aircraftModel = 'Boeing 747-400';
                   break;
               default:
                   aircraftModel = 'Unknown Aircraft!';
           }


            // Construct the modal content
            let modalContent = `
            <div style="font-family: 'Inter', sans-serif; font-weight:400">
               <div style="margin-top:10%">
                   <div style="text-align: left; margin-top:-10%;position:absolute ">
                      <div style="font-size: 133%"> <strong>${departureAirport} → ${destinationAirport}</strong><br></div>
                      <?= translate('Flight', $_SESSION['language']); ?> ${flightNumber}
                       

                       <br><br>
                   </div>
                   <div style="text-align: right; margin-top:-10%; margin:right; margin-left:64%; position:absolute">
                       ${aircraftModel}
                   </div>
                   <div style="text-align: center; margin-top:10%">
                   <div style="font-size: 133%"> <?= $modalContent ?>
</div>
                   <?= translate('Flight Duration', $_SESSION['language']); ?>: ${flightDuration}
                       </br>
                   </div>
               </div>
                  
                       <div style="position: absolute; bottom: 10px; left: 0; right: 0; display: flex; justify-content: space-between; padding: 0 5%;">
               <div style="text-align: left;">

               <?php 
    if (isset($flightDetails[$flightNumber])) {
        $flightDistance = $flightDetails[$flightNumber]['flightDistance'];  // Accessing the distance.
        $convertedDistance = convertDistance($flightDistance, $_SESSION['language']);  // Convert the distance.
        $distanceUnit = $_SESSION['language'] == 'fr' ? 'km' : 'miles';  // Decide the unit based on the language.
    } else {
        $convertedDistance = "N/A";
        $distanceUnit = $_SESSION['language'] == 'fr' ? 'km' : 'miles';
    }
?>



<?= translate('Flight Distance', $_SESSION['language']); ?>: <?= htmlspecialchars($convertedDistance) . " " . htmlspecialchars($distanceUnit); ?>
               </div>
               <div style="text-align: right;">
               <?= translate('Number of Passengers', $_SESSION['language']); ?>: ${passengerDensity}
               </div>
           </div>
           </div>
              
           `;
      


           // Set the modal content
           document.querySelector('#modal-content-text').innerHTML = modalContent;


           // Show the modal
           document.querySelector('.modal-bg').style.display = 'block';
           document.querySelector('#main-content').classList.add('blur-effect');
       });
   });
  
//close mod
   document.querySelector('.close').addEventListener('click', function() {
       document.querySelector('.modal-bg').style.display = 'none';
       document.querySelector('#main-content').classList.remove('blur-effect'); // Remove blur from main content
   });
});


document.querySelector('.flight-number').addEventListener('click', function(e) {
   e.preventDefault();
   const flightNumber = this.innerText;
   const modal = document.querySelector('.modal-bg');
   document.querySelector('#modal-content-text').innerText = 'Flight Number: ' + flightNumber;
   modal.style.display = 'block';
   document.querySelector('#main-content').classList.add('blur-effect');
});


document.querySelector('.close').addEventListener('click', function() {
   document.querySelector('.modal-bg').style.display = 'none';
   document.querySelector('#main-content').classList.remove('blur-effect');
});






function swapAirports() {
   var departureSelect = document.querySelector('select[name="departureAirportCode"]');
   var destinationSelect = document.querySelector('select[name="destinationAirportCode"]');
  
   // Swap the values
   var temp = departureSelect.value;
   departureSelect.value = destinationSelect.value;
   destinationSelect.value = temp;
}








     /**
* Function to display either the arrivals or departures section and hide the other.
* This is achieved by changing the CSS display property of the respective sections.
*
* @param {string} section The section to show. This should be either 'arrivals' or 'departures'.
*/
function showSection(section) {
   // Obtain references to the arrivals and departures HTML elements by their IDs.
   var arrivals = document.getElementById('arrivals');
   var departures = document.getElementById('departures');


   // Check if the 'section' parameter is set to 'arrivals'.
   if (section === 'arrivals') {
       // If 'arrivals' is the section to show, set its display style to 'block' to make it visible.
       arrivals.style.display = 'block';
       // Meanwhile, hide the departures section by setting its display style to 'none'.
       departures.style.display = 'none';
   } else {
       // If 'departures' is the section to show (or any value other than 'arrivals'),
       // set the arrivals section to be hidden and the departures section to be visible.
       arrivals.style.display = 'none';
       departures.style.display = 'block';
   }
}


/**
* Toggles the active state of a button based on its ID.
* All buttons with the class 'button-style' will have the 'active-button' class removed,
* then the specified button will have the 'active-button' class added to it.
* This visually indicates which button is currently active.
*
* @param {string} buttonId The ID of the button to activate.
*/
function toggleButton(buttonId) {
   // Select all elements with the class 'button-style' and iterate over them using forEach.
   document.querySelectorAll('.button-style').forEach(button => {
       // Remove the 'active-button' class from each button to reset their styles.
       button.classList.remove('active-button');
   });


   // Find the button with the specified ID and add the 'active-button' class to it.
   // This highlights the button, showing it as currently active.
   document.getElementById(buttonId).classList.add('active-button');
}


  


   </script>
   
</head>
<!-- logo and navigation bar-->




<!-- Language Selection Form with an ID -->
<form id="languageForm" action="" method="post" style="margin-left:3%; margin-top:4%; position:absolute">
<select name="language" onchange="this.form.submit();" style="margin-left:10%;background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 68px; height: 28px; font-size:10px; margin-left:3%">
        <option value="en" <?php echo ($_SESSION['language'] == 'en' ? 'selected' : ''); ?>>English</option>
        <option value="fr" <?php echo ($_SESSION['language'] == 'fr' ? 'selected' : ''); ?>>Français</option>
    </select>
 <!-- Hidden fields to preserve other selections -->
 <input type="hidden" name="departureAirportCode" value="<?= htmlspecialchars($selectedDepartureAirportCode) ?>">
    <input type="hidden" name="destinationAirportCode" value="<?= htmlspecialchars($selectedDestinationAirportCode) ?>">
    <input type="hidden" name="departureDate" value="<?= htmlspecialchars($selectedDepartureDate) ?>">
</form>

<img src="calol.png" width="220" style="margin-top:2%; margin-left:-95%; position:absolute">


<div class="topnav" style="font-family: 'Inter', sans-serif; font-weight:400; font-size:10px; margin-top:-1%; margin-left:7%">
  <a class="active" href="calc.php" style="font-weight: 100;  font-size:10px; margin-top:3%; margin-left:3%;  ">Financial Summary</a> 
  <a href="repo.php" style="font-size:10px; margin-top:3%;  margin-left:3%; font-weight:100; ">Daily Report</a> 
  <a href="route.php" style="font-size:10px; margin-top:3%; font-weight: 100;  margin-left:3%; f color:white">Routes</a>
  <a href="index.php" style="font-size:10px; margin-top:3%; font-weight: 100;  margin-left:1%; font-weight:200;color:yellow">Airport Timetable</a> 


  <!-- Language Selection Form -->



</div>


<body onload="showSection('departures');">



    

<div class="modal-bg">
   <div class="modal-content">
       <span class="close">&times;</span> <!-- Close button -->
       <p id="modal-content-text"></p> 
   </div>
</div>


<div id="main-content">




   <div id="header" style="border:none">
       <!-- <h1 style="margin:center; text-align: center; color:white; margin-top:-3.2%; font-family: 'Inter', sans-serif; font-weight:400">Comfort Airlines Timetable</h1>
   --></div>
       <!-- <h1 style="color:#FFE933">Select Airport</h1>-->



       


       <form action="" method="post" style="margin-top:1.7%; margin-left:-13%">
   <select name="departureAirportCode" style="margin-left:36%; margin:center; font-family: 'Inter', sans-serif; font-weight:400; border-radius:9px; width: 180px; height: 38px; font-size:14px; color:white; background-color:rgba(0,0,0,0);">
       <option value="" disabled selected style="color:grey;"><?= translate('Departure Airport', $_SESSION['language']); ?></option>
       <?php foreach ($airports as $airport): ?>
           <option class="flight-number" value="<?= $airport['AirportCode']; ?>" <?= ($selectedDepartureAirportCode == $airport['AirportCode']) ? 'selected' : ''; ?>>
               <?= $airport['AirportCode']; ?>
           </option>
       <?php endforeach; ?>
   </select>
  
   <select name="destinationAirportCode" style="margin:center; font-family: 'Inter', sans-serif; font-weight:400; border-radius:9px; width: 180px; height: 38px; font-size:14px; color:white; background-color:rgba(0,0,0,0);">
       <option value="" disabled selected style="color:grey;"><?= translate('Destination Airport', $_SESSION['language']); ?></option>
       <?php foreach ($airports as $airport): ?>
           <option class="flight-number" value="<?= $airport['AirportCode']; ?>" <?= ($selectedDestinationAirportCode == $airport['AirportCode']) ? 'selected' : ''; ?>>
               <?= $airport['AirportCode']; ?>
           </option>
       <?php endforeach; ?>
   </select>
   <input type="date" name="departureDate" value="<?= isset($_POST['departureDate']) ? $_POST['departureDate'] : ''; ?>" min="2024-08-01" max="2024-08-14" required style="background-color:rgba(0,0,0,0); color:white; background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 100px; height: 38px; font-size:12px; text-align:center">
  
   <input type="submit" value="<?= translate('Show Flights', $_SESSION['language']); ?>" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 120px; height: 38px; font-size:14px;">
</form>






       <div id="main" style="border: none; margin-top:-4.4% ">


       <!-- Buttons for toggling between arrivals and departures -->
       <button id="arrivalsButton" onclick="showSection('arrivals'); toggleButton('arrivalsButton')" class="button-style" style="font-family: 'Quantico', sans-serif; "><i class="fas fa-plane-arrival" style="color:black; "></i> <?= translate('Arrivals', $_SESSION['language']); ?></button>
       <button id="departuresButton" onclick="showSection('departures'); toggleButton('departuresButton')" class="button-style" style="margin-left:-1%; font-family: 'Quantico', sans-serif;" > <i class="fas fa-plane-departure" style="color:black"></i> <?= translate('Departures', $_SESSION['language']); ?> </button>


       <div style="float:right; margin-right:1%; margin-top:4.6%;">
   <button type="button" onclick="swapAirports()" style="background-color:transparent; border:none; cursor:pointer; color:white;">
       <i class="fas fa-exchange-alt"></i> <?= translate('Switch Airports', $_SESSION['language']); ?>
   </button>
</div> </h1>
       <!-- Arrivals Section -->
   <div style="background-color: #2b2b2b; ">






       <!-- Arrivals section, initially hidden, displays flight arrival information in a table format. -->
<div id="arrivals" class="flights" style="display:none; margin-top:0%; white-space:nowrap">
   <!-- Section header with a styled icon indicating arrivals -->
   <h1 style="background-color: #FFE933">
       <i class="fas fa-plane-arrival fa-border" style="padding:6px; color:black; margin-left:2%"></i>
       <span style="color:black; font-family: 'Inter', sans-serif;"> <?= translate('Arrivals', $_SESSION['language']); ?>
       <span style="margin-left:67%; font-size:90%"><?php echo htmlspecialchars($departureAirportName) . " → " . htmlspecialchars($destinationAirportName); ?></span>

       </h1>
   <!-- Table for displaying arrivals data with transparent borders for a seamless design -->
   <table style="border: 2px solid rgba(0,0,0,0); border-color:rgba(0,0,0,0)">
   
       <!-- Table header row defining the data columns for flight information -->
       <tr style="border-color: rgba(255, 255, 255, 0.5); white-space:nowrap; font-family: 'Inter', sans-serif;font-weight:400"> 
       

           <!-- Headers for the departures table, including a status column not present in arrivals -->
           <th style="border-color: transparent; white-space:nowrap"><?= translate('Flight Number', $_SESSION['language']); ?></th>
           <th style="border-color: transparent; white-space:nowrap"><?= translate('Flight Date', $_SESSION['language']); ?></th>
               <th style="border-color: transparent; white-space:nowrap"><?= translate('No. Of Passengers', $_SESSION['language']); ?></th>
               <th style="border-color: transparent; white-space:nowrap"><?= translate('Arrival Airport', $_SESSION['language']); ?></th>
               <th style="border-color: transparent; white-space:nowrap"><?= translate('Scheduled Arrival Time', $_SESSION['language']); ?></th>
               <th style="border-color: transparent; white-space:nowrap"><?= translate('Actual Arrival Time', $_SESSION['language']); ?></th>
               <th style="border-color: transparent; white-space:nowrap"><?= translate('Tail Number', $_SESSION['language']); ?></th>
       </tr>
       <!-- PHP code iterates over the arrivals array, outputting each arrival's details in table rows -->     
       <?php foreach ($arrivals as $arrival) : ?>
        
       <tr>  
           <!-- Data cells containing the formatted flight information for each arrival -->
           <td><?= formatField($arrival['FlightNumber'], 5); ?></td>
           <td><?= formatFieldtwo($arrival['ArrivalDate'], 5, 5); ?></td>
           <td><?= formatField($arrival['NumberOfPassengers'], 3); ?></td>
           <td><?= formatField($arrival['DestinationAirport'], 4); ?></td>
           <td><?= formatTimeField($arrival['ScheduledArrivalTime'], 5); ?></td>
           <td><?= formatTimeField($arrival['ActualArrivalTime'], 5); ?></td>
           <td><?= formatField($arrival['TailNumber'], 6); ?></td>
       </tr>
       <?php endforeach; ?>
   </table>
</div>


<!-- The departures section, similar to arrivals, but for flights leaving the airport -->
<div style="background-color: #2b2b2b">

   <div id="departures" class="flights" style="display:none; margin-top:0% ">
       <!-- Departures section header with a date display and styled departure icon -->
       <h1 style="background-color: #FFE933">
           <i class="fas fa-plane-departure fa-border" style="padding:6px; color:black; margin-left:2%; "></i>
           <span style="color:black; font-family: 'Inter', sans-serif;"><?= translate('Departures', $_SESSION['language']); ?> 
           <span style="margin-left:67%; font-size:90%"><?php echo htmlspecialchars($departureAirportName) . " → " . htmlspecialchars($destinationAirportName); ?></span>

       </h1>


       <div id="flight-info">
    <?php if (!$flightsFound): ?>
        <i class="fas fa-plane fa-2x" style="margin-top:3%; color:#FFE933;margin-left:50%; width:90px"></i> 
        <p style="display: flex;justify-content: center;align-items: center;"><?= htmlspecialchars($noFlightsMessage); ?></p>
    <?php else: ?>



       <!-- Table for departures, structured similarly to the arrivals table -->
       <table style="border: 2px solid rgba(0,0,0,0); border-color:rgba(0,0,0,0); white-space:nowrap">
           <tr style="font-family: 'Inter', sans-serif;font-weight:300;white-space:nowrap">
               <!-- Headers for the departures table, including a status column not present in arrivals -->
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Flight Number', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('No. Of Passengers', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Departure Airport', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Scheduled Depart Time', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Actual Depart Time', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Tail Number', $_SESSION['language']); ?></th>
               <th style="border-color: transparent;white-space:nowrap"><?= translate('Status', $_SESSION['language']); ?></th>

           </tr>
           <!-- Iteration over departures array to display each departure's detailed information -->
           <?php foreach ($departures as $departure) : ?>
           <tr>
           <td>
              <a href="#" class="flight-number" style="color: inherit; text-decoration: none;"
              data-flight-number="<?= htmlspecialchars($departure['FlightNumber']); ?>"
              data-passenger-density="<?= htmlspecialchars($flightDetails[$departure['FlightNumber']]['passengerDensity']); ?>"

              data-flight-distance="<?= htmlspecialchars($flightDetails[$departure['FlightNumber']]['flightDistance']); ?>"
              data-travel-time="<?= htmlspecialchars($departure['FlightDuration']); ?>"
              data-ticket-price="<?= htmlspecialchars($flightDetails[$departure['FlightNumber']]['ticketPrice']); ?>"
              data-tail-number="<?= htmlspecialchars($departure['TailNumber']); ?>"
              data-departure-airport="<?= htmlspecialchars($departure['DepartureAirport']); ?>"
              data-destination-airport="<?= htmlspecialchars($departure['DestinationAirport']); ?>"> <!-- And this line -->
             
  <?= formatField($departure['FlightNumber'], 5); ?>
</a>
           </td>
               <td><?= formatField($departure['NumberOfPassengers'], 3, true); ?></td>
               <td><?= formatField($departure['DepartureAirport'], 4, true); ?></td>
               <td><?= formatTimeField($departure['ScheduledDepartureTime'], 5, true); ?></td>
               <td><?= formatTimeField($departure['ActualDepartureTime'], 5, true); ?></td>
               <td><?= formatField($departure['TailNumber'], 6, true); ?></td>
         


      
       <!-- Status Calculation and Display -->
       <?php
       if ($departure['ActualArrivalTime'] === '00:00:00' || $departure['ActualDepartureTime'] === '00:00:00') {
           $status = 'Cancelled';
           $style = 'background-color: red; color: white;   font-family: "Roboto", sans-serif; vertical-align: middle;font-weight:700            ';
       } elseif ($departure['ActualDepartureTime'] < $departure['ScheduledDepartureTime']) {
           $status = 'Early';
           $style = 'background-color: blue; color: white;font-family: "Roboto", sans-serif;vertical-align: middle;font-weight:700
           ';
       } elseif ($departure['ActualDepartureTime'] == $departure['ScheduledDepartureTime']) {
           $status = 'On-Time';
           $style = 'background-color: green; color: white;   font-family: "Roboto", sans-serif;vertical-align: middle;font-weight:700
           ';
       } else {
           $status = 'Delayed';
           $style = 'background-color: yellow; color: black;  font-family: "Roboto", sans-serif;vertical-align: middle;font-weight:700
           '; // Using black text for better readability on yellow
       }
      
       ?>
<td style="<?= htmlspecialchars($style); ?>"><?= htmlspecialchars($status); ?></td>
                   </tr>
               <?php endforeach; ?>
           </table>
       </div>
   </div>
</div>
<div style="color:white">










</div>






</div>

<?php endif; ?>
</div>

   <div id="footer" style="border:none; font-family: 'Roboto', sans-serif;">
       <p>&copy; <?php echo date("Y"); ?> Comfort Airlines, Inc.</p>
   </div>
 


</body>
</html>



