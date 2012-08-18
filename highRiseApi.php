<?php

class HighRise {
	var $noDuplicates = 0;
	var $baseUrl = '';
	var $authToken = '';
	var $contactGroup = '';
	var $contactOwner = '';

    function HighRise(){
        return 1;
    }
    
    function get_contacts_list(){
		$header[] = "Content-type: application/xml";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . 'people.xml');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->authToken . ":X");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header ); //Include the head info – this is important must be application/xml
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); // return into a variable
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    /*
     * This function attaches a note to a person
     */
    function create_person_note($person_id, $comment){
        
        // Both the comment and the id are required
        if (!(isset($person_id) && isset($comment))){
            return 0;
        }
        
        
        $request = '<note><body>' . $comment . '</body>'
            . '<subject-id type="integer">' . $person_id . '</subject-id>'
            . '<subject-type>Party</subject-type>'
            . '</note>';
            
        $this->send_request('/notes.xml', $request);
        
        return 1;
    }
    
    function attach_tag($id, $tag){
        $request = '<name>' . $tag . '</name>';
        
        $form = '/people/' . $id . '/tags.xml';
        $this->send_request($form, $request);
    }
    
    function create_person($request){
    	$result = '0';
    
    	$id = ($this->noDuplicates) ? $this->_person_in_highrise($request['first'], $request['last']) : -1;
		
		
		if($id < 0){
			$request = '<person>';
	        $request .= '<first-name>' . htmlspecialchars($contact['first']) . '</first-name>';
        	$request .= '<last-name>' . htmlspecialchars($contact['last']) . '</last-name>';
			$request .= '<visible-to>NamedGroup</visible-to>';
			$request .= '<group-id>' . $this->contactGroup . '</group-id>';
			$request .= '<owner-id>' . $this->contactOwner . '</owner-id>';
        
        	if (isset($contact['title'])){
            	$request .= '<title>' . htmlspecialchars($contact['title']) . '</title>';
	        }
        
        	if (isset($contact['company'])){
            	$request .= '<company-name>' . htmlspecialchars($contact['company']) . '</company-name>';
	        }
        
        	if (isset($contact['background'])){
            	$request .= '<background>' . htmlspecialchars($contact['background']) . '</background>';
        	}
        	
        	if (isset($contact['email'])){
        		$email = '<email-addresses><email-address><address>' . htmlspecialchars($contact['email']) . '</address>'
                	    . '<location>Work</location>'
                	    . '</email-address></email-addresses>';
        	}
        	
        	if (isset($contact['phone'])){
            	$phone = '<phone-numbers><phone-number><number>' . htmlspecialchars($contact['phone']) . '</number>'
                	    . '<location>Work</location>'
                	    . '</phone-number></phone-numbers>';
	        }
	        
	        if (isset($contact['street'])){
	        	$address = '<addresses><address>'
				      . '<city>' . htmlspecialchars($contact['city']) . '</city>'
				      . '<country>' . htmlspecialchars($contact['country']) . '</country>'
				      . '<state>' . htmlspecialchars($contact['state']) . '</state>'
				      . '<street>' . htmlspecialchars($contact['street']) . '</street>'
				      . '<zip>' . htmlspecialchars($cntact['zip']) . '</zip>'
				      . '<location>' . htmlspecialchars($cntact['location']) . '</location>'
				      . '</address></addresses>';
	        }
        
        	if (isset($email) || isset($phone) || isset($address)){
            	$request .= '<contact-data>';
            	
            	if (isset($email)){
            		$request .= $email;
            	}
            	if (isset($phone)){
            		$request .= $phone;
            	}
            	if (isset($address)){
            		$request .= $address;
            	}
            
            	$request .= '</contact-data>';
     		}
			
			$request .= '</person>';
			
			// Send id as a result (0 as fail)
			$response = $this->_send_request('people.xml', 'post', $request);
			
			// Parse response here
			$result = 'id';
		}
		else {
			$this->errorMsg = "Person already in Highrise";
		}
		
		return $result;    
    }
    
    
    //function update_person(){
    //function delete_person(){
    
    //Search for a person in Highrise 
	function _person_in_highrise($first, $last){				
		$url = 'people.xml?criteria[first]=' . urlencode($first) . '&criteria[last]=' . urlencode($last);
		$response = $this->_send_request($url , 'get', '');	
		$people = simplexml_load_string($response);
		echo($people);
		$id = '-1';
		foreach ($people->person as $person ) {
			if($person != null) {
				$id = $person->id;
			}
		}
		return $id;
	}
	
	function _send_request($form, $method, $request){
        $header[] = "Content-type: application/xml";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $form);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->authToken . ":X");
        
        if ($method == 'post'){
        	curl_setopt($ch, CURLOPT_POST, TRUE); // Set cUrl to post
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //includes the xml request
        }
        else if ($method == 'put'){
       		
        }
        else {
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header ); //Include the head info – this is important must be application/xml
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
?>
