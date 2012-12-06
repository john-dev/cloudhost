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
        $sql="insert into files (filename,extension,hashname,channel_name,data_storage,email,acl,filesize,shorturl,direct_shorturl,unique_hash,item_type,download_shorturl,created) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt=$this->db->prepare($sql);
        $ds=DATA_STORAGE;//unreference it
        $create_time = strtotime(gmdate("M d Y H:i:s", time()));
        $stmt->bind_param(
            'sssssssisssssi',
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
            $download_shorturl,
            $create_time
        );
        $stmt->execute();
        $stmt->close();
        return array('unique_hash'=>$unique_hash);
    }

    private function getFILETYPE($ext) {
        $file['image']=array('jpg','jpeg','png','tiff','tif','psd','eps','png','jpe','bmp','gif');//.. continue as you wish
        $file['video']=array('mp4','webm','mov','avi','mkv','mpg','mpeg','m4v','ogv');
        $file['audio']=array('mp3','wav','wave','aaf','aiff');
        $file['text']=array('txt','rtf','pdf','doc','docx');
        $file['archive']=array('zip','rar','tar','gz');
        $a="unknown";
        $a=(in_array($ext,$file['image']))?"image":$a;
        $a=(in_array($ext,$file['video']))?"video":$a;
        $a=(in_array($ext,$file['audio']))?"audio":$a;
        $a=(in_array($ext,$file['text']))?"text":$a;
        $a=(in_array($ext,$file['archive']))?"archive":$a;
        return $a;
    }
    
    public function addBOOKMARK($req,$email,$o_userID=false) {
        //probably the wrong place.. but who cares..
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select count(*),enabled,id from user where email=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$email);
        $stmt->bind_result($count,$enabled,$userID);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if($count>0 && $enabled>0) {
            $surl=new handleSHORTURL();
            $sql="insert into files (filename,hashname,extension,email,shorturl,unique_hash,item_type,acl,created) values (?,?,?,?,?,?,?,?,?)";
            $stmt=$this->db->prepare($sql);
            $a=md5($req['item']['name'].$email.microtime(true));
            $type="surl";
            $bm="bookmark";
            $g='public-read';
            $shorturl=$surl->newSHORTURL(CLOUDHOST_FILE_FRONTEND."/".$a);
            $created=$create_time = strtotime(gmdate("M d Y H:i:s", time()));
            $stmt->bind_param(
                'ssssssssi',
                $req['item']['redirect_url'],
                md5($req['item']['name']),
                $type,
                $email,
                $shorturl,
                $a,
                $bm,
                $g,
                $created
        );
        $stmt->execute();
        $stmt->close();
        $last_id=$this->db->insert_id;
        $res=array(
           'created_at'=>date("Y-m-d",$created)."T".date("H:i:s",$created)."Z",
           'deleted_at'=>null,
           'id'=>$last_id,
           'item_type'=>'bookmark',
           'name'=>$req['item']['name'],
           'private'=>true,
           'redirect_url'=>($item_type=="bookmark")?$filename:$req['item']['redirect_url'],
           'remote_url'=>($item_type=="bookmark")?$filename:null,
           'source'=>CURL_USER_AGENT,
           'updated_at'=>date("Y-m-d",$created)."T".date("H:i:s",$created)."Z",
           'view_counter'=>0,
           'href'=>(USE_SHORTURLS)?$shorturl:CLOUDHOST_FILE_FRONTEND."/".$a,
           'icon'=>"http://my.cld.me/images/item-types/bookmark.png",//fix that later
           'subscribed'=>false,
           "url"=>(USE_SHORTURLS)?$shorturl:CLOUDHOST_FILE_FRONTEND."/".$a,
           'content_url'=>(USE_SHORTURLS)?$shorturl:CLOUDHOST_FILE_FRONTEND."/".$a,
           'last_viewed_at'=>null,
           'gauge_id'=>null        
        );
        if($o_userID && PUSHER_app_id) {
            $res['userID']=$userID;
        }
        return $res;
        } else {
            return false;
        }
    }
}
