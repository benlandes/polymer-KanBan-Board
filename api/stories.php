<?php
	//Headers allow cross-domain ajax calls
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header("Access-Control-Allow-Origin: *");
	
	//MySQL Database Credentials
	$SQL_DATABASE = "kanbanboard";
	$SQL_USERNAME = "root";
	$SQL_PASSWORD = "";
	
	//Required lengths for database fields
	$REQUIRED_LENGTHS = [
		"id"=>["min"=>13, "max"=>13],
		"first_name"=>["min"=>1, "max"=>50],
		"last_name"=>["min"=>1, "max"=>50],
		"summary"=>["min"=>1, "max"=>400],
		"description"=>["min"=>1, "max"=>4000]
		];
	$STATE_TRANSFERS = [[],[],[],[],[],[]];

	//Route request depending on http method
	if($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$returnValue = createStory($_POST);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'PUT')
	{
		$_PUT = array();
		parse_str(file_get_contents('php://input'), $_PUT);
		$returnValue = editStory($_PUT);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'DELETE')
	{
		$_DELETE = array();
		parse_str(file_get_contents('php://input'), $_DELETE);
		$returnValue = deleteStory($_DELETE);
	}
	else{ //GET
		//If a specific id is set get that story otherwise get all
		if(isset($_GET["id"]))
		{
			$returnValue = getStory($_GET);
		}
		else
		{
			$returnValue = getStoryList($_GET);
		}
	}
	print(json_encode($returnValue));
	
	//Shared method that creates a connection to the MySQL database
	function createDBConnection()
	{
		global $SQL_DATABASE, $SQL_USERNAME, $SQL_PASSWORD;
		$db = new PDO("mysql:dbname=$SQL_DATABASE", "$SQL_USERNAME", "$SQL_PASSWORD");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}
	
	//Shared method that checks input parameters
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
	
	//Gets a blog entry
	function getStory($params)
	{
	
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Retrieve from database
		$db = createDBConnection();
		$queryResult = $db->query("SELECT id, sprint_id, summary, story_points, ".
									"qa_story_points, pbi_rank, description, state, ".
									"champion_id, qa_champion_id FROM stories ".
									"WHERE id = ".$db->quote($params["id"]));
		
		//Check if blog was not found
		if($queryResult->rowCount() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No stories for that id");
		}
		
		//Return result
		$result = $queryResult->fetch(PDO::FETCH_ASSOC);
		return $result;
	
	}
	
	//Gets a list of blogs
	function getStoryList($params)
	{
		//Get content from database
		$db = createDBConnection();
		$queryResult = $db->query("SELECT id, sprint_id, summary, story_points, ".
									"qa_story_points, pbi_rank, description, state, ".
									"champion_id, qa_champion_id FROM stories");
									
		//Return no content header if empty
		if($queryResult->rowCount() == 0) setHeaderStatus(204);
		
		//Return results
		$result = $queryResult->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	
	//Creates a blog entry
	function createStory($params)
	{
		//Check required parameters
		$required = array("summary", "sprint_id","story_points","qa_story_points",
							"pbi_rank","description","state","champion_id",
							"qa_champion_id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check parameters
		if($db->query("SELECT COUNT(*) FROM sprints WHERE id = ".
			$db->quote($params["sprint_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No sprint exists for that sprint_id");
		}
		if($params["champion_id"] != "" && $db->query("SELECT COUNT(*) FROM users ".
			"WHERE id = ".$db->quote($params["champion_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No user exists for champion_id");
		}
		if($params["qa_champion_id"] != "" && $db->query("SELECT COUNT(*) FROM users ".
			"WHERE id = ".$db->quote($params["qa_champion_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No user exists for qa_champion_id");
		}
		
		//Add entry to database
		$db = createDBConnection();
		$id = uniqid();
		$db->query("INSERT INTO entries (id, sprint_id, summary, story_points, ".
					"qa_story_points, pbi_rank, description, state, champion_id, ".
					"qa_champion_id) VALUES ('$id',".$db->quote($params["sprint_id"]).","
					$db->quote($params["summary"]).",".intval($params["story_points"]).",".
					intval($params["qa_story_points"]).",".intval($params["pbi_rank"]).",".
					$db->quote($params["description"]).",".intval($params["state"]).",".
					$db->quote($params["champion_id"]).",".
					$db->quote($params["qa_champion_id"]).")");
					
		//return create success
		return array("status" => "Entry Created", "id" => $id);
	}
	
	//Updates a blog entry
	function editStory($params)
	{
		/*
		//Check required parameters
		$required = array("id", "title", "body");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check if entry exists
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM entries WHERE id = ".$db->quote($params["id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No entry exists for that id");
		}
		
		//Update Entry
		$db->query("UPDATE entries SET title = ".$db->quote($params["title"]).", body = ".$db->quote($params["body"])." WHERE id = ".$db->quote($params["id"]));
		
		//return update success
		return array("status" => "Entry Updated");
		*/
	}
	
	//Deletes a blog entry
	function deleteStory($params)
	{
		/*
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check if entry exists
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM entries WHERE id = ".$db->quote($params["id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No entry exists for that id");
		}
		
		//Delete Entry
		$db->query("DELETE FROM entries WHERE id = ".$db->quote($params["id"]));
		
		//return delete success
		return array("status" => "Entry Deleted");
		*/
	}
?>
