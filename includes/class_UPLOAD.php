<?

class handleUPLOAD {
    public function newUPLOAD($file) {
        $this->key=new handleKEY();
        $res=$this->key->validateKEY();
        if(!$res) {
            return false;
        }

        $channel_name=$res['email'];
        $out_dir=DATA_STORAGE.$channel_name;
        if(!is_dir($out_dir)) {
            mkdir($out_dir,0774);
        }
        $original_filename=$file['file']['name'];
        $file_extension=strtolower(substr($file['file']['name'],strrpos($file['file']['name'],".")+1));
        if(!$file_extension || $file_extension=="") {
            $file_extension="na";//lets handle files without extension later..
        }
        $out_file=md5($res['key']).".".$file_extension;//should be unique for that user, thanks to cloudapp
        move_uploaded_file($file['file']['tmp_name'], $out_dir.DIRECTORY_SEPARATOR.$out_file);  
        $res=$this->addFILE($file,$file_extension,$out_file,$res,$channel_name,$original_filename,$out_dir);
        return $res;//will be the files unique hash
    }

    private function addFILE($file,$file_extension,$out_file,$res,$channel_name,$original_filename,$out_dir) {
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        //create shorturls.. (ill use google here.. feel free to use whatever you want..)
        $surl=new handleSHORTURL();
        $unique_hash=md5($channel_name.$out_file.microtime(true));
        $shorturl=$surl->newSHORTURL(CLOUDHOST_FILE_FRONTEND."/".$unique_hash);
        $direct_shorturl=$surl->newSHORTURL(CLOUDHOST_FILE_FRONTEND_DIRECT."/".$unique_hash);
        $download_shorturl=$surl->newSHORTURL(CLOUDHOST_FILE_FRONTEND_DOWNLOAD."/".$unique_hash);
        $type=$this->getFILETYPE($file_extension);
        $sql="insert into files (filename,extension,hashname,channel_name,data_storage,email,acl,filesize,shorturl,direct_shorturl,unique_hash,item_type,download_shorturl) values (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt=$this->db->prepare($sql);
        $ds=DATA_STORAGE;//unreference it
        $stmt->bind_param(
            'sssssssisssss',
            $original_filename,
            $file_extension,
            $out_file,
            $channel_name,
            $ds,//data storage so we can change this later in the config, if we want to move new files to a different location..
            $res['email'],
            $res['acl'],//will files be availbale public? (not working yet; always public)
            filesize($out_dir.DIRECTORY_SEPARATOR.$out_file),
            $shorturl,
            $direct_shorturl,
            $unique_hash,
            $type,
            $download_shorturl
        );
        $stmt->execute();
        $stmt->close();
        return array('unique_hash'=>$unique_hash);
    }

    private function getFILETYPE($ext) {
        $image=array('jpg','jpeg','png','tiff','tif','psd','eps','png','bmp','gif');//.. continue as you wish
        return (in_array($ext,$image))?"image":"unknown";
    }
    
    public function addBOOKMARK($req,$email) {
        //probably the wrong place.. but who cares..
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select count(*),enabled from user where email=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$email);
        $stmt->bind_result($count,$enabled);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if($count>0 && $enabled==1) {
            $surl=new handleSHORTURL();
            $shorturl=$surl->newSHORTURL($req['item']['redirect_url']);
            $sql="insert into files (filename,hashname,extension,email,shorturl,unique_hash,item_type) values (?,?,?,?,?,?,?)";
            $stmt=$this->db->prepare($sql);
            $a=md5($req['item']['name'].$email.microtime(true));
            $type="surl";
            $bm="bookmark";
            $stmt->bind_param(
                'sssssss',
                $req['item']['name'],
                md5($req['item']['name']),
                $type,
                $email,
                $shorturl,
                $a,
                $bm
        );
        $stmt->execute();
        $stmt->close();
        return $res=array(
           'created_at'=>date("Y-m-d",time())."T".date("H:i:s",time())."Z",
           'deleted_at'=>null,
           'id'=>0,
           'item_type'=>'bookmark',
           'name'=>$req['item']['name'],
           'private'=>true,
           'redirect_url'=>$req['item']['redirect_url'],
           'remote_url'=>null,
           'source'=>CURL_USER_AGENT,
           'updated_at'=>date("Y-m-d",time())."T".date("H:i:s",time())."Z",
           'view_counter'=>0,
           'href'=>CLOUDHOST_FILE_FRONTEND."/",$a,
           'icon'=>"http://my.cld.me/images/item-types/bookmark.png",//fix that later
           'subscribed'=>false,
           "url"=>$shorturl,
           'content_url'=>$shorturl,
           'last_viewed_at'=>null,
           'gauge_id'=>null        
        );
        //sample{"created_at":"2012-12-02T19:16:33Z","deleted_at":null,"id":24481988,"item_type":"bookmark","name":"https://github.com/matthiasplappert/CloudApp-API-PHP-wrapper","private":true,"redirect_url":"https://github.com/matthiasplappert/CloudApp-API-PHP-wrapper","remote_url":null,"source":"Cloud/1.5.4 CFNetwork/520.5.1 Darwin/11.4.2 (x86_64) (MacBookPro8%2C1)","updated_at":"2012-12-02T19:16:33Z","view_counter":0,"href":"http://my.cl.ly/items/24481988","icon":"http://my.cld.me/images/item-types/bookmark.png","subscribed":false,"url":"http://cl.ly/2W0h2l3R0l1f","content_url":"http://cl.ly/2W0h2l3R0l1f","last_viewed_at":null,"gauge_id":null}
        } else {
            return false;
        }
    }
}
