<?php
/**
 * This is a guca pagecontroller.
 *
 */
// Include the essential config-file which also creates the $guca variable with its defaults.
include(__DIR__.'/config.php'); 
 
 
// Do it and store it all in variables in the guca container.
$guca['title'] = "My hello world page";
 

 
$guca['main'] = <<<EOD
<article>
	<h1>{$guca['title']}</h1>
	<p>This is my hello world example</p>
</article>
{$guca['byline']}
EOD;


 
 
// Finally, leave it all to the rendering phase of Guca.
include(GUCA_THEME_PATH);

// Add js/main.js for inklusion
//$guca['javascript_include'][] = 'js/main.js';


