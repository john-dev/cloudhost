<?
//cloudhost by john-dev
//-------------------------- Basic Configuration -----------------------------//
ini_set("display_errors", TRUE);
ini_set("display_errors", 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//-------------------------- Standard Requirements ---------------------------//

include("../includes/config.php");
include("../includes/function.inc.php");
include("../includes/class_REQUEST.php");//clouapp request handler
include("../includes/class_AUTH.php");
include("../includes/class_KEY.php");
include("../includes/class_UPLOAD.php");
include("../includes/class_FILES.php");
include("../includes/class_SHORTURL.php");

if (function_exists("apache_request_headers")) {
  $header_in = apache_request_headers();
} else {
   die('cannot access headers');
}

$req=new handleREQUEST($header_in);

if(DO_DEBUG) {
    echo "<pre>SERVER:<br />";
    print_r($_SERVER);
    echo "</pre><br />";
     echo "<pre>FILES:<br />";
    print_r($_FILES);
    echo "</pre>"; 
}












