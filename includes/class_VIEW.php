<?

class handleVIEW {
    
    public function get_header() {
        include('templates/header_tpl.php');
    }  
    
    public function get_content($key) {
        if (strlen($key)!=32) { return false; }
        $file=new handleFILES();
        $res=$file->getFILEINFOS($key);
        if($res['item_type']=="bookmark") {
            $this->update_file("hits",$key);
            header('Location: '.$res['url']);
            exit();
        }
        $this->get_header();
        include('templates/content_view_tpl.php');    
    }   
    
    public function get_footer() {
        include('templates/footer_tpl.php');
    }

    public function get_thumbnail($key) {
        if (strlen($key)!=32) { return false; }
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select filename,extension,channel_name,hashname,data_storage,filesize from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($filename,$extension,$channel_name,$hashname,$data_storage,$filesize);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if(!$hashname || $hashname==null) {
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
        $sql="select filename,extension,channel_name,hashname,data_storage,filesize,item_type,shorturl from files where unique_hash=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$key);
        $stmt->bind_result($filename,$extension,$channel_name,$hashname,$data_storage,$filesize,$item_type,$shorturl);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if(!$hashname || $hashname==null) {
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

    private function update_file($method,$key) {
            $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
            $sql="update files set ".$method." = ".$method."+1 where unique_hash='".$key."'";
            $stmt=$this->db->prepare($sql);
            $stmt->execute();
            $stmt->close();
            unset($stmt);
    }

    
}
