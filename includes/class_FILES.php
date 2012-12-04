<?

class handleFILES {
    public function getFILEINFOS($key) {
        //sample output: {"created_at":"2012-11-30T12:51:51Z","deleted_at":null,"id":24429016,"item_type":"image","name":"ajax-loader.gif","private":true,"redirect_url":null,"remote_url":"http://f.cl.ly/items/1h3h1Q413Z0T3W2B2g1z/ajax-loader.gif","source":"Cloud/1.5.4 CFNetwork/520.5.1 Darwin/11.4.2 (x86_64) (MacBookPro8%2C1)","updated_at":"2012-11-30T12:51:51Z","view_counter":0,"href":"http://my.cl.ly/items/24429016","icon":"http://my.cld.me/images/item-types/image.png","subscribed":false,"url":"http://cl.ly/image/3Y1B1I0w3e2A","content_url":"http://cl.ly/image/3Y1B1I0w3e2A/ajax-loader.gif","download_url":"http://cl.ly/image/3Y1B1I0w3e2A/download/ajax-loader.gif","thumbnail_url":"http://thumbs.getcloudapp.com/3Y1B1I0w3e2A","last_viewed_at":null,"gauge_id":null}
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select unix_timestamp(created),deleted_at,id,item_type,filename,acl,shorturl,direct_shorturl,hits,enabled,download_shorturl,filesize from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($created,$deleted_at,$id,$item_type,$filename,$acl,$shorturl,$direct_shorturl,$hits,$enabled,$download_shorturl,$filesize);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if($enabled<1) {
            return false;
        }       
        $res=array();
        $res['created_at']=date("Y-m-d",$created)."T".date("H:i:s",$created)."Z";
        $res['deleted_at']=null;
        $res['id']=$id;
        $res['item_type']=$item_type;
        $res['name']=$filename;//the original filename, not the hased..
        $res['private']=($acl=="public-read")?false:true;
        $res['redirect_url']=null;
        $res['remote_url']=CLOUDHOST_FILE_FRONTEND_DIRECT."/".$key;//dont know yet where we need this for..
        $res['source']=CURL_USER_AGENT;
        $res['updated_at']=$res['created_at'];
        $res['view_counter']=$hits;
        $res['href']=(USE_SHORTURLS)?$shorturl:CLOUDHOST_FILE_FRONTEND."/".$key;//do we use the short-url we created while uploading the file
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
        return $res;
    }

    public function listFILES($email) {
          if(!$email) {
              return false;
          }
          $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
          if(!isset($_GET['per_page']) || !is_numeric($_GET['per_page']) || $_GET['per_page']>=100) {
              $_GET['per_page']=20;
          }
          $sql="select unique_hash from files where email=? order by id DESC limit 0,".$_GET['per_page'];
          $stmt=$this->db->prepare($sql);
          $stmt->bind_param('s',$email);
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
    

}

