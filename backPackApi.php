<?php
/*----------------------------------------------------------------------
 * BackPack / Backpack API class
 *----------------------------------------------------------------------
 * SimpleBackpack: PHP wrapper class for Backpack API
 * 
 * Backpack API documentation:
 * http://developer.37signals.com/backpack/
 * 
 * Author: Brandon Schmidt
 * Based on the simpleHighRise.php class by Garlin Gilchrist II
 * 
 * The authentication tokens come from your backpack account. 
 * They can be found in the My Info section of your account.
 * 
 * TODO
 
 */
class SimpleBackpack {
    var $username = '';
    var $token = '';
    var $result_type = '';

    function SimpleBackpack($u, $t, $result = "raw") {
        $this->username = $u;
        $this->token = $t;
        $this->result_type = $result;
    }

    function create_request($parameters) {
        $request_payload = "";
        if ((!empty($parameters)) && (is_array($parameters))) {
            foreach($parameters as $key => $value) {
                if (!is_array($value)) {
                    $request_payload .= ("<".$key.">".$value."</".$key.">\r\n");
                }
                else {
                    $request_payload .= "<".$key.">\r\n";
                    $request_payload .= $this->create_request($value);
                    $request_payload .= "</".$key.">\r\n";
                }
            }
        }
        return $request_payload;
    }

    function curl_request($url, $verb = "", $request_body) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($verb)) {
            if (!empty($request_body)) {
                if ($verb == "PUT") {
                    $putData = tmpfile();
                    fwrite($putData, $request);
                    fseek($putData, 0);
                    curl_setopt($ch, CURLOPT_PUT, true);
                    curl_setopt($ch, CURLOPT_INFILE, $putData);
                    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($request));
                }
                else if ($verb == "POST") {
                    curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/xml"));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
                }
            }
            else {
                if ($verb == "DELETE") {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                }
                else {
                    // Simple GET
                }
            }
        }
        else {
            // No Action
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->token);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    function convertXmlObjToArr($obj, &$arr) {
        $children = $obj->children();
        foreach ($children as $elementName => $node) {
            $nextIdx = count($arr);
            $arr[$nextIdx] = array();
            $arr[$nextIdx]['@name'] = strtolower((string)$elementName);
            $arr[$nextIdx]['@attributes'] = array();
            $attributes = $node->attributes();
            foreach ($attributes as $attributeName => $attributeValue) {
                $attribName = strtolower(trim((string)$attributeName));
                $attribVal = trim((string)$attributeValue);
                $arr[$nextIdx]['@attributes'][$attribName] = $attribVal;
            }
            $text = (string)$node;
            $text = trim($text);
            if (strlen($text) > 0) {
                $arr[$nextIdx]['@text'] = $text;
            }
            $arr[$nextIdx]['@children'] = array();
            $this->convertXmlObjToArr($node, $arr[$nextIdx]['@children']);
        }
        return;
    }

    function request($path, $parameters = "", $verb = "") {
        $url = "http://".$this->username.".highrisehq.com/".$path;
        if (!empty($parameters)) {
            $request_body = $this->create_request($parameters)."";
        }
        if (empty($verb)) {
            $verb = "GET";
        }
        $result = $this->curl_request($url, $verb, $request_body);

        if ($result[0] != '<') {
            return -1;
        }

        if ($this->result_type == "simplexml") {
            $result = simplexml_load_string($result);
        }
        elseif ($this->result_type == "array") {
            $xml = simplexml_load_string($result);
            $temp = $this->convertXmlObjToArr($xml, $array);
            $result = $array;
        }

        return($result);
    }
}
?>







