<?php
	/*
		Written by: William Lindner for Clarabridge, Inc.
		01-14-2012
		v1.0
	*/
	
	/*
	<rep:attributeReport>
        <attributeRequest>
           <projectName>Twitter Dashboard</projectName>
           <modelName>Categorization Library</modelName>
           <samplingSize>1000000</samplingSize>
           <attributeName>USER_NAME</attributeName>
        </attributeRequest>
    </rep:attributeReport>

	*/
	/*function compareVolume( $a, $b )
	{
		if( $a[2] == $b[2] ){ return 0 ; }
		return ($a->[2] < $b->[2]) ? -1 : 1;
	}*/
	
	error_reporting(E_ALL);
	ini_set('display_errors', True);

	$endpointURL = 'http://localhost/cbapi/';

	$endpointDocumentURL .= 'document?wsdl';
	$endpointQueryIndexURL .= 'queryIndex?wsdl';
	
	//assign variables
	if(isset($_GET['projectName'])) {
		$projectName = $_GET['projectName'];
	}else {
		$projectName = null;
	}
	
	if(isset($_GET['modelName'])) {
		$modelName = $_GET['modelName'];
	}else {
		$modelName = null;
	}
	
	if(isset($_GET['query'])) {
		$query = $_GET['query'];
	}else {
		$query = null;
	}
	
	if(isset($_GET['filter'])) {
		$filter = $_GET['filter'];
	}else {
		$filter = null;
	}
	
	if(isset($_GET['username'])) {
		$username = $_GET['username'];
	}else {
		$username = 'admin';
	}
	
	if(isset($_GET['password'])) {
		$password = $_GET['password'];
	}else {
		$password = 'admin';
	}
	
	if(isset($_GET['limit'])) {
		$limit = $_GET['limit'];
	}else {
		$limit = 50;
	}
	
	$parameters = array( 'querySentenceRequest' => 
								array('projectName' => $projectName, 
									  'modelName' => $modelName,
									  'filter' => $filter,
									  'query' => $query));

	ini_set("soap.wsdl_cache_enabled", "1"); // Set to zero to avoid caching WSDL
	
	//echo 'username: '.$username.'<br />';
	//echo 'password: '.$password.'<br />';

	try {
		$soapClient = new SoapClient($endpointQueryIndexURL, array('login'=>$username, 'password'=>$password, 'trace' => true, "features" => SOAP_SINGLE_ELEMENT_ARRAYS));
	} catch (Exception $ex) {
		var_dump($ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->_name, $ex->headerfault);
	}

	try{
		$result = $soapClient->querySentence($parameters);
	}catch (Exception $e){
		echo $e->getMessage();
		var_dump($result);
	}
	
	//echo "REQUEST:\n<pre>" . $soapClient->__getLastRequest() . "</pre>\n";
	
	$returnObject['status'] = $result->return->status;
	
	if($returnObject['status'] != "SUCCESS") {
		print json_encode($result);
		return;
	}

	$documents = Array();
	$index = 0;
	foreach($result->return->sentences->sentence as $sentence) {
		if($index >= $limit) {
			break;
		}
		try {
			$soapClient = new SoapClient($endpointDocumentURL, array('login'=>$username, 'password'=>$password, 'trace' => true, "features" => SOAP_SINGLE_ELEMENT_ARRAYS));
		} catch (Exception $ex) {
			var_dump($ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->_name, $ex->headerfault);
		}

		try{
			$parameters = array( 'getDocumentRequest' => 
								array('projectName' => $projectName,
									  'documentId' => $sentence->documentId,
									  'responseLevel' => 'VERBATIM'));
			$result = $soapClient->getDocument($parameters);

			$document = Array();
			$document['attributes'] = Array();
			foreach($result->return->attributes->attribute as $attribute) {
				$document['attributes'][$attribute->name] = 
									array('value' => $attribute->_, 
										'display' => $attribute->display);
				//array_push($document['attributes'], $newAttribute);
			}
			//$document['attributes'] = $result->return->attributes;
			$document['categories'] = $result->return->models->model[0]->categories;
			$document['verbatims'] = $result->return->verbatims;
			array_push($documents, $document);

		}catch (Exception $e){
			echo $e->getMessage();
			var_dump($result);
		}
		$index++;
	}

	print json_encode($documents);
?>