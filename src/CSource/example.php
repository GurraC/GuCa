 <?php
/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly



/**
 * Do it.
 *
 */
include('CSource.php');
$source = new CSource();

?><!doctype html>
<html lang='en'>
<meta charset='utf-8' />
<title>View sourceode</title>
<meta name="robots" content="noindex" />
<meta name="robots" content="noarchive" />
<meta name="robots" content="nofollow" />
<link rel='stylesheet' type='text/css' href='source.css'/>
<body>
<h1>View sourcecode</h1>
<p>
The following files exists in this folder. Click to view.
</p>
<?=$source->View()?>



