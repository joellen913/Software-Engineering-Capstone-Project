<!--
Client: Professor Michael Oudshoorn  
Group Name: Byte Me                
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V.  
Date: 3/22/2024
File Name: comfort_db.php

Description: This PHP script establishes a connection to a MySQL database using 
PDO (PHP Data Objects). It is designed to connect to the 'comfort_airlines_byteme' 
database hosted on 'joecool.highpoint.edu'. In the event of a failure to connect due 
to a PDOException, the script captures the error message, includes a specific error 
file for displaying the error ('comfort_db_error.php'), and then exits to prevent 
further script execution.

Input: Database credentials including DSN (Data Source Name), username, and password.

Output: A PDO object connected to the specified database if successful; otherwise, 
redirects to an error page.

Functions: None explicitly defined within this code snippet.
Languages Used: PHP.
-->

<?php
    // Define DSN (Data Source Name) for MySQL connection, including host and database name
    $dsn = 'mysql:host=db;dbname=comfort_airlines_byteme';
    // Database connection credentials
    $username = 'byteme';
    $password = 'letmein';

    try {
        // Attempt to establish a connection to the database using PDO
        $db = new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        // Catch any PDOException that occurs during the connection attempt
        // Store the error message from the exception
        $error_message = $e->getMessage();
        // Include a specific PHP file intended to handle and display database connection errors
        include('comfort_db_error.php');
        // Terminate script execution to prevent running subsequent code without a database connection
        exit();
    }
?>
