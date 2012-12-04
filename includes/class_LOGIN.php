<?

class handleLOGIN {
    function __construct() {
        $this->session=$_SESSION;
        $this->post=$_POST;
        include('templates/header_tpl.php');  
        if(!isset($_POST['do_login'])) {
            $res=$this->showLOGINFORM();
        } else {
            $res=$this->validateLOGIN();
        }
        include('templates/content_tpl.php');
        include('templates/footer_tpl.php');
    }
    
    private function showLOGINFORM() {
      
       $res['title']="Log in to cloudapp / cloudhost";
       $res['content']='
        <div style="color: #ffffff;">
            <form id="login_form" class="login" method="post" action="">
                    email: <input type="text" id="login_name" name="login_name" size="20" />
                    pass: <input type="password" id="login_pass" name="login_pass" size="20" /><input type="submit" id="do_login" name="do_login" value="Log in" />
                </p>
            </form>
        </div>';
        return $res;
        
    }
    
    private function validateLOGIN() {
        include('ext_class_CLOUDAPI.php');
        $cloud = new Cloud_API($_POST['login_name'], $_POST['login_pass'], 'cloudhost');
        $items=$cloud->getItems();
        if($items['status']==true) {
            //check for cloudhost account here
            $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
            $sql="select count(*),enabled from user where email=?";
            $stmt=$this->db->prepare($sql);
            $stmt->bind_param('s',$_POST['login_name']);
            $stmt->bind_result($count,$enabled);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
            unset($stmt);
            if($count>0 && $enabled>0) {
                $res['title']="Login successfull";
                $_SESSION['logged_in']=true;
                $_SESSION['login_name']=$_POST['login_name'];
                $_SESSION['login_pass']=$_POST['login_pass'];
                $res['content']="<img src='img/loading.gif'> <br /> redirecting .. <meta http-equiv='refresh' content='1; URL=".CLOUDHOST_ACCOUNT_FRONTEND."'>";    
            } else {
                 $res['title']="This user has no access to cloudhost!";
                 $res['content']='
                <div style="color: #ffffff;">
                    <form id="login_form" class="login" method="post" action="">
                             email: <input type="text" id="login_name" name="login_name" size="20" />
                            pass: <input type="password" id="login_pass" name="login_pass" size="20" /><input type="submit" id="do_login" name="do_login" value="Log in" />
                        </p>
                    </form>
                </div>
            ';
            }
        } else {
            $res['title']="Wrong user credentials";
            $res['content']='
                <div style="color: #ffffff;">
                    <form id="login_form" class="login" method="post" action="">
                             email: <input type="text" id="login_name" name="login_name" size="20" />
                            pass: <input type="password" id="login_pass" name="login_pass" size="20" /><input type="submit" id="do_login" name="do_login" value="Log in" />
                        </p>
                    </form>
                </div>
            ';
        }
        return $res;        
    }
}
