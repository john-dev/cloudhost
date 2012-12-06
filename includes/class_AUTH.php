<?
class handleAUTH {
    public function doAUTH($email,$header) {                      
            $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);           
            $sql="select count(*) from user where email = ? and enabled>0";            
            $stmt=$this->db->prepare($sql);
            $stmt->bind_param('s',$email);
            $stmt->bind_result($user_exists);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
            unset($stmt);
            if($user_exists<1) {
                return false; //if user does not exist in a privat system
            } else {
                $sql="REPLACE INTO user_session SET email=?,cookie=?";
                $stmt=$this->db->prepare($sql);
                $stmt->bind_param('ss',$email,$header);
                $stmt->execute();
                $stmt->close();
                unset($stmt);
            }
            return true;
        }
    public function username_to_user($email) {
        $this->db=new mysqli( DB_HOST , DB_USER , DB_PASS , DB_NAME);
        $sql="select id from user where email=?";
        $stmt=$this->db->prepare($sql);
        $stmt->bind_param('s',$email);
        $stmt->bind_result($userID);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        return $userID;
    }
}
