<?

//cloudhost example web-frontend
include("../includes/config.php");
include("../includes/function.inc.php");
include("../includes/class_FILES.php");
include("../includes/class_VIEW.php");
include("../includes/class_LOGIN.php");
include("../includes/class_SHORTURL.php");
include("../includes/ext_class_PUSHER.php");
include("../includes/class_AUTH.php");
//-------------------------- Basic Configuration -----------------------------//
#ini_set("display_errors", TRUE);
#ini_set("display_errors", 1);
#error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
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
        $db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select shorturl from files where email=?";
        $stmt=$db->prepare($sql);
        $stmt->bind_param('s',$_SESSION['login_name']);
        $stmt->bind_result($url);
        $stmt->execute();
        while($stmt->fetch()) {
            $res[]=$url;
        }
        $stmt->close();
        unset($stmt);
        foreach ($res as $key => $value) {
            $res[$key]=json_decode(getData("https://www.googleapis.com/urlshortener/v1/url?shortUrl=".$value."&projection=FULL"),true);
        }
        $view->get_header();
        echo "<pre>";
        foreach($res as $key => $value) {
            echo $key ." - ".print_r($value) ." - ".$value['longUrl']."<br />";   
        }
        echo "</pre>";
        $view->get_footer();
    }
}
function getData($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
}














