<?

class handleREQUEST {
    private $header_in;
   
    function __construct($header_in=false) {
       if(!is_array($header_in)) {
           return false;
       }
       $this->header_in=$header_in;
       $this->_ch = curl_init();
       $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
       $this->splitREQUEST();
    }
    
    private function splitREQUEST() {
        
        if(isset($this->header_in['Authorization'])) {
            $headers = array('Content-Type: application/json',
                 'Accept: application/json',
                 'Authorization: '.$this->header_in['Authorization'],
                 'Accept-Language: de-de',
                 'Accept-Encoding: gzip, deflate'
                 );
                 $this->email=explode(",",$this->header_in['Authorization']);
                 $this->email=substr($this->email[0],strpos($this->header_in['Authorization'],'"')+1,-1);
        } elseif(isset($this->header_in['Cookie'])) {
            $headers = array('Content-Type: application/json',
                 'Accept: application/json',
                 'Cookie: '.$this->header_in['Cookie'],
                 'Accept-Language: de-de',
                 'Accept-Encoding: gzip, deflate'
                 );               
                 $sql="select email from user_session where cookie=?";
                 $stmt=$this->db->prepare($sql);
                 $stmt->bind_param('s',split_cookie($this->header_in['Cookie']));
                 $stmt->bind_result($this->email);
                 $stmt->execute();
                 $stmt->fetch();
                 $stmt->close();
                 unset($stmt);
        } else {
            $headers = array('Content-Type: application/json',
                 'Accept: application/json',
                 'Accept-Language: de-de',
                 'Accept-Encoding: gzip, deflate'
                 );
        }
        $method=$_SERVER['REQUEST_METHOD'];
        $body=($method=="POST")?$_POST:array();
        $query=$this->query_CLOUDAPP(CLOUDAPP_SERVER.$_SERVER['REDIRECT_URL']."?".$_SERVER['QUERY_STRING'], $headers, $body, $method);
        $this->header_query=get_headers_from_curl_response($query);
        $this->body_query=json_decode(get_body_from_curl_response($query),true);
        $this->body_query_plain=$query;
        $this->body_in=json_decode(@file_get_contents('php://input'),true);
        if($_SERVER['REDIRECT_URL']!="/upload" && $_SERVER['REDIRECT_URL']!="/items/s3") {//we handle that on our own
            switch ($this->header_query['http_code']) {
                case "HTTP/1.1 100 Continue":
                case "HTTP/1.1 422 ":
                case "HTTP/1.1 200 OK":
                    if(isset($this->header_query['Set-Cookie_engine'])) {
                        $auth=new handleAUTH();
                        $res=$auth->doAUTH($this->email,split_cookie($this->header_query['Set-Cookie_engine']));    
                    } else {
                        $auth=new handleAUTH();
                        $res=$auth->doAUTH($this->email,split_cookie($this->header_in['Cookie']));
                    }
                    if($res) {
                        $this->authed=true;
                        header('HTTP/1.1 200 OK');
                        header('Server: thin 1.5.0 codename Knife');
                        header('Cache-Control: max-age=0, private, must-revalidate');
                        header('Content-Type:  application/json; charset=utf-8');
                        if(isset($this->header_query['Set-Cookie'])) {
                            header('Set-Cookie:  '.$this->header_query['Set-Cookie'].'');    
                        }
                        if(isset($this->header_query['Set-Cookie_engine'])) {
                            header('Set-Cookie:  '.$this->header_query['Set-Cookie_engine'],false);    
                        }
                        if(isset($this->header_query['X-Runtime'])) {
                            header('X-Runtime:   '.$this->header_query['X-Runtime']);    
                        }
                        header('X-Ua-Compatible: IE=Edge,chrome=1');
                        header('Connection:  keep-alive'); 
                         
                    } else {
                        header('HTTP/1.1 401 Unauthorized');
                        header('Server: thin 1.5.0 codename Knife');
                        header('Cache-Control: no-cache');
                        header('Content-Type: text/plain; charset=utf-8');
                        header('Www-Authenticate: '.$this->header_query['Www-Authenticate']);
                        header('X-Runtime: 0.000000');
                        header('X-Ua-Compatible: IE=Edge,chrome=1');
                        header('Connection: keep-alive');
                        echo "HTTP Digest: Access denied";
                        exit();
                    }
                    break;                                  
                case "HTTP/1.1 401 Unauthorized":
                    header('HTTP/1.1 401 Unauthorized');
                    header('Server: thin 1.5.0 codename Knife');
                    header('Cache-Control: no-cache');
                    header('Content-Type: text/plain; charset=utf-8');
                    header('Www-Authenticate: '.$this->header_query['Www-Authenticate']);
                    header('X-Runtime: 0.000000');
                    header('X-Ua-Compatible: IE=Edge,chrome=1');
                    header('Connection: keep-alive');
                    echo "HTTP Digest: Access denied";
                    exit();
                    break;
                case "HTTP/1.1 400 Bad Request":
                    header('HTTP/1.1 400 Bad Request');
                    header('Server: thin 1.5.0 codename Knife');
                    header('Cache-Control: no-cache');
                    header('Content-Type: text/plain; charset=utf-8');
                    header('X-Runtime: 0.000000');
                    header('X-Ua-Compatible: IE=Edge,chrome=1');
                    header('Connection: keep-alive');
                    exit();
                    break;
                default:
                    die("unknown response: ".print_r($this->header_query));
                    break;
            }
        }
        
        switch($_SERVER['REDIRECT_URL']) {
            case "/account":
                //we already handled that
                echo json_custom_encode($this->body_query);
                break;
            case "/pusher/auth":
                //updated
                $this->handlePUSHER();
                break;
            case "/items/new":
                //updated
                $this->handleNEW();
                break;
            case "/upload":
                $this->handleUPLOAD();
                break;
            case "/items/s3":
                $this->handleS3();
                break;
            case "/items":
                //handle bookmarks here
                if($_SERVER['REQUEST_METHOD']=="POST") {//split itemlisting vs. addbookmark
                    $this->handleADDBOOKMARK();
                } else {
                    $this->handleLISTITEMS();    
                }
            break;
            case "/view/direct":
            case "/view":
            default:
                header('Location: '.CLOUDHOST_ACCOUNT_FRONTEND);
                exit();
        }
    }
    
    private function query_CLOUDAPP($url,$headers,$body,$method='GET') {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_USERAGENT, CLOUDAPP_SERVER.$url);
        curl_setopt($this->_ch, CURLOPT_HEADER, true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_ch, CURLOPT_COOKIEFILE, '/dev/null'); // enables cookies
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $body);
        
        $response = curl_exec($this->_ch);
        return $response;
    }
    
    private function handleNEW() {
        //create and modify response(=s3 key) here
        $this->body_query['url']=CLOUDAPP_SERVER."/upload";
        $this->body_query['max_upload_size']=USER_MAX_UPLOADSIZE;
        $this->body_query['params']['acl']=CREATE_FILES_PUBLIC;
        $this->body_query['email']=$this->email;
        $key=new handleKEY();
        $res=$key->createKEY($this->body_query);        
        unset($this->body_query['email']);//dont output to client..
        if($res) {
            echo json_custom_encode($this->body_query);//modified version
        } else {
            die('something went wrong while creating a key');
        }
        exit();
    }

    private function handlePUSHER() {
        //we dont need that authcode, so lets skip this step
        echo json_custom_encode($this->body_query);        
        exit();
    }
    
    private function handleUPLOAD() {
        $upl=new handleUPLOAD();
        $res=$upl->newUPLOAD($_FILES);
        if(!$res) {
            header('HTTP/1.1 400 Bad Request');
            header('x-amz-id-2: '.md5(microtime(true)));
            header('x-amz-request-id: '.md5(microtime(true)));
            header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time())); 
            header('Content-Length: 0');
            header('Expires: 0');
            header('Cache-Control: no-cache');
            exit();  
        }
        header('HTTP/1.1 303 See Other');
        header('x-amz-id-2: '.md5(microtime(true)));
        header('x-amz-request-id: '.md5(microtime(true)));
        header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time())); 
        header('Location: '.CLOUDAPP_SERVER.'/items/s3?key='.$res['unique_hash']);//default response
        header('Content-Length: 0');
        header('Expires: 0');
        header('Cache-Control: no-cache');
        exit();  
    }
    
     private function handleS3() {
        //we got here because we have a valid file upload.. bam!
        if(!isset($_GET['key'])) {
            die('no file given');
        }
        $file=new handleFILES();
        $res=$file->getFILEINFOS($_GET['key']);
        echo json_custom_encode($res);
        exit();
    }  

    private function handleADDBOOKMARK() {
        $bmark=new handleUPLOAD();
        $res=$bmark->addBOOKMARK($this->body_in,$this->email);
        echo json_custom_encode($res);
        exit();
    }

    private function handleLISTITEMS() {
        //mix listitems here?
        $list=new handleFILES();
        $res=$list->listFILES($this->email);
        if(COMBINE_LISTINGS) {
            $a=array_merge($res,$this->body_query);
            $a=sort2d($a,"created_at",'desc');
            echo json_custom_encode($a);    
        } else {
            echo json_custom_encode($res);//list channel items    
        }
        exit();
    }
}
