<?

class handleVIEW {
    
    public function get_header($res=null) {
        include('templates/header_tpl.php');
    }  
    
    public function get_content($key) {
        if (strlen($key)!=32) { return false; }
        $file=new handleFILES();
        $res=$file->getFILEINFOS($key,true);//true = set $res['userID']
        if($res['item_type']=="bookmark" && $res['deleted_at']==0) {
            $this->update_file("hits",$key,$res);
            header ('HTTP/1.1 302 Found');
            header('Location: '.$res['name']);
            exit();
        }
        $this->update_file("hits",$key,$res);
        if($res['deleted_at']!=0) {
            $res=false;
        }
        $this->get_header($res);
        include('templates/content_view_tpl.php');    
    }   
    
    public function get_footer() {
        include('templates/footer_tpl.php');
    }

    public function get_thumbnail($key) {
        if (strlen($key)!=32) { return false; }
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select filename,extension,channel_name,hashname,data_storage,filesize,deleted_at from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($filename,$extension,$channel_name,$hashname,$data_storage,$filesize,$deleted_at);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if(!$hashname || $hashname==null || $deleted_at>0) {
            exit();
        } else {
            if(file_exists($data_storage.$channel_name.DIRECTORY_SEPARATOR.$hashname)) {
                //create thumbnail on the fly
               if(!create_thumbnail_otf($data_storage.$channel_name.DIRECTORY_SEPARATOR.$hashname)) {
                   header("Content-type: image/png");
                   readfile("img/unknown_type.png"); 
               }
                exit();
            } else {
               header("Content-type: image/png");
               readfile("img/unknown_type.png");
               exit();
            }
        }
    }
    
    public function get_download($key,$trigger=false) {
        if (strlen($key)!=32) { return false; }
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select filename,extension,channel_name,hashname,data_storage,filesize,item_type,shorturl,deleted_at from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($filename,$extension,$channel_name,$hashname,$data_storage,$filesize,$item_type,$shorturl,$deleted_at);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if(!$hashname || $hashname==null || $deleted_at>0) {
            exit();
        } else {
            if($item_type=="bookmark") {
                $this->update_file("hits",$key);
                header('Location: '.$shorturl);
                exit();
            }
            if(file_exists($data_storage.$channel_name.DIRECTORY_SEPARATOR.$hashname)) {
                    header('Content-Type: '.get_mime_type($extension));
                    header('Content-Disposition: attachment; filename="' . $filename . '"');                          
                if($trigger) {                   
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    $this->update_file("downloads",$key);
                } else {
                    $this->update_file("hits",$key);
                }
                header('Content-Length: '.$filesize);
                readfile($data_storage.$channel_name.DIRECTORY_SEPARATOR.$hashname);
            } else {
                die();
            }
            exit();
        }
    }

    private function update_file($method,$key,$res=false) {
            $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
            $sql="update files set ".$method." = ".$method."+1 where unique_hash='".$key."'";
            $stmt=$this->db->prepare($sql);
            $stmt->execute();
            $stmt->close();
            unset($stmt);
            if($method=="hits" && $res) {
                $pusher = new Pusher(PUSHER_key, PUSHER_secret, PUSHER_app_id);
                $a=$res['userID'];
                unset($res['userID']);
                $res['view_counter']++;
                $pusher->trigger('private-items_'.$a, 'update', $res, false , null, false);
            }
    }

    
}
