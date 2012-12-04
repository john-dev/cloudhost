<?

class handleSHORTURL {
    public function newSHORTURL($longUrl) {
        $dataArray = array("longUrl" => $longUrl);
        $dataJson = json_encode($dataArray);
 
        $returnJson = $this->postData(SHORTURL_SERVICE, $dataJson);
 
        $returnArray = json_decode($returnJson);
        return $returnArray->id;
    }
    
    private function postData($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}
