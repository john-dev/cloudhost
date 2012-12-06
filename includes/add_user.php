<?

if(isset($argv[1])) {
    $user=$argv[1];
} else {
    $user=false;
}

if(isset($argv[2])) {
    $pass=$argv[2];
} else {
    $pass=false;
}
include('config.php');
if(!$user) {
   die("wrong usage: add_user.php user pass\n"); 
}
if(!$pass) {
    //expect cloudapp auth use
    $db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
    $sql="replace into user set email=?";
    $stmt=$db->prepare($sql);
    $stmt->bind_param('s',$user);
    $stmt->execute();
} else {
    //expect cloudhost as auth server
    $db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
    $sql="replace into user set email=?,password=?";
    $stmt=$db->prepare($sql);
    $pass=md5($user.":CLOUDHOST:".$pass);
    $stmt->bind_param('ss',$user,$pass);
    $stmt->execute();
}

echo "user $user added\n";
