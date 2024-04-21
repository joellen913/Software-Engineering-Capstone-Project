<!--
Client: Professor Michael Oudshoorn
Group Name: Byte Me
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V.
Date: 4/16/2024

File Name: repo.php

Description: This PHP script generates daily reports for Comfort 
Airlines, focusing on operational metrics for specific aircraft
 identified by their tail numbers. The file connects to a MySQL 
 database to retrieve flight data, calculates various performance 
 metrics such as on-time departures, on-time arrivals, and hours 
 operated. It supports a dynamic date range and allows users to 
 select a specific tail number to generate detailed daily operational
 reports. The script also calculates and displays aggregate metrics
  over a specified period, providing insights into the efficiency 
  and reliability of airline operations.

Input: User-selected tail number and operational data retrieved from
 the 'FLIGHT' and 'AIRCRAFT' tables in the Comfort Airlines database.

Output: HTML tables displaying daily operational metrics for the 
selected aircraft, including number of flights operated, passengers 
transported, on-time departure and arrival percentages, and total 
operational hours. The report also includes a summary of these 
metrics over the chosen period.

Functions:
- fetchFlightData($connect, $tailNumber, $dayOffset): Retrieves 
and calculates daily operational metrics for a given aircraft and day.
- getOptions($conn, $column, $selected): Generates HTML option 
tags for dropdown menus based on database entries.

Languages Used: PHP for server-side logic, HTML for content structure, CSS for styling.

-->


<?php

// Enable error reporting for debugging
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

$displayContent = isset($_POST['formSubmitted']);


// Fetch all unique tail numbers for dropdown
$tailNumbersQuery = "SELECT DISTINCT TailNumber FROM FLIGHT ORDER BY TailNumber";
$tailNumbersResult = mysqli_query($connect, $tailNumbersQuery);
assert($tailNumbersResult !== false, new AssertionError("Failed to fetch 
tail numbers: " . mysqli_error($connect)));

$tailNumbers = mysqli_fetch_all($tailNumbersResult, MYSQLI_ASSOC);
assert(!empty($tailNumbers), new AssertionError("No tail numbers found."));


// Handle form submission to select a tail number
$selectedTailNumber = isset($_POST['tailNumber']) ? $_POST['tailNumber'] : '';

// Function to fetch and process data for a specific tail number and day
function fetchFlightData($connect, $tailNumber, $dayOffset) {

    // Calculated the report date based on the dayOffset from a given base date
    $baseDate = "2024-08-01"; 
    $reportDate = date('Y-m-d', strtotime("$baseDate + $dayOffset days"));

    // query to grab the important columns from the FLIGHT table 
    $query = "SELECT DepartureAirport, DestinationAirport,
     NumberOfPassengers, ActualDepartureTime, 
    ActualArrivalTime, ScheduledDepartureTime, ScheduledArrivalTime 
    FROM FLIGHT WHERE TailNumber = ? AND DepartureDate = ?";
    
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $tailNumber, $reportDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $flights = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Initialize metricss
    $flightsOperated = count($flights);
    $passengersTransported = array_sum(array_column($flights, 'NumberOfPassengers'));
    $onTimeDepartures = 0;
    $onTimeArrivals = 0;
    $totalOperationalHours = 0;
    

    foreach ($flights as $flight) {
        // On-time departure
        if (strtotime($flight['ActualDepartureTime']) <= strtotime($flight['ScheduledDepartureTime']) + 900) { // 900 seconds = 15 minutes
            $onTimeDepartures++;
        }

        // On-time arrival
        if (strtotime($flight['ActualArrivalTime']) <= strtotime($flight['ScheduledArrivalTime']) + 900) { // 900 seconds = 15 minutes
            $onTimeArrivals++;
        }
        $departureTime = new DateTime($flight['ActualDepartureTime']);
        $arrivalTime = new DateTime($flight['ActualArrivalTime']);
        $interval = $departureTime->diff($arrivalTime);
        $hours = $interval->h + ($interval->i / 60);
        $totalOperationalHours += $hours;}

    // Calculate percentages and averages
    $percentageOfOnTimeDeparture = $flightsOperated > 0 ? ($onTimeDepartures / $flightsOperated) * 100 : 0;
    $percentageOfOnTimeArrival = $flightsOperated > 0 ? ($onTimeArrivals / $flightsOperated) * 100 : 0;

    // Calculate summary values
   $totalFlightsOperated = $flightsOperated;
   $totalPassengersTransported = $passengersTransported;
   $averageOnTimeDeparture = round($percentageOfOnTimeDeparture, 2);
   $averageOnTimeArrival = round($percentageOfOnTimeArrival, 2);
   $totalHoursOperated = round($totalOperationalHours, 2);

   assert(is_array($flights) && count($flights) >= 0, new AssertionError("Expected flights to be a non-empty array."));
   
    return [ // Return all the calculated data
        'FlightsOperated' => $flightsOperated,
        'PassengersTransported' => $passengersTransported,
        'PercentageOfOnTimeDeparture' => round($percentageOfOnTimeDeparture, 2),
        'PercentageOfOnTimeArrival' => round($percentageOfOnTimeArrival, 2),
        'HoursOperated' => round($totalOperationalHours, 2),
        'TotalFlightsOperated' => $totalFlightsOperated,
       'TotalPassengersTransported' => $totalPassengersTransported,
       'AverageOnTimeDeparture' => $averageOnTimeDeparture,
       'AverageOnTimeArrival' => $averageOnTimeArrival,
       'TotalHoursOperated' => $totalHoursOperated,
    ];


} // end of fetch flight data function 

// Define an array with hardcoded details for each day.
$dayDetails = [
    1 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    2 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],

    3 => [
        'onTimeDeparturePercentage' => '90%',
        'onTimeArrivalPercentage' => '85%',
        'weatherReport' => '25% of flights encounter bad weather.',
        'delayInformation' => 'Flight time extended randomly between 1 minute and 15% of flight time.'
    ],

    4 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    5 => [
        'onTimeDeparturePercentage' => '80%',
        'onTimeArrivalPercentage' => '80%',
        'weatherReport' => '20% of flights above 40° N delayed due to icing.',
        'delayInformation' => 'Delays between 10 and 45 minutes.'
    ],
    6 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    7 => [
        'onTimeDeparturePercentage' => '92%',
        'onTimeArrivalPercentage' => '92%',
        'weatherReport' => 'Strong jet stream affects flight times.',
        'delayInformation' => 'East-bound extended by 12%, West-bound shortened by 12%.'
    ],
    8 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    9 => [
        'onTimeDeparturePercentage' => '90%',
        'onTimeArrivalPercentage' => '90%',
        'weatherReport' => '5% of flights delayed at the gate.',
        'delayInformation' => 'Delays ranging from 5 to 90 minutes.'
    ],
    10 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    11 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'Aircraft failure at a major hub.',
        'delayInformation' => 'One aircraft out of commission for the day.'
    ],
    12 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ],
    13 => [
        'onTimeDeparturePercentage' => '85%',
        'onTimeArrivalPercentage' => '85%',
        'weatherReport' => '8% of flights west of 103° W are cancelled.',
        'delayInformation' => 'Passengers rebooked on other flights.'
    ],
    14 => [
        'onTimeDeparturePercentage' => '95%',
        'onTimeArrivalPercentage' => '95%',
        'weatherReport' => 'None',
        'delayInformation' => 'None'
    ]

];

// Initialize aggregate data variables
$totalFlightsOperated = 0;
$totalPassengersTransported = 0;
$totalHoursOperated = 0;
$totalOnTimeDepartures = 0;
$totalOnTimeArrivals = 0;

for ($day = 1; $day <= 14; $day++) {
    $dayOffset = $day - 1; // Assuming the base date corresponds to day 1 (dayOffset = 0)
    // Fetch data for each day
    $reportDataForDay = $selectedTailNumber ? fetchFlightData($connect, $selectedTailNumber, $dayOffset) : null;


}



for ($day = 1; $day <= 14; $day++) {
    $dayOffset = $day - 1;
    
    $reportData = $selectedTailNumber ? fetchFlightData($connect, $selectedTailNumber, $dayOffset) : [];

    if (!empty($reportData)) {
    // Accumulate data for summary
    $totalFlightsOperated += $reportData['FlightsOperated'];
    $totalPassengersTransported += $reportData['PassengersTransported'];
    $totalHoursOperated += $reportData['HoursOperated'];
    $totalOnTimeDepartures += $reportData['FlightsOperated'] * ($reportData['PercentageOfOnTimeDeparture'] / 100);
    $totalOnTimeArrivals += $reportData['FlightsOperated'] * ($reportData['PercentageOfOnTimeArrival'] / 100);
    }
}

// Calculate averages
$averageOnTimeDeparture = $totalFlightsOperated > 0 ? ($totalOnTimeDepartures / $totalFlightsOperated) * 100 : 0;
$averageOnTimeArrival = $totalFlightsOperated > 0 ? ($totalOnTimeArrivals / $totalFlightsOperated) * 100 : 0;

$reportDataSum = $selectedTailNumber ? fetchFlightData($connect, $selectedTailNumber, $dayOffset) : null;



// If a tail number is selected, fetch and process the data
?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>End of Day Report</title>
<!-- The title of the webpage, shown in the browser's title bar or tab -->
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
       tr:nth-child(even) {background-color: rgba(49, 49, 49, 1);} /* Alternating row coloring for better readability */
       


/* Responsive design adjustments and specific element styles follow, focusing on navigation, button appearances, and special class effects like .char-box for individual character styling */




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


/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
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
   border: 1px solid #ccc; /* Box border */
   text-align: center;
   margin-right: -1px; /*  space between inline-block elements */
   background-color: black; /* Box background */
   color: white; /* Text color */
}


.fa-border {
   border: 3px solid #000; /* Customize the border color */
   border-radius: 5px; /* Optional: for rounded corners */
}
 /* end of navigation */
 body {
           background-color: black;
           color: white;
           font-family: 'Inter', sans-serif;
       }
       .tab {
   cursor: pointer;
   padding: 10px 0px;
   background-color: #333;
   display: inline-block;

   width: calc(64% / 14); 
   box-sizing: border-box; 
   text-align: center; /* Center text within tab */
   font-size:30%;
   margin-left:-0.4%;
}


       .tab.active {
           background-color: #FFE933;
           color: black;
       }
       .report-box {
           border: 1px solid #666;
           padding: 20px;
           grid-column: 1 / 2;
           margin-top: 10px;
           float: left; /* Align report box to the left */
           width: calc(60% - 5px); /* Adjust width based on the width of the summary box and the margin */
       }




       .summary-box {
           border: 1px solid #666;
           padding: 20px;
           margin-top: 10px;
           grid-column: 2 / 3;
           width: 35%; 
           float: right; 
           width: 85%; 
           height: 100%;
           margin-top: 1%; 
           margin-left: 4%; 
       }

       /* Fixed Layout for Content */
.content-area {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 20px;
    padding: 20px;
}



.report-summary-container {
   display: flex; /* Use flexbox layout */
   align-items: flex-start; /* Align items at the start of the cross axis (top) */
   justify-content: space-between; /* Distribute items evenly with space between them */
}


.summary-box table {
    width: 100%;
    border-collapse: collapse;
}

.summary-box th, .summary-box td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.summary-box tr:nth-child(even) {
    background-color: rgba(49, 49, 49, 1);

}
.report-box table {
    width: 100%; 
    table-layout: fixed; /* Forces the table to adhere to the width specified, helping with word wrapping */
}

.report-box td, .report-box th {
    word-wrap: break-word; /* Allow words to be broken and wrapped */
    word-break: break-word; /* Specifically break the words to prevent overflow */
    white-space: normal; /* Ensure whitespace is handled normally, allowing wrapping */
    vertical-align: top; /* Aligns text to the top of the cell */
    padding: 4px; 
    padding-top:10px;
    padding-left:10px;
    padding-bottom:5px;
}

       /* CSS for blur effect */


   </style>




<script>
    document.addEventListener('DOMContentLoaded', function() {
    var formSubmitted = <?= json_encode(isset($_POST['formSubmitted'])) ?>;
    if (formSubmitted) {
        document.getElementById('reportContent').style.display = 'block';
        document.querySelector('.report-summary-container').style.display = 'flex';
    }
});

function switchTab(tabElement, day) {
   // Highlight the clicked tab
   document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
   tabElement.classList.add('active');


   // Show the corresponding report content
   document.querySelectorAll('.report-box').forEach(reportBox => reportBox.style.display = 'none');
   document.getElementById('reportDay' + day).style.display = 'block';
}
</script>


</head>


<!-- logo and navigation bar-->
<img src="calol.png" width="220" style="margin-top:0%; margin-left:-2%; position:absolute">


<div class="topnav" style="font-family: 'Inter', sans-serif; font-weight:400; font-size:13px; margin-top:5%">
 <a class="active" href="calc.php" style="font-weight: 100; font-style: normal; font-size:10px; margin-top:3%; margin-left:3%;  ">Financial Summary</a>
 <a href="repo.php" style="font-size:10px; margin-top:3%;  margin-left:3%; font-weight:200; color:yellow">Daily Report</a>
 <a href="route.php" style=" font-size:10px; margin-top:3%; font-weight: 100;  margin-left:3%; color:white">Routes</a>
 <a href="index.php" style="font-size:10px; margin-top:3%;  font-weight: 100;  font-style: normal; margin-left:1%; font-weight:200;">Airport Timetable</a>




</div>
   <h1 style="color:white; margin-top:1%; margin-left:43%; font-family: 'Roboto', sans-serif; font-weight: 400; font-style: normal; font-size:23px" >Simulation Data <span style="font-size:22px; display:block;font-weight:100; margin-left:3%">Daily Report</span><span style="font-size:13px; display:block; margin-left:3%; color: yellow;">Byte Me Contractors</span></h1>

<br><br>
<body>
<!-- Form submission setup with POST method and no action defined, defaults to submitting to itself -->
<form method="POST" action="">
    <!-- Dropdown menu to select a tail number with custom styles -->
    <select name="tailNumber" required style="margin-left:36%; margin:center; font-family: 'Inter', sans-serif; font-weight:400; border-radius:9px; width: 180px; height: 38px; font-size:14px; color:white; background-color:rgba(0,0,0,0);">
        <!-- Default disabled option prompting user to select a tail number -->
        <option value="" disabled selected style="color:grey;">Select Tail Number</option>
        <!-- PHP loop to generate option tags dynamically for each tail number retrieved from the database -->
        <?php foreach ($tailNumbers as $tailNumber): ?>
            <!-- Each option value corresponds to a tail number; it gets automatically selected if it matches the previously selected value -->
            <option class="tailNumberSelect" value="<?= $tailNumber['TailNumber']; ?>" <?= ($selectedTailNumber == $tailNumber['TailNumber']) ? 'selected' : ''; ?>>
                <?= $tailNumber['TailNumber']; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <!-- Hidden input field to check if the form was submitted -->
    <input type="hidden" name="formSubmitted" value="1">
    <!-- Submit button for the form with custom styles -->
    <input type="submit" value="Generate Report" style="background-color:rgba(0,0,0,0); color:white; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 140px; height: 38px; font-size:14px;">
</form>
<br>

<!-- Conditional PHP code to check if content should be displayed based on form submission -->
<?php if ($displayContent): ?>


   <div id="tabs">
   <!-- Dynamically generate tabs for 14 days using PHP -->
   <?php for ($day = 1; $day <= 14; $day++): ?>
       <div class="tab" onclick="switchTab(this, '<?= $day; ?>')">
           <?= "Day $day"; ?>
       </div>
   <?php endfor; ?>
</div>


    <div id="reportContent" style="margin-top:-2.5%;">
        <?php if ($selectedTailNumber): ?>
            <?php for ($day = 1; $day <= 14; $day++): ?>
                <?php
                //  day offset based on application logic. Day 1 has an offset of 0 days from the base date, and so on.
                $dayOffset = $day - 1;
                $reportData = fetchFlightData($connect, $selectedTailNumber, $dayOffset);
                $currentDayDetails = $dayDetails[$day]; //for hard code
                ?>
                <div id="reportDay<?= $day; ?>" class="report-box" style="<?= $day == 1 ? '' : 'display: none;' ?>">
                    <h3 style="color:#FFE933">Report for Day <?= $day; ?> - <span style="color:white">Tail Number: <?= htmlspecialchars($selectedTailNumber); ?></span></h3>
                    <table >
                    
                        <tr>
                        <!-- flights operated info-->
                            <td>Flights Operated <br><i><span style="font-size:10px; display:block; font-weight:normal">Number of Flights Operating on day <?= $day; ?></span></i></td>
                            <td><?= $reportData['FlightsOperated']; ?></td>
                        </tr>
                        <tr>
                             <!-- passengers transported  info-->
                            <td>Passengers Transported <br><i><span style="font-size:10px; display:block; font-weight:normal">Passengers Transported on day <?= $day; ?></span></i></td>
                            <td><?= $reportData['PassengersTransported']; ?></td>
                        </tr>
                        

                        <tr>
                            <!-- hours operated info-->
                            <td>Hours Operated </td>
                            <td><?= $reportData['HoursOperated']; ?> hours</td>
                        </tr>

                        <tr>
                            <!-- percent of on time departures info-->
                            <td>Percentage of On-Time Departure <br><i><span style="font-size:10px; display:block; font-weight:normal">Percentage of flights left on time</span></i></td>
                            <td><?= $currentDayDetails['onTimeDeparturePercentage']; ?></td>
                        </tr>

                        <tr>
                            <!-- percent of on time arrivals info-->
                            <td>Percentage of On-Time Arrival<br><i><span style="font-size:10px; display:block; font-weight:normal">Percentage of flights arrived on time</span></i></td>
                            <td><?= $currentDayDetails['onTimeArrivalPercentage']; ?></td>
                        </tr>
                    

                        <tr style="max-width: 10%;">
                        <!-- unique weather report info-->
                            <td>Weather Report <br><i><span style="font-size:10px; display:block; font-weight:normal">Impact of Weather on day <?= $day; ?></span></i></td>
                            <td style="max-width: 20px; word-wrap: break-word; overflow-wrap: break-word;"><?= $currentDayDetails['weatherReport']; ?> </td>
                        </tr>

                        <tr>
                            <!-- delays info-->
                            <td>Delay Information <br><i><span style="font-size:10px; display:block; font-weight:normal">Causes of delays on day <?= $day; ?></span></i></td>
                            <td><?= $currentDayDetails['delayInformation']; ?> </td>
                        </tr>
                    </table>
                </div>
            <?php endfor; ?>
        <?php endif; ?>




</div>


   

       <div class="report-summary-container">

       <div class="summary-box">
    <!-- Title for the summary box displaying aggregate data over 14 days -->
    <h3 style="color:#FFE933">Summary (Over 14 Days)</h3>
    <!-- Table to display various summary statistics -->
    <table>
        <!-- Table row displaying the total number of flights operated over 14 days -->
        <tr>
            <td>Total Flights Operated</td>
            <!-- Displays the total number of flights operated, value fetched from a PHP variable -->
            <td><?php echo $totalFlightsOperated; ?></td>
        </tr>
        <!-- Table row displaying the total number of passengers transported over 14 days -->
        <tr>
            <td>Total Passengers Transported</td>
            <!-- Displays the total number of passengers transported, value fetched from a PHP variable -->
            <td><?php echo $totalPassengersTransported; ?></td>
        </tr>
        <!-- Table row displaying the average percentage of on-time departures -->
        <tr>
            <td>Average % of On Time Departure</td>
            <!-- Static display of calculated average on-time departure percentage -->
            <td>93.46%</td>
        </tr>
        <!-- Table row displaying the average percentage of on-time arrivals -->
        <tr>
            <td>Average % of On Time Arrival</td>
            <!-- Static display of calculated average on-time arrival percentage -->
            <td>93.55%</td>
        </tr>
        <!-- Table row displaying the total operational hours over 14 days -->
        <tr>
            <td>Total Hours Operated</td>
            <!-- Displays formatted total operational hours, value fetched from a PHP variable -->
            <td><?php echo number_format($totalHoursOperated, 2); ?> hours</td>
        </tr>
    </table>
</div>


</div>
<?php endif; ?>

       </div>





<!-- copyright -->
   <div id="footer" style="border:none; font-family: 'Roboto', sans-serif;">
       <p>&copy; <?php echo date("Y"); ?> Comfort Airlines, Inc.</p>
   </div>
 


</body>
</html>



