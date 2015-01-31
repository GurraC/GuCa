<?php
/**
 * Config-file for guca. Change settings here to affect installation.
 *
 */
 
 
/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly
 
 
/**
 * Define guca paths.
 *
 */
define('GUCA_INSTALL_PATH', __DIR__ . '/..');
define('GUCA_THEME_PATH', GUCA_INSTALL_PATH . '/theme/render.php');
 
 
/**
 * Include bootstrapping functions.
 *
 */
include(GUCA_INSTALL_PATH . '/src/bootstrap.php');
 
 
/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();

 
 
/**
 * Create the guca variable.
 *
 */
$guca = array();
 
 
/**
 * Site wide settings.
 *
 */
$guca['lang']         = 'sv';
$guca['title_append'] = ' | guca en webbtemplate';

$guca['header'] = <<<EOD
<img class='sitelogo' src='img/guca.png' alt='Logo'/>
<span class='sitetitle'>Welcome to - GuCa web templete</span>
<span class='siteslogan'>A site slogan.</span>
EOD;

$guca['footer'] = <<<EOD
<footer><span class='sitefooter'>Made by GuCa | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;

$guca['byline'] = <<<EOD
<footer class="byline">
  <p>This is a simple byline</p>
</footer>
EOD;




/**
 * The navbar
 *
 */
//$guca['navbar'] = null; // To skip the navbar
$guca['navbar'] = array(
  'class' => 'nb-plain',
  'items' => array(
    'home'         => array('text'=>'Home',         'url'=>'index.php',          'title' => 'Homepage'),
	'source'     => array('text'=>'Source',     'url'=>'source.php',      'title' => 'Source'),
  ),
  'callback_selected' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);


/**
 * Theme related settings.
 *
 */
//guca['stylesheet'] = 'css/style.css';
$guca['stylesheets'] = array('css/style.css');
$guca['favicon']    = 'favicon.ico';

/**
 * Settings for JavaScript.
 *
 */
$guca['javascript_include'] = array();
//$guca['javascript_include'] = array('js/main.js'); // To add extra javascript files
$guca['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
//$guca['jquery'] = null; // To disable jQuery
$guca['modernizr'] = 'js/modernizr.js';

/**
 * Google analytics.
 *
 */
$guca['google_analytics'] = '0'; // Set to null to disable google analytics



/**
 * Credentials
 *
 */
define('DB_USER', 'name'); // The database username
define('DB_PASSWORD', 'password'); // The database password
 

/**
 * Create array key CDatabase.
 *
 */

if(gethostname()=='Gurras'){
	$guca['database']['dsn']            = 'mysql:host=localhost;dbname=example;';
	$guca['database']['username']       = 'root';
	$guca['database']['password']       = '';
	$guca['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");}
else {
	$guca['database']['dsn']            = 'mysql:host=host.domain.com;dbname=example;';
	$guca['database']['username']       = DB_USER;
	$guca['database']['password']       = DB_PASSWORD;
	$guca['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");}
