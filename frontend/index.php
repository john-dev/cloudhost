<?
//cloudhost example web-frontend
include("../includes/config.php");
include("../includes/function.inc.php");
include("../includes/class_FILES.php");
include("../includes/class_VIEW.php");
include("../includes/class_LOGIN.php");
//-------------------------- Basic Configuration -----------------------------//
ini_set("display_errors", TRUE);
ini_set("display_errors", 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//-------------------------- Standard Requirements ---------------------------//

$view=new handleVIEW();

if (substr($_SERVER['REDIRECT_URL'],0,6)=="/view/") {
    if(substr($_SERVER['REDIRECT_URL'],0,13)=="/view/direct/") {
        $view->get_download(substr($_SERVER['REDIRECT_URL'],13));
    } elseif (substr($_SERVER['REDIRECT_URL'],0,15)=="/view/download/") {
        $view->get_download(substr($_SERVER['REDIRECT_URL'],15),true);
    } elseif (substr($_SERVER['REDIRECT_URL'],0,16)=="/view/thumbnail/") {
        $view->get_thumbnail(substr($_SERVER['REDIRECT_URL'],16));
    } else {
        $view->get_content(substr($_SERVER['REDIRECT_URL'],6));
        $view->get_footer();
    }
}
if (substr($_SERVER['REDIRECT_URL'],0,8)=="/account") {
    session_start();
    if(!isset($_SESSION['logged_in'])) {
        $login=new handleLOGIN();
    } else {
        #$iface=new createINTERFACE();
        #session_destroy();    
        echo "your-account splitted into cloudapp / cloudhost";
    }
}














