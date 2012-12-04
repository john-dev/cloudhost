<?

class handleSHORTURL {
    public function newSHORTURL($longUrl) {
        $dataArray = array("longUrl" => $longUrl);
        $dataJson = json_encode($dataArray);
 
        $returnJson = $this->postData(SHORTURL_SERVICE, $dataJson);
 
        $returnArray = json_decode($returnJson);
        return $returnArray->id;
    }
    
    public function getHITS_SIMPLE($shortUrl) {
        $dataJson=array("");
        $returnJson = $this->postData("https://www.googleapis.com/urlshortener/v1/url?shortUrl=".$shortUrl."&projection=FULL", $dataJson, 0);
 
        $returnArray = json_decode($returnJson,true);

        return $returnArray['analytics']['allTime']['shortUrlClicks'];
    }
       
    private function postData($url, $data, $post=1) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}
