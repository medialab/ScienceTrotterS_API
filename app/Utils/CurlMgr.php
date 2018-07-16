<?php

namespace App\Utils;

/**
 * Gestionnaire Curl
 */
class CurlMgrUtil {
    private $c;             // var Curl
    private $res;           // Request Result
    private $err;           // Error Msg
    private $errCode;       // Error Code
    private $responseCode = 0;   // Http Response Code

    public $headers = [];   // Headers to Apply

    // Authorized Methods
    private $methods = [
        "GET",
        "HEAD",
        "POST",
        "PUT",
        "DELETE",
        "CONNECT",
        "OPTIONS",
        "TRACE",
        "PATCH"
    ];

    /**
     * Construct
     * @param (String|boolean)     $url  Url
     * @param Array $data Request Data
     */
    function __construct($url=false, Array $data=[]){
        $this->c = curl_init('');
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->c, CURLINFO_HEADER_OUT, true);
        
        if ($url) {
            $this->setUrl($url, $data);
        }
    }

    /**
     * Add Header to Apply
     * @param (String|Array) $heads ex: 'Content-Type: text/html' || ['Content-Type' => 'text/html']
     */
    public function setHeader($heads){
        if (is_string($heads) && strpos($heads, ': ')) {
            $h = explode(': ', $heads);

            $heads = [];
            $heads[$h[0]] = $h[1];
        }

        if (is_array($heads)) {
            if (!$this->headers) {
                $this->headers = [];
            }

            $this->headers = array_merge($this->headers, $heads);
        }
        elseif($heads){
            $this->headers[] = $heads;
        }
        else{
            $this->headers = false;
        }

        return $this;
    }

    /**
     * Set Request Url
     * @param String      $url Request Url
     * @param Array $data Request Data
     */
    public function setUrl($url, Array $data=[]){
        if (count($data)) {
            $d = http_build_query($data);
            
            if (!strpos($url, '?')) {
                $sep = '?';
            }
            elseif(strpos($url, '&') || substr($url, -1) != '?'){
                $sep = '&';
            }
            else{
                $sep = '';
            }

            $url .= $sep.$d;
        }

        curl_setopt($this->c, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Set Request data
     * @param (String|Array)  $data       Request Data
     * @param boolean $asJson     Data to Json
     * @param boolean $addHeaders add Content-Type
     */
    public function setData($data, $asJson=true, $addHeaders=true){
        if(is_array($data)){
            if ($asJson) {
                $data = json_encode($data);
                $this->setHeader("Content-Type: application/json");
            }
            else{
                $data = http_build_query($data);
                
                $this->setHeader("Content-Type: application/x-www-form-urlencoded");
            }
        }

        $this->setHeader("Content-Length: ".strlen($data));
        curl_setopt($this->c, CURLOPT_POSTFIELDS, $data);

        return $this;
    }

    /**
     * Reset Curl
     * @param  string $url New Request Url
     * @return CurlMgr      $this
     */
    public function reset($url=""){
        curl_reset($this->c);
        curl_close($this->c);

        $this->c = curl_init($url);
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->c, CURLINFO_HEADER_OUT, true);

        $this->err = "";
        $this->res = NULL;
        $this->errCode = 0;
        $this->headers = [];
        $this->responseCode = 0;

        return $this;
    }

    /**
     * Athentication
     * @param String $user Athentication User
     */
    public function setUser($user){
        curl_setopt($this->c, CURLOPT_USERPWD, $user);
        return $this;
    }

    /**
     * Apply Header To Request
     * @return [type] [description]
     */
    private function applyHeaders(){
        $heads = [];
        
        foreach ($this->headers as $k => $v) {
            if (is_string($k)) {
                $heads[] = "{$k}: {$v}";
            }
            else{
                $heads[] = $v;
            }
        }

        $t = curl_setopt($this->c, CURLOPT_HTTPHEADER, $heads);
    }

    /**
     * Execute Request
     * @return String Response
     */
    public function exec(){
        $this->applyHeaders();
        $this->res = curl_exec($this->c);
        $this->err = curl_error($this->c);
        $this->errCode = curl_errno($this->c);
        $this->responseCode = $this->getInfos()['http_code'];

        return $this->res;
    }

    /**
     * Get Http Response Code
     * @return String Response
     */
    public function getHttpCode(){
        return $this->responseCode;
    }

    /**
     * Return Curl Error
     * @return Array ['code' => $code, 'err' => $msg]
     */
    public function getError(){
        return ["code" => $this->errCode, "err" => $this->err];
    }

    /**
     * Get Curl Infos
     * @param  integer $opt Option (0 for All)
     * @return Array|OptionType       Option(s) Value(s)
     */
    public function getInfos($opt=0){
        if ($opt) {
            return curl_getinfo($this->c, $opt);
        }

        return curl_getinfo($this->c);
    }

    /**
     * Set Request to POST METHOD
     * @param  boolean $p [description]
     * @return boolean    [description]
     */
    public function isPost($p=true){
        curl_setopt($this->c, CURLOPT_POST, $p);
        return $this;
    }

    /**
     * Enable Verify SSL
     * @param  boolean $s [description]
     * @return [type]     [description]
     */
    public function verifySSL($s=true){
        curl_setopt($this->c, CURLOPT_SSL_VERIFYHOST, $s);
        curl_setopt($this->c, CURLOPT_SSL_VERIFYPEER, $s);
        return $this;
    }

    /**
     * Enable Verbose Get Info
     * @param  boolean $v [description]
     * @return [type]     [description]
     */
    public function verbose($v=true){
        curl_setopt($this->c, CURLOPT_VERBOSE, $v);
        return $this;
    }

    // Enable Response Header Parsing
    public function responseHeader($v=true){
        curl_setopt($this->c, CURLOPT_HEADER, $v);
        return $this;
    }

    /**
     * Set Request Method
     * @param [type] $m [description]
     */
    public function setMethod($m){
        $m = strtoupper($m);
        if (!in_array($m, $this->methods)) {
            throw new Exception("Error: HTTP Method '{$m}' Does not exist", 1);
        }

        curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, $m);
        return $this;
    }

    /**
     * SET Curl Option
     * @param [type] $opt  [description]
     * @param [type] $data [description]
     */
    public function setOpt($opt, $data){
        curl_setopt($this->c, $opt, $data);
        return $this;
    }

    /**
     * Close Curl
     * @return [type] [description]
     */
    public function close(){
        curl_close($this->c);
    }

    /**
     * Set Curl TimeO-ut
     * @param [type] $sec [description]
     */
    public function setTimeout($sec) {
        curl_setopt($this->c, CURLOPT_TIMEOUT, $sec);
    }
}

?>