<?php

	//Headers allow cross-domain ajax calls
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
	
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
		else if(isset($_GET["sprint_id"]))
		{
			$returnValue = getBoard($_GET);
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
									"description, state, champion_id FROM stories ".
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
	function getBoard($params)
	{
		//Check required parameters
		$required = array("sprint_id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check parameters
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM sprints WHERE id = ".
			$db->quote($params["sprint_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No sprint exists for that sprint_id");
		}
		
		//Get states
		$statesResult = $db->query("SELECT `column`, name FROM states ORDER BY `column` ASC");
		$states = $statesResult->fetchAll(PDO::FETCH_ASSOC);
		
		//For each epic
		$db = createDBConnection();
		$epicsResult = $db->query("SELECT id, name FROM epics");
		$epics = $epicsResult->fetchAll(PDO::FETCH_ASSOC);
		foreach($epics as &$epic)
		{
			$epic["states"] = array();
		
			//For each state
			foreach($states as $state)
			{	
				$stateEntry = array();
				$stateEntry["name"] = $state["name"];
				$storiesResult = $db->query("SELECT id, sprint_id, epic_id, summary, size, ".
									"description, state, percent_done, status FROM stories ".
									"JOIN `order` on id = story_id WHERE ".
									"epic_id = ".$db->quote($epic["id"])." AND ".
									"sprint_id = ".$db->quote($params["sprint_id"])." AND ".
									"state = ".$db->quote($state["column"])." ".
									"ORDER BY `order` ASC");
				$stories = $storiesResult->fetchAll(PDO::FETCH_ASSOC);
				$stateEntry["stories"] = $stories;
				array_push($epic["states"],$stateEntry);
			}
		}
		
		return array("epics"=>$epics);
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
		$required = array("sprint_id","epic_id","state","size","summary", "description",
						"percent_done","status");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check parameters
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM epics WHERE id = ".
			$db->quote($params["epic_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No epic exists for epic_id");
		}
		
		if($db->query("SELECT COUNT(*) FROM states ".
			"WHERE `column` = ".$db->quote($params["state"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No state exists for state");
		}
		
		if($db->query("SELECT COUNT(*) FROM status ".
			"WHERE id = ".$db->quote($params["status"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for status parameter");
		}
		
		if($db->query("SELECT COUNT(*) FROM sizes ".
			"WHERE value = ".$db->quote($params["size"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for size parameter");
		}
		
		//Add entry to database
		$db = createDBConnection();
		$id = uniqid();
		
		$db->query("INSERT INTO stories (id, epic_id, summary, size, description".
					", state, percent_done, sprint_id, status) VALUES ('$id',".$db->quote($params["epic_id"]).",".
					$db->quote($params["summary"]).",".$db->quote($params["size"]).",".
					$db->quote($params["description"]).",".intval($params["state"]).",".
					$db->quote($params["percent_done"]).",".$db->quote($params["sprint_id"]).",".
					$db->quote($params["status"]).")");
		
		//Order of story will be last
		updateStoryOrder($id, -1);
		
		//return create success
		return array("status" => "Entry Created", "id" => $id);
	}
	
	//Updates order of story
	function updateStoryOrder($storyID,$order){
		//Get content from database
		$db = createDBConnection();
		
		$storyResult = $db->query("SELECT epic_id,sprint_id, state FROM stories ".
									"WHERE id = ".$db->quote($storyID));
		if($storyResult->rowCount() == 0)
		{
			throw new Exception("No story exists for that id");
		}
		$updateStory = $storyResult->fetch(PDO::FETCH_ASSOC);
		
		//Delete previous order position
		$db->query("DELETE from `order` WHERE story_id = ".$db->quote($storyID));
		
		//Query stories in same group
		$queryResult = $db->query("SELECT story_id FROM stories JOIN `order` ON id = story_id ".
									"WHERE sprint_id = ".$db->quote($updateStory["sprint_id"])." ".
									"AND epic_id = ".$db->quote($updateStory["epic_id"])." ".
									"AND state = ".$db->quote($updateStory["state"])." ".
									"ORDER BY `order` ASC");
				
		//If no order is set story will be ordered last
		$storyCount = $queryResult->rowCount();
		if($order == -1)
		{
			$order = $storyCount;
		}
		
		//Out of bounds
		if($order > $storyCount) throw new Exception("Order out of bounds");
		
		//Insert story into existing order
		$stories = $queryResult->fetchAll(PDO::FETCH_ASSOC);
		array_splice( $stories, $order, 0, array(array("story_id"=>$storyID)));
		
		//Reorder stories
		foreach($stories as $key=>$story){
			//print("Key".$key);
			//print_r($story);
			//Delete previous order row
			$db->query("DELETE from `order` WHERE story_id = ".$db->quote($story["story_id"]));
			
			//Create order row
			//print("INSERT INTO `order` (story_id,`order`) VALUES (".$db->quote($story["story_id"]).",'$key')");
			$db->query("INSERT INTO `order` (story_id,`order`) VALUES (".$db->quote($story["story_id"]).",'$key')");
		}
	}
	
	//Updates a blog entry
	function editStory($params)
	{
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check against optional fields
		$optional = array("sprint_id","epic_id","state","size","summary", "description",
				"percent_done","status");
		foreach($params as $key => $value)
		{
			if(in_array($key,$optional) === false)
			{
				throw new Exception("No story exists for that id");
			}
		}
		
		//Check if story exists
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM stories WHERE id = ".$db->quote($params["id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No story exists for that id");
		}
		
		//Update Entry
		$db->query("UPDATE entries SET title = ".$db->quote($params["title"]).", body = ".$db->quote($params["body"])." WHERE id = ".$db->quote($params["id"]));
		
		//return update success
		return array("status" => "Entry Updated");
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
