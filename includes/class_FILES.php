<?

class handleFILES {
    public function getFILEINFOS($key, $userID=false) {
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select created,deleted_at,id,item_type,filename,acl,shorturl,direct_shorturl,hits,enabled,download_shorturl,filesize,extension,email from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($created,$deleted_at,$id,$item_type,$filename,$acl,$shorturl,$direct_shorturl,$hits,$enabled,$download_shorturl,$filesize,$extension,$email);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if($enabled>1) {
            return false;
        }       
        $res=array();
        $res['created_at']=date("Y-m-d",$created)."T".date("H:i:s",$created)."Z";
        $res['deleted_at']=($deleted_at==0)?null:date("Y-m-d",$deleted_at)."T".date("H:i:s",$deleted_at)."Z";
        $res['id']=$id;
        $res['item_type']=$item_type;
        $res['name']=$filename;//the original filename, not the hased..
        $res['private']=true;
        $res['redirect_url']=($item_type=="bookmark")?$filename:null;
        $res['remote_url']=CLOUDHOST_FILE_FRONTEND_DIRECT."/".$key;//dont know yet where we need this for..
        $res['source']=CURL_USER_AGENT;
        $res['updated_at']=$res['created_at'];
        $res['view_counter']=$hits;
        $res['href']=CLOUDAPP_SERVER."/items?key=".$key;//do we use the short-url we created while uploading the file
        $res['icon']="http://my.cld.me/images/item-types/image.png";//fix that later..
        $res['subscribed']=false;//O_o
        $res['url']=(USE_SHORTURLS)?$shorturl:CLOUDHOST_FILE_FRONTEND."/".$key;//do we use the short-url we created while uploading the file
        $res['content_url']=(USE_SHORTURLS)?$direct_shorturl:CLOUDHOST_FILE_FRONTEND_DIRECT."/".$key;//do we use the short-url we created while uploading the file
        $res['download_url']=(USE_SHORTURLS)?$download_shorturl:CLOUDHOST_FILE_FRONTEND_DOWNLOAD."/".$key;//do we use the short-url we created while uploading the file
        $res['thumbnail_url']=CLOUDHOST_FILE_FRONTEND_THUMBNAIL."/".$key;
        $res['last_viewed_at']=null;
        $res['gauge_id']=null;//gau what?
        $res['filesize']=$filesize;//works? works!
        if($res['item_type']=="bookmark" && USE_SHORTURLS) {
            $stats=new handleSHORTURL();
            $res['view_counter']=$stats->getHITS_SIMPLE($res['url']);
        }
        $res['ext']=$extension;
        if($userID && PUSHER_app_id) {
            $auth=new handleAUTH();
            $res['userID']=$auth->username_to_user($email);
        }
        return $res;
    }

    public function listFILES($email) {
          if(!$email) {
              return false;
          }
          if(isset($_GET['page']) && isset($_GET['per_page'])) {
              $limit=($_GET['page']==1)?0 .",".$_GET['per_page']:($_GET['page']-1)*$_GET['per_page'].",".$_GET['per_page'];
          } else {
              $limit="0,20";
          }
          if(isset($_GET['deleted'])) {
              $enabled=($_GET['deleted']=="false")?"1":"0";
          } else {
              $enabled="1";
          }
          $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
          if(!isset($_GET['per_page']) || !is_numeric($_GET['per_page']) || $_GET['per_page']>=100) {
              $_GET['per_page']=20;
          }
          if(isset($_GET['type'])) {
                $sql="select unique_hash from files where email=? and item_type=? and enabled=? order by id DESC LIMIT ".$limit;
                $stmt=$this->db->prepare($sql);
                $stmt->bind_param('ssi',$email,$_GET['type'],$enabled);
          } else {
                $sql="select unique_hash from files where email=? and enabled=? order by id DESC LIMIT ".$limit;
                $stmt=$this->db->prepare($sql);
                $stmt->bind_param('si',$email,$enabled);
          }
          $stmt->bind_result($key);
          $stmt->execute();
          $res=array();
          while($stmt->fetch()) {
              $res[]=$key;
          }
          $stmt->close();
          unset($stmt);
          $ret=array();
          foreach($res as $key => $value) {
            $ret[]=$this->getFILEINFOS($value);    
          }
          return $ret;
    }

    public function changeFILE($key,$body_in) {
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);       
        $sql="select id,email,extension from files where unique_hash=?";        
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);       
        $stmt->bind_result($id,$email,$ext);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if(!$id || $id=null) {
            return false;
        }       
        $del_time = strtotime(gmdate("M d Y H:i:s", time()));
        $a=intval(0);
        if($_SERVER['REQUEST_METHOD']=="PUT") {//restore
            if(isset($body_in['item']['name'])) {
                $req=substr($body_in['item']['name'],0,strrpos($body_in['item']['name'],"."));
                if($req=="") {
                    $req=$body_in['item']['name'];
                }
                $req.=".".$ext;
                $sql="update files set filename=? where unique_hash=?";
                $stmt=$this->db->prepare($sql);
                $stmt->bind_param('ss',$req,$key);
            } else {
                $a=intval(1);
                $del_time=0;
                $sql="update files set deleted_at=?,enabled=? where unique_hash=?";
                $stmt=$this->db->prepare($sql);
                $stmt->bind_param('iis',intval($del_time),$a,$key);    
            }
        } else {
            $sql="update files set deleted_at=?,enabled=? where unique_hash=?";
            $stmt=$this->db->prepare($sql);
            $stmt->bind_param('iis',intval($del_time),$a,$key);    
        }        
        $stmt->execute();
        $stmt->close();
        unset($stmt);
        return true;
    }   

}

