<!--
Client: Professor Michael Oudshoorn 
Group Name: Byte Me               
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V. 
Date: 3/22/2024


File Name: calc.php


Description: This file is designed to calculate and display operational costs,
revenue, and occupancy information for Comfort Airlines over a specific period.
It connects to a MySQL database to fetch flight information, calculates various
costs such as fuel, leasing, and terminal fees, and aggregates these to provide
a financial summary. The output includes detailed tables showcasing operational
costs, revenue generated per flight, actual occupancy rates, and the total
operational cost over a two-week period. Additionally, it computes maintenance
costs and the impact of downtime on revenue, providing a comprehensive profit
and loss report.


Input: Flight information from the database, including tail numbers, flight dates,
and distances.


Output: HTML tables displaying operational costs per day, total operational costs
over two weeks, revenue generated, actual occupancy, and overall financial summaries
including maintenance and downtime impacts.
Functions: The file includes PHP code for database connection, data fetching and
processing, cost calculations based on flight data, and dynamic HTML content rendering
for the financial report.


Languages Used: PHP for backend processing and calculations, HTML for content structure,
and CSS for styling.
-->


<?php


// Enable PHP error reporting for development purposes. These lines make errors visible to help in debugging.
// Enable PHP error reporting for development purposes.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Execute an external program and store its output.
$output = shell_exec('./flighttime');
echo $output;


// Database connection settings
$host = 'db';
$dbname = 'comfort_airlines_byteme';
$user = 'byteme';
$pass = 'letmein';


// Establishing a connection to the database
$mysqli = new mysqli($host, $user, $pass, $dbname);
assert($mysqli->connect_errno == 0, new AssertionError("Connection failed: " . $mysqli->connect_error));


// Check connection
if ($mysqli->connect_error) {
   die("Connection failed: " . $mysqli->connect_error);
}

// Initialize $flights to prevent undefined variable errors
$flights = [];

// SQL query string
$sql = "SELECT a.TailNumber, f.NumberOfPassengers, f.DepartureDate, f.DepartureAirport, f.DestinationAirport, f.Distance, f.FlightNumber FROM
 AIRCRAFT a JOIN FLIGHT f ON a.TailNumber = f.TailNumber WHERE
  f.DepartureDate BETWEEN '2024-08-01' AND '2024-08-15' ORDER BY f.DepartureDate ASC";

// Execute query and fetch results
$result = $mysqli->query($sql);
assert($result !== false, new AssertionError("Failed to fetch data: " . $mysqli->error));


// Check for results
if ($result && $result->num_rows > 0) {
   // Fetch all resulting rows as an associative array
   $flights = $result->fetch_all(MYSQLI_ASSOC);
} else {
   echo "No flights found.";
}


// Define leasing rates based on tail number prefixes
$dates = array_column($flights, 'DepartureDate');


sort($dates); // Sort dates to find the earliest
if (!empty($dates)) {
    $earliestDate = new DateTime($dates[0]);
} else {
    // Handle the case where there are no dates (e.g., set a default or handle the error appropriately)
    echo "No valid dates found.";
    // Setting a default date or handling this scenario appropriately
}


// Defining leasing rates based on aircraft type
$leasingFeesPerFlight = [];
$leasingRates = [
    'B7376' => 8049.41,
    'B7378' => 8870.78,
    'AB1' => 6308.11,
    'AB3' => 7490.88,
    'B7474' => 9856.42,
];

// Gallons per mile for each aircraft type based on TailNumber prefix
$gallonsPerMile = [
    'B7376' => 2.07711521,
    'B7378' => 1.95235582,
    'AB1' => 1.70554916,
    'AB3' => 1.57378012,
    'B7474' => 25.980827
];


$fuelFeesPerFlight = []; //fuel fees per flight array
$pricePerLiter = 2.10; // Cost per liter at CDG
$pricePerGallon = 6.19; // Default cost per gallon

// Initialize an array to hold terminal fees per day
$terminalFeesPerFlight = [];
// Terminal and fuel fee adjustments for flights involving CDG
$cdgFeePerOperation = 2242; // Euro 2100 per operation at CDG to USD
$defaultFeePerOperation = 2000; // Default fee per operation

$ticketSalesPerFlight = []; //ticket sales per flight array


// Process each flight to calculate fees and revenues
foreach ($flights as $flight) {
   $prefix = explode('-', $flight['TailNumber'])[0]; // Get aircraft type prefix from TailNumber
   $leasingRate = $leasingRates[$prefix] ?? 0; // Get leasing rate based on prefix or 0 if not found
   $leasingFeesPerFlight[isset($flight['FlightNumber'])] = 0;
   $DepartureDate = new DateTime($flight['DepartureDate']);
   $dayNumber = ($earliestDate->diff($DepartureDate)->days)+1; // Calculate day number relative to start date
   $terminalFeesPerFlight[isset($flight['FlightNumber'])] = 0;
   $totalFeeForFlight = 0;
   $DepatureAirport = (string)$flight['DepartureAirport'];
   $DestinationAirport = (string)$flight['DestinationAirport'];
   $distance = $flight['Distance'];
   $fuelFeesPerFlight[isset($flight['FlightNumber'])] = 0;
   $passengers = $flight['NumberOfPassengers'];
   $ticketSalesPerFlight[isset($flight['FlightNumber'])] = 0;
   $totalOperationalCost = 0;

   // check if either the departure or destination airport is CDG 
   if ((isset($flight['DepartureAirport']) && $flight['DepartureAirport'] == 'CDG') || (isset($flight['DestinationAirport']) && $flight['DestinationAirport'] == 'CDG')) {
       $pricePerUnit = $pricePerLiter; // If price is CDG, then set price per unit to price per liter due to metrics
   } else {
       $pricePerUnit = $pricePerGallon; // other than that, all flights are American, so keep price per gallon for measurements
   }
   
   // Calculate fuel cost based on distance, gallons per mile, and price per unit
   $fuelCost = $distance * ($gallonsPerMile[$prefix] ?? 0) * $pricePerUnit; // Calculate fuel cost
   
   // Store fuel cost in an array with key as wehtehr flight number is set 
   $fuelFeesPerFlight[isset($flight['FlightNumber'])] = $fuelCost;
   
   //check if either destination or departure airport is CDG
   if ($DepatureAirport == "CDG"|| $DestinationAirport == "CDG") {
       $feePerOperation = $cdgFeePerOperation; // if CDG, set the fee per operation to CDG's fees
   } else {
       $feePerOperation = $defaultFeePerOperation;
   }
   
   //equation for total fee for each individual flight
   $totalFeeForFlight = $feePerOperation * 2; // Takeoff and landing

   $terminalFeesPerFlight[isset($flight['FlightNumber'])] = $totalFeeForFlight;

   //ensures that leasing rate is above 0
   if ($leasingRate > 0) {
        $leasingFeesPerFlight[isset($flight['FlightNumber'])] = $leasingRate;
   }

   // Total operational cost for the flight
   $totalOperationalCost = $fuelFeesPerFlight[isset($flight['FlightNumber'])] + $leasingFeesPerFlight[isset($flight['FlightNumber'])] + $terminalFeesPerFlight[isset($flight['FlightNumber'])];

   // If no passengers, avoid division by zero
   if ($passengers > 0) {
       $ticketPricePerPassenger = ($totalOperationalCost / $passengers)*1.02;

       // Calculate teh total ticket sales based on ticket price per passenger and number of passengers
       $totalTicketSales = $ticketPricePerPassenger * $passengers;
   } else {
       $totalTicketSales = 0;
   }

   // Store the total ticket sales in an array with key as whether flight number is set
   $ticketSalesPerFlight[isset($flight['FlightNumber'])] = $totalTicketSales;

}

// Output or process $ticketSalesPerFlight as needed



// Initialize an array to hold the total operational costs per day
$totalOperationalCostsPerDay = [];
$fuelFeesPerDay = [];
$leasingFeesPerDay = [];
$terminalFeesPerDay = [];
$ticketSalesPerDay = [];

// Initialize array increment
$arrayIncrement = 0;

// Loop through each day of the two-week period
for ($day = 1; $day <= 14; $day++) {
    // Initialize the total operational cost for the day
    $operationalCostPerDay = 0;
    $fuelCostPerDay = 0;
    $leasingCostPerDay = 0;
    $terminalCostPerDay = 0;
    $ticketRevenuePerDay = 0;

    // Initate daily fee arrays
    $terminalFeesPerDay[$arrayIncrement] = 0;
    $leasingFeesPerDay[$arrayIncrement] = 0;
    $fuelFeesPerDay[$arrayIncrement] = 0;
    $totalOperationalCostsPerDay[$arrayIncrement] = 0;
    $ticketSalesPerDay[$arrayIncrement] = 0;

    // Iterate through each flight to calculatae costs for the current day
    foreach ($flights as $flight){

        $DepartureDate = new DateTime($flight['DepartureDate']);
        $dayNumber = ($earliestDate->diff($DepartureDate)->days)+1;

        // Check if the flight is scheduled for the current day
        if($dayNumber == $day){

            // Accumulate daily revenue and costs
            $ticketRevenuePerDay += $ticketSalesPerFlight[isset($flight['FlightNumber'])] ?? 0;
            $fuelCostPerDay += $fuelFeesPerFlight[isset($flight['FlightNumber'])] ?? 0;
            $leasingCostPerDay += $leasingFeesPerFlight[isset($flight['FlightNumber'])] ?? 0;
            $terminalCostPerDay += $terminalFeesPerFlight[isset($flight['FlightNumber'])] ?? 0;

            // Calculate the total operational cost for the day
            $operationalCostPerDay += ($fuelFeesPerFlight[isset($flight['FlightNumber'])] ?? 0) + ($leasingFeesPerFlight[isset($flight['FlightNumber'])] ?? 0) + ($terminalFeesPerFlight[isset($flight['FlightNumber'])] ?? 0);
        }
    }

    // Store daily revenue and costs in respective arrays
    $ticketSalesPerDay[$arrayIncrement] = $ticketRevenuePerDay;
    $terminalFeesPerDay[$arrayIncrement] = $terminalCostPerDay;
    $leasingFeesPerDay[$arrayIncrement] = $leasingCostPerDay;
    $fuelFeesPerDay[$arrayIncrement] = $fuelCostPerDay;
    $totalOperationalCostsPerDay[$arrayIncrement] = $operationalCostPerDay;

    // Incremember array index
    $arrayIncrement++;
}


// Output the total operational costs per day in the table (within the HTML body)


// Calculate the total operational costs over the 2-week period
$totalOperationalCostsOverTwoWeeks = array_sum($totalOperationalCostsPerDay);


// Output this total cost in the table (within the HTML body)

// Query to fetch the number of passengers per day
$passengerSql = "SELECT f.DepartureDate, SUM(f.NumberOfPassengers) as TotalPassengers FROM FLIGHT f WHERE f.DepartureDate BETWEEN '2024-08-01' AND '2024-08-14' GROUP BY f.DepartureDate ORDER BY f.DepartureDate ASC";
$passengerResult = $mysqli->query($passengerSql);
$passengersPerDay = [];
if ($passengerResult && $passengerResult->num_rows > 0) {
    while($row = $passengerResult->fetch_assoc()) {
        $passengersPerDay[$row['DepartureDate']] = $row['TotalPassengers'];
    }
}






// Assuming '2024-08-01' is the start date and it corresponds to day 1
// $startDate = new DateTime('2024-08-01');
// $alignedOperationalCostsPerDay = [];
// foreach ($totalOperationalCostsPerDay as $day => $cost) {
//     $date = clone $startDate;
//     $date->add(new DateInterval('P' . ($day - 1) . 'D')); // P1D means add 1 day, so P0D is the start date
//     $dateKey = $date->format('Y-m-d');
//     $alignedOperationalCostsPerDay[$dateKey] = $cost;
// }




// Assuming $totalOperationalCostsPerDay and $passengersPerDay are filled correctly
// Calculate ticket sales per day using aligned dates

// Calculate the total revenue profit by summing up the ticket sales for each day
$totalRevenueProfit = array_sum($ticketSalesPerDay);



// Close the database connection at the end of script
$mysqli->close();
?>
<!DOCTYPE html>
<!-- Defines the document type and version of HTML -->
<html lang="en">
<!-- Specifies the language of the page -->


<head style="background-color:black">
   <!-- Head section of the HTML document with inline style -->
   <meta charset="UTF-8">
   <!-- Character set declaration to ensure proper rendering of text -->
   <title>Profit and Loss Report</title>
   <!-- Title of the document shown in the browser's title or tab bar -->
  
   <!-- Preconnect to Google Fonts to improve loading speeds -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  
   <!-- Linking multiple font families from Google Fonts for styling the document -->
   <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Quantico:ital,wght@0,400;0,700;1,400;1,700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Kode+Mono:wght@400..700&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Kode+Mono:wght@400..700&family=Noto+Sans+JP:wght@100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Kode+Mono:wght@400..700&family=Noto+Sans+JP:wght@100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  
   <!-- Inline CSS and @import directive to incorporate Roboto font with various weights and styles -->
   <style type="text/css">
       @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
     
  
       .roboto-thin {
 font-family: "Roboto", sans-serif;
 font-weight: 100;
 font-style: normal;
}


body{    
    transform: scale(1.25);
    transform-origin: top left; /* Adjust as needed */
    margin-left:0;
}
.roboto-light {
 font-family: "Roboto", sans-serif;
 font-weight: 300;
 font-style: normal;
}


.roboto-regular {
 font-family: "Roboto", sans-serif;
 font-weight: 400;
 font-style: normal;
}


.roboto-medium {
 font-family: "Roboto", sans-serif;
 font-weight: 500;
 font-style: normal;
}


.roboto-bold {
 font-family: "Roboto", sans-serif;
 font-weight: 700;
 font-style: normal;
}


.roboto-black {
 font-family: "Roboto", sans-serif;
 font-weight: 900;
 font-style: normal;
}
       body{ color:black; background-color:white }


       table { width: 100%; border-collapse: collapse; font-size: 12px; }
   th, td {width: 100%; text-align: left; padding: 14px; }
   th:first-child { width: 80%; }
   .tables-container { width: 100%; display: flex; justify-content: space-between; gap: 30px; }

    
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

 popup {
           display: none;
           position: fixed;
           top: 50%;
           left: 50%;
           transform: translate(-50%, -50%);
           background-color: black;
           padding: 20px;
           box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
           z-index: 2;
       }


       .blur-background {
           filter: blur(5px);
       }


       .close-btn {
           position: absolute;
           top: 10px;
           right: 10px;
           cursor: pointer;
       }
  
   </style>
</head>




<body>
<div style="background-color:black; margin-top:-5%">


  
<img src="calol.png" width="220" style="margin-top:1.3%; margin-left:-3%; position:absolute">


<div class="topnav" style="font-family: 'Inter', sans-serif; font-weight:400; font-size:13px; margin-top:5%">
 <a class="active" href="calc.php" style="font-weight: 200;  font-size:10px; margin-top:3%; margin-left:3%; color:yellow ">Financial Summary</a>
 <a href="repo.php" style="font-size:10px; margin-top:3%;  margin-left:3%; font-weight:100; ">Daily Report</a>
 <a href="route.php" style="font-size:10px; margin-top:3%; font-weight: 100; margin-left:3%; ">Routes</a>
 <a href="index.php" style="font-size:10px; margin-top:3%; font-weight: 100;  margin-left:1%; font-weight:100;">Airport Timetable</a>

</div>
   <h1 style="color:white; margin-left:40%; font-family: 'Roboto', sans-serif;
 font-weight: 400;
 font-style: normal; font-size:23px" >Financial Summary <span style="font-size:22px; display:block;font-weight:100; margin-left:0%">Profit & Loss Report</span><span style="font-size:13px; display:block; margin-left:5%; color: yellow;">Byte Me Contractors</span></h1>
<!--
   <form action="" method="post" >
           <select name="airportCode" style=" margin:center; border-radius:9px; width: 180px;height: 38px; font-size:14px; color:black; background-color:rgba(0,0,0,0); ">
               <?php foreach ($airports as $airport) : ?>
                   <option value="<?php echo $airport['AirportCode']; ?>" <?php if ($selectedAirportCode == $airport['AirportCode']) echo 'selected="selected"'; ?>>
                       <?php echo $airport['AirportCode']; ?>
                       <option value="" disabled selected > Select Flight </option>
                   </option>
               <?php endforeach; ?>
           </select>
           <input type="submit" value="generate revenue" style="background-color:rgba(0,0,0,0); color:black; text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3); border-radius:9px; width: 150px;height: 38px; font-size:14px ">
       </form>
               -->
  
               </br>
               </div>
<div class="tables-container">


   <table style="border: 1px solid white">
       <!-- Table headers -->
       <tr>
       <th style="background-color:black; color:black" >Day 0</th>
           <th style="background-color:black; color:white; white-space:nowrap;font-style: normal; font-family: 'Roboto', sans-serif;font-weight: 300;" >Day 1</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;" >Day 2</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 3</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 4</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 5</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 6</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 7</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 8</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 9</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 10</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 11</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 12</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 13</th>
           <th style="font-size: 11px; background-color:black; color:white; white-space:nowrap;font-family: 'Roboto', sans-serif;font-weight: 300;">Day 14</th>




       </tr>
      
                <!-- closing of black background -->


     


       <tr>
           <th style="background-color:#b8cde4; ">Operational Costs (Fuel, Terminal, and Leasing)</th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>
           <th style="background-color:#b8cde4; color:white"></th>




          
          
       </tr>


       <tr>
   <!-- Table header for fuel fees, explaining the calculation basis -->
   <th><b>Fuel Fees </b><span style="font-size:10px; display:block; font-weight:normal">Sum of flight distance x aircraft MPG x $6.19 Fixed Aviation Fuel Price</span> </th>
   <!-- PHP loop to output fuel fees for each day -->
   <?php for ($day = 0; $day <= 13; $day++): ?>
       <td>$<?= htmlspecialchars(number_format($fuelFeesPerDay[$day] ?? 0, 2)) ?></td> <!-- Formats and escapes the output for HTML -->
   <?php endfor; ?>
</tr>


<tr>
   <!-- Table header for terminal fees, with a brief explanation -->
   <th>Terminal Fees<span style="font-size:10px; display:block; font-weight:normal">Sum of Charges per Takeoffs and Landings ($2000 per charge)</span></th>
   <!-- Loop through each day to display terminal fees -->
   <?php for ($day = 0; $day <= 13; $day++): ?>
       <td style="color:black; white-space:nowrap">
           $ <?= htmlspecialchars(number_format($terminalFeesPerDay[$day] ?? 0, 2)) ?> <!-- Safe rendering of potentially variable data -->
       </td>
   <?php endfor; ?>
</tr>


<tr>
   <!-- Leasing fees header with a note on how they're calculated -->
   <th>Leasing Fees<span style="font-size:10px; display:block; font-weight:normal">Sum of Daily Price of Leasing per Boeing or Airbus Aircraft</span></th>
   <!-- Displays leasing fees per day -->
   <?php for ($day = 0; $day <= 13; $day++): ?>
       <td style="color:black; white-space:nowrap">
           $ <?= htmlspecialchars(number_format($leasingFeesPerDay[$day] ?? 0, 2)) ?> <!-- Ensures HTML is safely encoded -->
       </td>
   <?php endfor; ?>
</tr>


<tr>
   <!-- A summary row for total operational costs per week with a visual separator -->
   <th style="border-top: 1.5px solid black"><b>Total Operation Costs per day: </b></th>
   <!-- Loop to sum and display total operational costs for each day -->
   <?php foreach ($totalOperationalCostsPerDay as $day => $totalCost): ?>
       <td>$<?= htmlspecialchars(number_format($totalCost, 2)) ?></td> <!-- Formats the number to two decimal places and escapes HTML special characters -->
   <?php endforeach; ?>
</tr>


<tr>
   <!-- Overall summary of operational costs over the 2-week period, spanning all columns -->
   <td style="border-top: 1.5px solid black;"><b>Total Operation Costs over 2-week period:<span style="font-size:10px; display:block; font-weight:normal"></span></b></td>
   <td colspan="14"><b>$<?= htmlspecialchars(number_format($totalOperationalCostsOverTwoWeeks, 2)) ?></b></td> <!-- Calculates and displays the total -->
</tr>


      


  
 


  <tr>
           <th style="background-color:#5081bd; color:white">Revenue and Occupancy</th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>


       </tr>


  <!-- Row for displaying Ticket Sales with a description in the header -->
<tr>
    <th>Ticket Sales <span style="font-size:10px; display:block; font-weight:normal">Operational Costs / Number of Passengers</span></th>
    <!-- Loop through each day's ticket sales, format and display the sales amount -->
    <?php foreach ($ticketSalesPerDay as $day => $sales): ?>
        <td>$<?= htmlspecialchars(number_format($sales, 2)) ?></td> <!-- Output the formatted sales amount for each day -->
    <?php endforeach; ?>
</tr>

<!-- Row for displaying the total number of passengers transported each day -->
<tr>
    <th>Total Passengers Transported</th>
    <!-- Loop through each day's passenger count and display -->
    <?php foreach ($passengersPerDay as $day => $passengers): ?>
        <td><?= htmlspecialchars($passengers) ?></td> <!-- Display the passenger count for each day -->
    <?php endforeach; ?>
</tr>

<!-- Row for displaying the total revenue profit over a 2-week period -->
<tr>
    <td style="border-top: 1.5px solid black"><b>Total Revenue Profit over 2-week period:</b></td>
    <!-- Span the total revenue cell across all columns for the days -->
    <td colspan="14"><b>$<?= htmlspecialchars(number_format($totalRevenueProfit, 2)) ?></b></td> <!-- Display the total revenue profit, formatted as currency -->
</tr>




       <tr>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
          
       </tr>



  <tr>
           <th style="background-color:#5081bd; color:white">Total Financial Summary</th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>
           <th style="background-color:#5081bd; color:white"></th>


       </tr>

<!-- Row for displaying the total operational costs over a two-week period -->
<tr>
    <td><b>Total Operational Costs</b></td>
    <!-- Use colspan to extend the cell across all 14 columns for the two-week period -->
    <td colspan="14"><b>$<?= htmlspecialchars(number_format($totalOperationalCostsOverTwoWeeks, 2)) ?></b></td> <!-- Display the total operational costs, formatted as currency -->
</tr>

<!-- Row for displaying the total revenue over the same period -->
<tr>
    <td style="border-top: 1.5px solid black"><b>Total Revenue Costs</b></td>
    <!-- Spanning this row as well across all date columns -->
    <td colspan="14"><b>$<?= htmlspecialchars(number_format($totalRevenueProfit, 2)) ?></b></td> <!-- Display the total revenue, formatted as currency -->
</tr>

<!-- Row for calculating and displaying the profit or loss -->
<tr>
    <td style="border-top: 1.5px solid black"><b>Profit/Loss <span style="font-size:10px; display:block; font-weight:normal">Revenue - Operational</span></b></td>
    <!-- Spanning the profit/loss row across all columns -->
    <td colspan="14"><b>$<?= htmlspecialchars(number_format($totalRevenueProfit - $totalOperationalCostsOverTwoWeeks, 2)) ?></b></td> <!-- Calculate and display profit or loss by subtracting operational costs from revenue, formatted as currency -->
</tr>



   


   </table>

      
</div>


</div>
    <!-- copyright -->

   <div id="footer" style="border:none">
       <p>&copy; <?php echo date("Y"); ?> Comfort Airlines, Inc.</p>
   </div>
</body>
</html>
