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

	$endpointURL = 'http://localhost/cbapi/report?wsdl';

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
	
	if(isset($_GET['attributeName'])) {
		$attributeName = $_GET['attributeName'];
	}else {
		$attributeName = null;
	}
	
	if(isset($_GET['filter'])) {
		$filter = $_GET['filter'];
	}else {
		$filter = null;
	}
	
	if(isset($_GET['from'])) {
		$from = $_GET['from'];
	}else {
		$from = null;
	}
	
	if(isset($_GET['to'])) {
		$to = $_GET['to'];
	}else {
		$to = null;
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
	
	if(isset($_GET['nodeId'])) {
		$nodeId = $_GET['nodeId'];
	}else {
		$nodeId = null;
	}
	
	if(isset($_GET['additionalMetric3'])) {
		$additionalMetric3 = $_GET['additionalMetric3'];
	}else {
		$additionalMetric3 = null;
	}
	
	if(isset($_GET['samplingSize'])) {
		$samplingSize = $_GET['samplingSize'];
	}else {
		$samplingSize = null;
	}
	
	if(isset($_GET['resultLimit'])) {
		$resultLimit = $_GET['resultLimit'];
	}else {
		$resultLimit = 50;
	}
	
	$parameters = array( 'attributeRequest' => 
								array('projectName' => $projectName, 
									  'modelName' => $modelName,
									  'samplingSize' => $samplingSize,
									  'attributeName' => $attributeName,
									  'additionalMetric3' => $additionalMetric3,
									  'nodeId' => $nodeId,
									  'filter' => $filter,
									  'from' => $from,
									  'to' => $to));

	ini_set("soap.wsdl_cache_enabled", "1"); // Set to zero to avoid caching WSDL
	
	//echo 'username: '.$username.'<br />';
	//echo 'password: '.$password.'<br />';

	//Create the Endpoint Client so that you can call methods
	try {
		$soapClient = new SoapClient($endpointURL, array('login'=>$username, 'password'=>$password, 'trace' => true, "features" => SOAP_SINGLE_ELEMENT_ARRAYS));
	} catch (Exception $ex) {
		var_dump($ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->_name, $ex->headerfault);
	}

	//Call the Clarabridge API Endpoint with the above parameters
	try{
		$result = $soapClient->attribute($parameters);
	}catch (Exception $e){
		echo $e->getMessage();
		var_dump($result);
	}
	
	//echo "REQUEST:\n<pre>" . $soapClient->__getLastRequest() . "</pre>\n";
	
	$returnObject['status'] = $result->return->status;
	
	//Check for the status returned by CBAPI
	if($returnObject['status'] != "SUCCESS") {
		print json_encode($result);
		return;
	}
	
	//Trim out the JSON, remove extraneous parens at beginning and end
	//decode it so you can print
	$data = $result->return->data;
	$data = trim($data, '(');
	$data = rtrim($data, ')');
	$json = json_decode($data, true);
	$data = $json['data'];
	$dataSlice = array_slice($data, 0, $resultLimit);
	
	print json_encode($dataSlice);
?>