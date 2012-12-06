<?
session_name('_engine_session');
session_start();
include("../includes/config.php");
include("../includes/function.inc.php");
include("../includes/class_DIGEST.php");
include('../includes/ext_class_PUSHER.php');

$db = mysql_connect( DB_HOST , DB_USER , DB_PASS , DB_NAME) or die( "Connection error..." );
mysql_select_db( DB_NAME , $db);

if(!isset($_SERVER['REDIRECT_URL'])) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

$auth=new authentication();
if(!isset($_SESSION['logged_in'])) {
    if(!$auth->loggedIn) {
        $auth->authenticate();
    } else {
        header("Set-Cookie: user_credentials=".md5($_SESSION['username'])."; path=/; expires=Tue, 05-Mar-2099 16:13:17 GMT");
        header("Set-Cookie: _engine_session=".session_id()."; path=/;",false);
        header('Content-Type: application/json');
        $_SESSION['logged_in']=true;
        response();
    }    
} else {
    header('Content-Type: application/json');
    header("Set-Cookie: user_credentials=".md5($_SESSION['username'])."; path=/; expires=Tue, 05-Mar-2099 16:13:17 GMT");
    header("Set-Cookie: _engine_session=".session_id()."; path=/;",false);
    $_SESSION['logged_in']=true;
    response();
}

function response() {
    switch($_SERVER['REDIRECT_URL']) {
            case "/account":
                if(PUSHER_app_id) {
                    $socket=array( 
                    "auth_url" => "http://my.cl.ly/pusher/auth",
                    "api_key" => PUSHER_key,
                    "app_id" => PUSHER_app_id,
                    "channels" => array(
                        "items" => "private-items_".$_SESSION['id']
                        )
                    );
                } else {
                    $socket=array( 
                    "auth_url" => "http://my.cl.ly/pusher/auth",
                    "api_key" => '',
                    "app_id" => '',
                    "channels" => array(
                        "items" => ''
                        )
                    );
                }
                 $res=array(
                 "alpha" => false,
                 "created_at" => date("Y-m-d",time())."T".date("H:i:s",time())."Z",
                 "domain" => null,
                 "domain_home_page" => null,
                 "email" => $_SESSION['username'],
                 "id" => $_SESSION['id'],
                 "private_items" => true,
                 "updated_at" => date("Y-m-d",time())."T".date("H:i:s",time())."Z",
                 "activated_at" => date("Y-m-d",time())."T".date("H:i:s",time())."Z",
                 "subscribed" => false,
                 "socket" => $socket,
                 "subscription_expires_at" => null
                );
                echo json_custom_encode($res);
                break;
            case "/pusher/auth":
                //hmh.. 
                $pusher = new Pusher(PUSHER_key, PUSHER_secret, PUSHER_app_id);
                die($pusher->socket_auth($_GET['channel_name'],$_GET['socket_id']));    
                break;
            case "/items/new":
                $res=array(
                'url'=>"",
                'max_upload_size'=>0,
                'uploads_remaining'=>1,
                'params'=>array(
                    'AWSAccessKeyId'=>md5($_SESSION['username'].$_SESSION['id'].microtime(true).rand(1,999999)),
                    'key'=>md5('items/'.$_SESSION['username'].$_SESSION['id'].microtime(true).rand(1,999999).time())."/\${filename}",
                    'policy'=>md5($_SESSION['username'].$_SESSION['id'].microtime(true).rand(1,999999)),
                    'signature'=>md5($_SESSION['username'].$_SESSION['id'].microtime(true).rand(9,999)),
                    'acl'=>"public-read",
                    'success_action_redirect'=>""
                    )
                );
                echo json_custom_encode($res);
                break;
            case "/items":
                   die(json_custom_encode(array()));    
            break;
            default:
                header('HTTP/1.1 400 Bad Request');
                exit();
        }
}
