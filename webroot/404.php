<?php 
/**
 * This is a guca pagecontroller.
 *
 */

// Include the essential config-file which also creates the $guca variable with its defaults.
include(__DIR__.'/config.php'); 


// Do it and store it all in variables in the guca container.
$guca['title'] = "404";

$guca['main'] = "This is a guca 404. Document is not here.";
 
// Send the 404 header 
header("HTTP/1.0 404 Not Found"); 
 
 
// Finally, leave it all to the rendering phase of Guca.
include(GUCA_THEME_PATH);

