<?
class handleKEY {
    
    public function createKEY($body) {
        //up to here all authentication is done, and cloudapp even granted us a unique key, we can use to create upload tickets!
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="insert into `keys` (AWSAccessKeyId,uploads_remaining,`key`,policy,signature,acl,max_upload_size,success_action_redirect,email) values (?,?,?,?,?,?,?,?,?)";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param(
            'sissssiss',
            
            $body['params']['AWSAccessKeyId'],
            $body['uploads_remaining'],
            $body['params']['key'],
            $body['params']['policy'],
            $body['params']['signature'],
            $body['params']['acl'],
            $body['max_upload_size'],
            $body['params']['success_action_redirect'],
            $body['email']//we need to know who this key belongs to, to determine the file's destination path.. later
        );
        $stmt->execute();
        $stmt->close();
        unset($stmt);
        return true;
    }

    public function validateKEY() {
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $creds=array();
        $creds=$_POST;
        $sql="select 
                success_action_redirect,
                `key`,
                acl,
                email,
                unix_timestamp(created) 
            from 
                `keys`
            where
                AWSAccessKeyId=?
            and
                success_action_redirect=?
            and    
                `key`=?
            and
                signature=?
            and
                acl=?
            and
                policy=?
             ";
        
        
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param(
            'ssssss', 
            $creds['AWSAccessKeyId'],
            $creds['success_action_redirect'],
            $creds['key'],
            $creds['signature'],
            $creds['acl'],
            $creds['policy']
        );
        $stmt->bind_result($success_action_redirect,$key,$acl,$email,$created);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        unset($stmt);
        if($created) {
            if($created+KEY_VALIDATION_TIME>time()) {
                    return array('success_action_redirect'=>$success_action_redirect,'key'=>$key,'acl'=>$acl,'email'=>$email);
            } else {
                    return false;
            }
        } else {
            return false;
        }
    }
}
