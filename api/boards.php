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
	
	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		if(isset($_GET["id"]))
		{
			print(json_encode(getBoard($_GET)));
		}
		else
		{
			print(json_encode(getBoardList()));
		}
	}
	
	function checkParams($params, $required)
	{
		global $REQUIRED_LENGTHS;
		$missing = array();
		foreach($required as $param)
		{
			if(!isset($params[$param])) array_push($missing, $param);
		}
		if(count($missing) != 0){
			setHeaderStatus(400);
			return array("error"=>"Required fields are missing: ".implode(", ", $missing));
		}
		
		foreach($params as $key=>$value)
		{
			if(isset($REQUIRED_LENGTHS->$key))
			{
				$min = $REQUIRED_LENGTHS->$key->min;
				$max = $REQUIRED_LENGTHS->$key->max;
				if(strlen($value) < $min || strlen($value) > $max)
				{
					setHeaderStatus(400);
					return array("error"=>"$key length must be between $min and $max");
				}
			}
		}
	}
	
	//Shared function to set status header
	 function setHeaderStatus($code) 
	 {
        switch ($code) 
		{
            case 200: $value = 'OK'; break;
            case 201: $value = 'Created'; break;
            case 202: $value = 'Accepted'; break;
            case 203: $value = 'Non-Authoritative Information'; break;
            case 204: $value = 'No Content'; break;
            case 400: $value = 'Bad Request'; break;
            case 401: $value = 'Unauthorized'; break;
            case 403: $value = 'Forbidden'; break;
            case 404: $value = 'Not Found'; break;
            case 405: $value = 'Method Not Allowed'; break;
            case 501: $value = 'Not Implemented'; break;
            default: return;
        }
        header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$value, true, $code);
	}
	
	function getBoardList()
	{
		//Get content from database
		$db = createDBConnection();
		$queryResult = $db->query("SELECT id, name FROM boards");
									
		//Return no content header if empty
		if($queryResult->rowCount() == 0) setHeaderStatus(204);
		
		//Return results
		$result = $queryResult->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	function getBoard ($params)
	{

		//Check required parameters
		$required = array("id", "sprint_id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		$result = array();
		
		//Check parameters
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM sprints WHERE id = ".
			$db->quote($params["sprint_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No sprint exists for that sprint_id");
		}
		
		if($db->query("SELECT COUNT(*) FROM boards WHERE id = ".
			$db->quote($params["id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No board exists for that id");
		}
		
		//Get colors from database
		$colorsResult = $db->query("SELECT id, name, color FROM colors");
		$result["colors"] = $colorsResult->fetchAll(PDO::FETCH_ASSOC);
		
		//Get Swimlanes
		$swimlanesResult = $db->query("SELECT id, name, description FROM swimlanes");
		$result["swimlanes"] = $swimlanesResult->fetchAll(PDO::FETCH_ASSOC);
		
		//Get Queues 
		$queuesResult = $db->query("SELECT id, name FROM queues ORDER BY `order` ASC");
		$result["queues"] = $queuesResult->fetchAll(PDO::FETCH_ASSOC);
		
		//Get tiles
		$tilesResult = $db->query("SELECT t.id, t.sprint_id, t.summary, t.size, ".
									"t.description, t.queue_id, t.swimlane_id ".
									"FROM tiles t ".
									"JOIN swimlanes s ON s.id = t.swimlane_id ".
									"JOIN board_swimlane_match m ON m.swimlane_id = s.id ".
									"WHERE m.board_id = ".$db->quote($params["id"])." AND ".
									"t.sprint_id = ".$db->quote($params["sprint_id"]));
		$result["tiles"] = $tilesResult->fetchAll(PDO::FETCH_ASSOC);
		
		return $result;
	}
?>