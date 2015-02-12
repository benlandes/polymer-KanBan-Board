<?php

	//Headers allow cross-domain ajax calls
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: content-type");
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

	//Route request depending on http method
	if($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$returnValue = createTile($_POST);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'PUT')
	{
		$_PUT = array();
		parse_str(file_get_contents('php://input'), $_PUT);
		$returnValue = editTile($_PUT);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'DELETE')
	{
		$_DELETE = array();
		parse_str(file_get_contents('php://input'), $_DELETE);
		$returnValue = deleteTile($_DELETE);
	}
	else if($_SERVER['REQUEST_METHOD'] === 'GET'){ //GET
		//If a specific id is set get that tile otherwise get all
		if(isset($_GET["id"]))
		{
			$returnValue = getTile($_GET);
		}
		else if(isset($_GET["sprint_id"]))
		{
			$returnValue = getBoard($_GET);
		}
		else
		{
			$returnValue = getTileList($_GET);
		}
	}
	if(isset($returnValue))
	{
		print(json_encode($returnValue));
	}
	
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
	
	//Gets a tile entry
	function getTile($params)
	{
	
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Retrieve from database
		$db = createDBConnection();
		$queryResult = $db->query("SELECT id, sprint_id, summary, size, percent_done, color_id, ".
									"description, queue_id, swimlane_id FROM tiles ".
									"WHERE id = ".$db->quote($params["id"]));
		
		//Check if blog was not found
		if($queryResult->rowCount() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No tiles for that id");
		}
		
		$result = $queryResult->fetch(PDO::FETCH_ASSOC);
		
		//Get Assignees
		$assigneeResult = $db->query("SELECT u.id, u.first_name, u.last_name FROM users u ".
									"JOIN tile_user_match m ON m.user_id = u.id ".
									"WHERE m.tile_id = ".$db->quote($params["id"]));
		$result["assignees"] = $assigneeResult->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	
	}
	
	
	//Creates a tile 
	function createTile($params)
	{
		
		//Check required parameters
		$required = array("sprint_id","swimlane_id","queue_id","size","summary", "description",
						"percent_done","color_id","assignees");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check parameters
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM swimlanes WHERE id = ".
			$db->quote($params["swimlane_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No swimlane exists for swimlane_id");
		}
		
		if($db->query("SELECT COUNT(*) FROM queues ".
			"WHERE id = ".$db->quote($params["queue_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No queue exists for queue_id");
		}
		
		if($db->query("SELECT COUNT(*) FROM colors ".
			"WHERE id = ".$db->quote($params["color_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for color_id parameter");
		}
		
		if($db->query("SELECT COUNT(*) FROM sizes ".
			"WHERE value = ".$db->quote($params["size"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for size parameter");
		}
		
		$assignees = array_unique(explode(",",preg_replace('/\s+/', '', $params["assignees"])));
		foreach($assignees as $index => $assignee)
		{
			if($assignee != "")
			{
				if($db->query("SELECT COUNT(*) FROM users ".
				"WHERE id = ".$db->quote($assignee))->fetchColumn() == 0)
				{
					setHeaderStatus(400);
					return array("error"=>"No user with id '$assignee'");
				}
			}
			else
			{
				unset($assignees[$index]);
			}
		}
		
		$icons = array_unique(explode(",",preg_replace('/\s+/', '', $params["icons"])));
		foreach($icons as $index => $icon)
		{
			if($icon != "")
			{
				if($db->query("SELECT COUNT(*) FROM icons ".
				"WHERE id = ".$db->quote($icon))->fetchColumn() == 0)
				{
					setHeaderStatus(400);
					return array("error"=>"No icon with id '$icons'");
				}
			}
			else
			{
				unset($icons[$index]);
			}
		}
		
		//Add entry to database
		$db = createDBConnection();
		$id = uniqid();
		
		$db->query("INSERT INTO tiles (id, swimlane_id, summary, size, description".
					", queue_id, percent_done, sprint_id, color_id) VALUES ('$id',".$db->quote($params["swimlane_id"]).",".
					$db->quote($params["summary"]).",".$db->quote($params["size"]).",".
					$db->quote($params["description"]).",".$db->quote($params["queue_id"]).",".
					$db->quote($params["percent_done"]).",".$db->quote($params["sprint_id"]).",".
					$db->quote($params["color_id"]).")");
		
		//Order of tile will be last
		updateTileOrder($id, -1);
		
		//Add assignees
		foreach($assignees as $assignee)
		{
			$db->query("INSERT INTO tile_user_match (tile_id, user_id) VALUES ('$id',".$db->quote($assignee).")");
		}
		
		//Add icons
		foreach($icons as $icon)
		{
			$db->query("INSERT INTO tile_icon_match (tile_id, icon_id) VALUES ('$id',".$db->quote($icon).")");
		}
		
		//return create success
		return array("status" => "Entry Created", "id" => $id);
	}
	
	//Updates order of tile
	function updateTileOrder($tileID,$order){
		//Get content from database
		$db = createDBConnection();
		
		$tileResult = $db->query("SELECT swimlane_id,sprint_id, queue_id FROM tiles ".
									"WHERE id = ".$db->quote($tileID));
		if($tileResult->rowCount() == 0)
		{
			throw new Exception("No tile exists for that id");
		}
		$updateTile = $tileResult->fetch(PDO::FETCH_ASSOC);
		
		//Delete previous order position
		$db->query("DELETE from `order` WHERE tile_id = ".$db->quote($tileID));
		
		//Query tiles in same group
		$queryResult = $db->query("SELECT tile_id FROM tiles JOIN `order` ON id = tile_id ".
									"WHERE sprint_id = ".$db->quote($updateTile["sprint_id"])." ".
									"AND swimlane_id = ".$db->quote($updateTile["swimlane_id"])." ".
									"AND queue_id = ".$db->quote($updateTile["queue_id"])." ".
									"ORDER BY `order` ASC");
				
		//If no order is set tile will be ordered last
		$tileCount = $queryResult->rowCount();
		if($order == -1)
		{
			$order = $tileCount;
		}
		
		//Out of bounds
		if($order > $tileCount) throw new Exception("Order out of bounds");
		
		//Insert tile into existing order
		$tiles = $queryResult->fetchAll(PDO::FETCH_ASSOC);
		array_splice( $tiles, $order, 0, array(array("tile_id"=>$tileID)));
		
		//Reorder tiles
		foreach($tiles as $key=>$tile){
			//Delete previous order row
			$db->query("DELETE from `order` WHERE tile_id = ".$db->quote($tile["tile_id"]));
			
			//Create order row
			$db->query("INSERT INTO `order` (tile_id,`order`) VALUES (".$db->quote($tile["tile_id"]).",'$key')");
		}
	}
	
	//Updates a tile
	function editTile($params)
	{
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		$id = $params["id"];
		unset($params["id"]);
		
		//Check against optional fields
		$optional = array("sprint_id","swimlane_id","queue_id","size","summary", "description",
				"percent_done","color_id","assignees", "icons","order");
		foreach($params as $key => $value)
		{
			if(in_array($key,$optional) === false)
			{
				setHeaderStatus(400);
				throw new Exception("Passed field '$key' not supported");
			}
		}
		
		//Check parameters that are set
		$db = createDBConnection();
		if(isset($params["swimlane_id"]) && $db->query("SELECT COUNT(*) FROM swimlanes WHERE id = ".
			$db->quote($params["swimlane_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No swimlane exists for swimlane_id");
		}
		if(isset($params["queue_id"]) && $db->query("SELECT COUNT(*) FROM queues ".
			"WHERE id = ".$db->quote($params["queue_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"No queue exists for queue_id");
		}
		if(isset($params["color_id"]) && $db->query("SELECT COUNT(*) FROM colors ".
			"WHERE id = ".$db->quote($params["color_id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for color_id parameter");
		}
		if(isset($params["size"]) && $db->query("SELECT COUNT(*) FROM sizes ".
			"WHERE value = ".$db->quote($params["size"]))->fetchColumn() == 0)
		{
			setHeaderStatus(400);
			return array("error"=>"Incorrect value for size parameter");
		}
		
		if(isset($params["assignees"]))
		{
			$assignees = array_unique(explode(",",preg_replace('/\s+/', '', $params["assignees"])));
			unset($params["assignees"]);
			foreach($assignees as $index => $assignee)
			{
				if($assignee != "")
				{
					if($db->query("SELECT COUNT(*) FROM users ".
					"WHERE id = ".$db->quote($assignee))->fetchColumn() == 0)
					{
						setHeaderStatus(400);
						return array("error"=>"No user with id '$assignee'");
					}
				}
				else
				{
					unset($assignees[$index]);
				}
			}
		}
		
		if(isset($params["icons"]))
		{
			$icons = array_unique(explode(",",preg_replace('/\s+/', '', $params["icons"])));
			unset($params["icons"]);
			foreach($icons as $index => $icon)
			{
				if($icon != "")
				{
					if($db->query("SELECT COUNT(*) FROM icons ".
					"WHERE id = ".$db->quote($icon))->fetchColumn() == 0)
					{
						setHeaderStatus(400);
						return array("error"=>"No icon with id '$icons'");
					}
				}
				else
				{
					unset($icons[$index]);
				}
			}
		}
		if(isset($params["order"]))
		{
			$order = $params["order"];
			unset($params["order"]);
		}
		
		
		//Check if tile exists
		$oldTileResult = $db->query("SELECT swimlane_id, queue_id FROM tiles WHERE id = ".$db->quote($id));
		if($oldTileResult->rowCount() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No tile exists for that id");
		}
		$oldTile = $oldTileResult->fetch(PDO::FETCH_ASSOC);
		
		//Update Entry
		foreach($params as $key=>$value)
		{
			$db->query("UPDATE tiles SET $key = ".$db->quote($value)." WHERE id = ".$db->quote($id));
		}
		
		//Update assignees
		if(isset($assignees))
		{
			$db->query("DELETE FROM tile_user_match WHERE tile_id = ".$db->quote($id));
			foreach($assignees as $assignee)
			{
				$db->query("INSERT INTO tile_user_match (tile_id, user_id) VALUES ('$id',".$db->quote($assignee).")");
			}
		}
		
		//Update icons
		if(isset($icons))
		{
			$db->query("DELETE FROM tile_icon_match WHERE tile_id = ".$db->quote($id));
			foreach($icons as $icon)
			{
				$db->query("INSERT INTO tile_user_match (tile_id, user_id) VALUES ('$id',".$db->quote($icon).")");
			}
		}
		
		//Update tile order
		if(isset($order))
		{
			updateTileOrder($id,intval($order));
		}else if((isset($params["swimlane_id"]) && $oldTile["swimlane_id"] !=  $params["swimlane_id"] ) || 
				(isset($params["queue_id"]) && $oldTile["queue_id"] !=  $params["queue_id"] )){
			updateTileOrder($id,-1);
		}
		
		//return update success
		return array("status" => "Entry Updated");
	}
	
	//Deletes a Tile
	function deleteTile($params)
	{
		
		//Check required parameters
		$required = array("id");
		$checkResult = checkParams($params, $required);
		if(isset($checkResult["error"])) return $checkResult;
		
		//Check if entry exists
		$db = createDBConnection();
		if($db->query("SELECT COUNT(*) FROM tiles WHERE id = ".$db->quote($params["id"]))->fetchColumn() == 0)
		{
			setHeaderStatus(404);
			return array("error"=>"No entry exists for that id");
		}
		
		//Delete Entry
		$db->query("DELETE FROM tiles WHERE id = ".$db->quote($params["id"]));
		
		//return delete success
		return array("status" => "Tile Deleted");
	}
?>
