<?php
/**
 * This is a guca pagecontroller.
 *
 */
// Include the essential config-file which also creates the $guca variable with its defaults.
include(__DIR__.'/config.php'); 
 
 
// Add style for csource
$guca['stylesheets'][] = 'css/source.css';


// Create the object to display sourcecode
/**
 * Do it.
 *
 */

//$source = new CSource();
$source = new CSource(array('secure_dir' => '..', 'base_dir' => '..'));
// Do it and store it all in variables in the Anax container.
$guca['title'] = "Visa källkod";

$guca['header'] = <<<EOD
<img class='sitelogo' src='img/guca.png' alt='guca Logo'/>
<span class='sitetitle'>GuCa - Källkod</span>
<span class='siteslogan'>Källkoden till denna sajt</span>
EOD;

$guca['main'] = "<h1>Visa källkod</h1>\n" . $source->View();

 
 
// Finally, leave it all to the rendering phase of Guca.
include(GUCA_THEME_PATH);

// Add js/main.js for inklusion
//$guca['javascript_include'][] = 'js/main.js';
