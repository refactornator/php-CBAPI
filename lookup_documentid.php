<?php
	/*
		Written by: William Lindner for Clarabridge, Inc.
		08-10-2011
		v1.0
	*/
	
	/*
	<doc:getDocument>
        <getDocumentRequest>
            <projectName>Twitter Dashboard</projectName>
            <documentId>102072</documentId>
            <attributes>true</attributes>
            <analysis>SENTIMENT_AND_CATEGORIES</analysis>
            <responseLevel>VERBATIM</responseLevel>
        </getDocumentRequest>
    </doc:getDocument>
	*/

	$endpointURL = 'http://localhost/cbapi/document?wsdl';
	
	//assign variables
	if(isset($_GET['project_name'])) {
		$projectName = $_GET['project_name'];
	}else {
		echo 'FAIL';
		return;
	}
	
	if(isset($_GET['document_id'])) {
		$documentId = $_GET['document_id'];
	}else {
		echo 'FAIL';
		return;
	}
	
	$parameters = array( 'getDocumentRequest' => 
								array('projectName' => $projectName, 
									  'documentId' => $documentId,
									  'attributes' => true,
									  'analysis' => 'SENTIMENT_AND_CATEGORIES',
									  'responseLevel' => 'VERBATIM'));

	ini_set("soap.wsdl_cache_enabled", "1"); // Set to zero to avoid caching WSDL

	$username = 'admin';
	$password = 'admin';
	
	//echo 'username: '.$username.'<br />';
	//echo 'password: '.$password.'<br />';

	try {
		$soapClient = new SoapClient($endpointURL, array('login'=>$username, 'password'=>$password, 'trace' => true, "features" => SOAP_SINGLE_ELEMENT_ARRAYS));
	} catch (Exception $ex) {
		var_dump($ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->_name, $ex->headerfault);
	}

	try{
		$result = $soapClient->getDocument($parameters);
	}catch (Exception $e){
		echo $e->getMessage();
		var_dump($result);
	}
	
	$returnObject['status'] = $result->return->status;
	
	if($returnObject['status'] != "SUCCESS") {
		return $result;
	}
	
	print json_encode($result->return);
	
	/*echo "<pre>";
	echo var_dump($result->return);
	//print json_encode($result->return);
	echo "</pre>";*/
?>