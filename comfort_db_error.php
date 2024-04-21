<!--
Client: Professor Michael Oudshoorn  
Group Name: Byte Me                
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V.  
Date: 3/22/2024
File Name: comfort_db_error.php

Description: This HTML document serves as a dedicated error page for
displaying database connection errors in the Comfort Airlines web 
application. It provides a user-friendly message indicating that a
 database error has occurred, along with suggestions for resolving
  the issue, such as ensuring the database is installed and MySQL 
  is running. Additionally, the specific error message received from 
  the attempt to connect to the database is displayed for debugging purposes.

Input: An error message variable ($error_message) provided by the PHP
script that failed to connect to the database.

Output: A styled HTML page that presents the error message and suggestions
for troubleshooting in a readable format.

Languages Used: HTML for the document structure, PHP for dynamic content
 (error message), and CSS for styling (linked externally).
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- Head section with meta information about the document, including the document title and a link to an external stylesheet for styling. -->
<head>
    <title>Comfort Airlines</title>
    <link rel="stylesheet" type="text/css" href="main.css" />
</head>

<!-- Body section containing the structure of the error page, including a container div, header, main content area, and footer. -->
<body>
    <div id="page">
        <!-- Header "Comfort Airlines" relevant to the current application -->
        <div id="header">
            <h1>My Guitar Shop</h1> <!-- title related to Comfort Airlines. -->
        </div>

        <!-- Main content area that includes a descriptive error message and troubleshooting steps. -->
        <div id="main">
            <h1>Database Error</h1>
            <a style="color:white" href="calc.php">calc php file </a>
            <p>There was an error connecting to the database.</p>
            <p>The database must be installed as described in the appendix.</p>
            <p>MySQL must be running as described in chapter 1.</p>
            <!-- PHP embedded code to dynamically display the error message passed to this page. -->
            <p>Error message: <?php echo $error_message; ?></p>
            <p>&nbsp;</p>
        </div><!-- End of main content area -->

        <!-- Footer section with copyright information, dynamically including the current year. -->
        <div id="footer">
            <p class="copyright">
                &copy; <?php echo date("Y"); ?> Comfort Airlines, Inc.
            </p>
        </div>

    </div><!-- End of page container -->
</body>
</html>
