<?

define('OPAQUE', md5(SYS_TITLE));
define('NONCEKEY', md5(SYS_TITLE.CLOUDAPP_SERVER));//$_SERVER['UNIQUE_ID']);
define('IECOMPAT', true);
define('REALM', 'CLOUDHOST');

class authentication {
    var $loggedIn;
    var $userID;
    var $trust;
    var $reducedTrust;
    var $authMethod;
    var $auth;
    var $a1;
    var $nonce;
    var $nextNonce;

    function authentication() {
        $return = '';
        $this->loggedIn = false;
        $this->userID = 0;
        $this->trust = 0;
        $this->reducedTrust = false;
        global $db;
        if(isset($_SERVER['Authorization'])) {
            // Just in case they ever fix Apache to send the Authorization header on, the following code is included
            $headers['Authorization'] = $_SERVER['Authorization'];
        }
        if(function_exists('apache_request_headers')) {
            // We are running PHP as an Apache module, so we can get the Authorization header this way
            $headers = apache_request_headers();
        }
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            // Basic authentication information can be retrieved from these server variables
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        }
        $requestURI = stripslashes($_SERVER['REQUEST_URI']);
        if(isset($headers['Authorization'])) {
            if(substr($headers['Authorization'],0,7) == 'Digest ') {
                // Digest authentication received
                // Get the Authorization header into a usable format
                $authtemp = explode(',', substr($headers['Authorization'],strpos($headers['Authorization'],' ')+1));
                $auth = array();
                foreach($authtemp as $key => $value) {
                    $value = trim($value);
                    if(strpos($value,'=') !== false) {
                        $lhs = substr($value,0,strpos($value,'='));
                        $rhs = substr($value,strpos($value,'=')+1);
                        if(substr($rhs,0,1) == '"' && substr($rhs,-1,1) == '"') {
                            $rhs = substr($rhs,1,-1);
                        }
                        $auth[$lhs] = $rhs;
                    }
                }
                $this->auth = $auth;
                if(strpos($auth['uri'], '?') === false && strpos($requestURI, '?') !== false && IECOMPAT) {
                    // Another joyful IE bug: URI in Authorization header does not include querystring
                    $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
                }
                if($requestURI == $auth['uri']) {
                    // Request URI matches URI in Authorization header
                    $this->authMethod = 'digest';
                    $nonceOptions = $this->nonceOptions();
                    $this->nextNonce = $nonceOptions[0];
                    if($auth['nonce'] == $nonceOptions[0]) {
                        // Up-to-date nonce received
                        $this->nonce = $nonceOptions[0];
                        $stale = false;
                    } elseif ($auth['nonce'] == $nonceOptions[1]) {
                        // 1-minute old nonce received - allowed
                        $this->nonce = $nonceOptions[1];
                        $stale = false;
                    } elseif ($auth['nonce'] == $nonceOptions[2]) {
                        // 2-minute old nonce received - allowed
                        $this->nonce = $nonceOptions[2];
                        $stale = false;
                    } else {
                        // Stale nonce received (probably more than 2 minutes old)
                        $this->nonce = $auth['nonce'];
                        $stale = true;
                    }
                    $query = "SELECT id, password, enabled, email FROM user WHERE email = '".sqlEscape($auth['username'])."'";
                    $rdsUser =& mysql_query($query,$db);
                    if($rdUser =& mysql_fetch_array($rdsUser)) {
                        // Username valid - determine password validity
                        $rdUser['userid']=$rdUser['id'];
                        $rdUser['username']=$rdUser['email'];
                        $rdUser['a1']=$rdUser['password'];
                        $rdUser['trust']=$rdUser['enabled'];
                        $this->userID = $rdUser['userid'];
                        $this->a1 = $rdUser['a1']; // md5($auth['username'].":".REALM.":".$rdUser['a1']);
                        $a2unhashed = $_SERVER['REQUEST_METHOD'].":".$requestURI;
                        if($auth['qop'] == 'auth-int') {
                            $body = '';
                            foreach($_POST as $key => $value) {
                                if($body != '') $body .= '&';
                                $body .= rawurlencode(sqlUnescape($key)) . '=' . rawurlencode(sqlUnescape($value));
                            }
                            if(isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
                                // In PHP < 4.3 get raw POST data from this variable
                                $body = $GLOBALS["HTTP_RAW_POST_DATA"];
                            }
                            if(version_compare(phpversion(), '4.3.0', '>=')) {
                                if($lines = @file('php://input')) {
                                    // In PHP >= 4.3 get raw POST data from this file
                                    $body = implode("\n", $lines);
                                }
                            }
                            $a2unhashed .= ':'.md5($body);
                            $trust = 3;
                        } else {
                            $trust = 2;
                        }
                        $a2 = md5($a2unhashed);
                        $combined = $this->a1.':'.
                                    $this->nonce.':'.
                                    $auth['nc'].':'.
                                    $auth['cnonce'].':'.
                                    $auth['qop'].':'.
                                    $a2;
                        $expectedResponse = md5($combined);
                        if(!isset($auth['opaque']) || $auth['opaque'] == md5(OPAQUE)) {
                            if($auth['response'] == $expectedResponse) {
                                // Password valid
                                if(!$stale) {
                                    // Everything is good!
                                    $this->loggedIn = true;
                                    $this->userID = $rdUser['userid'];
                                    $this->trust = min($rdUser['trust'], $trust);
                                    if($this->trust < $rdUser['trust']) {
                                        // Reduce trust if user has used less secure login than before (eg. auth rather than auth-int)
                                        $query = "UPDATE user SET enabled = ".$this->trust." WHERE id = ".$rdUser['userid'];
                                        mysql_query($query,$db);
                                        $this->reducedTrust = true;
                                    }
                                    $_SESSION['username']=sqlEscape($auth['username']);
                                    $_SESSION['id']=$this->userID;
                                } else {
                                    // Nonce is stale
                                    $wwwauth = 'Www-Authenticate: Digest ';
                                    $wwwauth .= 'qop="'.$auth['qop'].'", ';
                                    $wwwauth .= 'algorithm=MD5, ';
                                    $wwwauth .= 'realm="'.REALM.'", ';
                                    $wwwauth .= 'domain="'.CLOUDAPP_SERVER.'", ';
                                    $wwwauth .= 'nonce="'.$nonceOptions[0].'", ';
                                    $wwwauth .= 'opaque="'.md5(OPAQUE).'", ';
                                    $wwwauth .= 'stale=true ';
                                    header($wwwauth);
                                    $stalepage = new page(NOAUTH);
                                    $stalepage->response('401 Unauthorized');
                                    $stalepage->title('Login stale');
                                    $stalepage->h1('Login stale');
                                    $stalepage->add('
                                    <h>Login stale</h>
                                    <p>Stale nonce value, please re-authenticate.</p>
                                    ');
                                    $stalepage->display();
                                    die();
                                }
                            } else {
                                // Password mismatch - log an error and allow retry
                            }
                        } else {
                            // Opaque doesn't match - bad request
                            $this->authenticateFailure('Opaque doesn\'t match ('.$auth['opaque'].' != '.md5(OPAQUE).')');
                        }
                    } else {
                        // Username not found - allow retry
                    }
                } else {
                    // Request URI doesn't match URI in Authorization header - bad request
                    $this->authenticateFailure('
                    Request URI doesn\'t match URI in Authorization header ('.$requestURI.' != '.$auth['uri'].')
                    ');
                }
            }
        } elseif(isset($username) && isset($password)) {
            // Basic authentication received
            $this->authMethod = 'basic';
            $query = "SELECT id, enabled FROM user WHERE email = '".sqlEscape($username)."' ";
            $query .= "AND password = '".md5($username.":".REALM.":".$password)."'";
            $rdsUser =& mysql_query($query,$db);
            if($rdUser =& mysql_fetch_array($rdsUser)) {
                // Login successful
                $rdUser['userid']=$rdUser['id'];
                $rdUser['trust']=$rdUser['enabled'];
                $this->loggedIn = true;
                $this->userID = $rdUser['userid'];
                $this->trust = min($rdUser['trust'], 1);
                if($this->trust < $rdUser['trust']) {
                    // Reduce trust if user has used less secure login than before (eg. basic rather than digest)
                    $query = "UPDATE user SET enabled = ".$this->trust." WHERE id = ".$rdUser['userid'];
                    $db->query($query);
                    $this->reducedTrust = true;
                }
            }
        }
        #else {
            // Unrecognised authentication type - bad request
        #    $this->authenticateFailure();
        #}
        return $this->loggedIn;
    }
    
    function authenticationInfo($contentMD5) {
        
        if($this->loggedIn && $this->authMethod == 'digest') {
            // Work out authorisation response
            $a2unhashed = ":".stripslashes($_SERVER['REQUEST_URI']);
            if($this->auth['qop'] == 'auth-int') {
                $a2unhashed .= ':'.$contentMD5;
            }
            $a2 = md5($a2unhashed);
            $combined = $this->a1.':'.
                        $this->nonce.':'.
                        $this->auth['nc'].':'.
                        $this->auth['cnonce'].':'.
                        $this->auth['qop'].':'.
                        $a2;
            
            // Send authentication info
            $wwwauth = 'Authentication-Info: ';
            if($this->nonce != $this->nextNonce) {
                $wwwauth .= 'nextnonce="'.$this->nextNonce.'", ';
            }
            $wwwauth .= 'qop='.$this->auth['qop'].', ';
            $wwwauth .= 'rspauth="'.md5($combined).'", ';
            $wwwauth .= 'cnonce="'.$this->auth['cnonce'].'", ';
            $wwwauth .= 'nc='.$this->auth['nc'].'';
            header($wwwauth);
        }
        
    }
    
    function authenticate() {
        
        // Force client to login
        header('HTTP/1.1 401 Unauthorized');
        $nonceOptions = $this->nonceOptions();
        $wwwauth = 'Www-Authenticate: Digest ';
        $wwwauth .= 'qop="auth-int,auth", ';
        $wwwauth .= 'algorithm=MD5, ';
        $wwwauth .= 'realm="'.REALM.'", ';
        $wwwauth .= 'domain="'.CLOUDAPP_SERVER.'", ';
        $wwwauth .= 'nonce="'.$nonceOptions[0].'", ';
        $wwwauth .= 'opaque="'.md5(OPAQUE).'" ';
        #$wwwauth .= "\r\n".'WWW-Authenticate: Basic realm="'.REALM.'"';
        header($wwwauth);
        
        ?>
HTTP Digest: Access denied
        <?php
        die();
        
    }
    
    function authenticateFailure($details = '') {
        
        // Bad authorisation request received.
        header('HTTP/1.x 400 Bad Request');
        ?>
        <h1>Bad Request</h1>
        <p>
            Your browser attempted to authenticate with this server, but the request was invalid.  If this problem persists,
            please contact the support staff quoting the following text (copy and paste it if necessary):
        </p>
        <pre><samp><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'])?></samp></pre>
        <?php
        if($details != '') {
            echo '<p><samp>'.htmlspecialchars($details).'</samp></p>';
        }
        die();
        
    }
    
    function nonceOptions() {
        
        // Return acceptable nonce values
        $time = time();
        return array(
            md5(date('Y-m-d H:i',$time).':'.$_SERVER['HTTP_USER_AGENT'].':'.NONCEKEY),
            md5(date('Y-m-d H:i',$time-60).':'.$_SERVER['HTTP_USER_AGENT'].':'.NONCEKEY),
            md5(date('Y-m-d H:i',$time-120).':'.$_SERVER['HTTP_USER_AGENT'].':'.NONCEKEY)
        );
        
    }
}

function sqlEscape($text) {
    if(get_magic_quotes_gpc()) {
        return $text;
    } else {
        return addslashes($text);
    }
}

function sqlUnescape($text) {
    if(get_magic_quotes_gpc()) {
        return stripslashes($text);
    } else {
        return $text;
    }
}
