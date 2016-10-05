<?php

// Check if we are able to get host name
if(!isset($_SERVER['HTTP_HOST'])) die();

// Get host name
$host="http".(isset($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['HTTP_HOST'];

// Constants
define("SITE_FULL_URL",$host."/");	// If project is not located in root directory, add /path/to/project/ here. Do same in .htaccess for RewriteBase
define("REC_DIR",__DIR__."/tmp/");	// Path to records folder
define("REC_EXT",".cdb");			// Extension of record file

// Check if directory for records is writable
if(!is_writable(REC_DIR)) die("Directory ".REC_DIR." is not writable.");

// Create .htaccess file in records directory it does not exists
if(!file_exists(REC_DIR.".htaccess")) file_put_contents(REC_DIR.".htaccess","Order allow,deny".PHP_EOL."Deny from all");

?>