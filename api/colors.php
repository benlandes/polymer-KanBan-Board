<?php
	//Headers allow cross-domain ajax calls
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
	
	//MySQL Database Credentials
	$SQL_DATABASE = "kanbanboard";
	$SQL_USERNAME = "root";
	$SQL_PASSWORD = "";
	
	//Shared method that creates a connection to the MySQL database
	function createDBConnection()
	{
		global $SQL_DATABASE, $SQL_USERNAME, $SQL_PASSWORD;
		$db = new PDO("mysql:dbname=$SQL_DATABASE", "$SQL_USERNAME", "$SQL_PASSWORD");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}
	
	print(json_encode(getColorsList()));
	
	function getColorsList()
	{
		//Get content from database
		$db = createDBConnection();
		$queryResult = $db->query("SELECT id, name, color FROM colors");
									
		//Return no content header if empty
		if($queryResult->rowCount() == 0) setHeaderStatus(204);
		
		//Return results
		$result = $queryResult->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
?>