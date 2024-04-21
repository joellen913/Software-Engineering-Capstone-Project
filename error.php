<!--
Client: Professor Michael Oudshoorn  
Group Name: Byte Me                
Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V.  
Date: error.php
File Name: 3/22/2024 

Description: This HTML document is designed as an error page for Comfort Airlines. 
It is intended to be displayed when an error occurs, particularly during database 
connection failures or other backend issues. The document dynamically displays an 
error message passed to it from the PHP script encountering the error. It includes 
a header with the airline's name, a main content area for the error message, and a 
footer with copyright information.

Input: An error message passed by the PHP script that includes this page.

Output: A formatted HTML page displaying the error message and general 
information about the airline.

Languages Used: HTML, with PHP embedded for dynamic content and date.
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- The head section contains meta information about the document, including its title and a link to an external CSS stylesheet for styling. -->
<head> 
    <title>Comfort Airlines Error Page</title>
    <link rel="stylesheet" type="text/css" href="main.css" />
</head> 

<!-- The body section structures the content of the error page, including a page container div, header, main section for the error message, and a footer. -->
<body>
    <div id="page">
        <!-- Header section with the airline's name -->
        <div id="header">
            <h1>Comfort Airlines Timetable</h1> 
        </div> 

        <!-- Main content area displaying the error message. The message is dynamically inserted using PHP. -->
        <div id="main">
            <h2 class="top" style="margin-left:36%">Error, Page Cannot Connect.</h2>
            <p><?php echo $error; ?></p> <!-- PHP code to display the error message -->
        </div>

        <!-- Footer section with copyright information, dynamically including the current year using PHP. -->
        <div id="footer" style="color:white; margin-left:-16%">
            <p class="copyright">
                &copy; <?php echo date("Y"); ?> Comfort Airlines, Inc.
            </p>
        </div>

    </div><!-- End of page container -->
</body>
</html>
