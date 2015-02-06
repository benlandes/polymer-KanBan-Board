<!DOCTYPE html>
<html ng-app="boardApp">
	<head>
		<!--jquery and jquery UI -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
		
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		
		<!-- Angular -->
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.11/angular.min.js"></script>
		
		<!-- ng sortable -->
		<script type="text/javascript" src="bower_components/ng-sortable/dist/ng-sortable.min.js"></script>
		<link rel="stylesheet" type="text/css" href="bower_components/ng-sortable/dist/ng-sortable.min.css">

		<!-- OPTIONAL: default style -->
		<link rel="stylesheet" type="text/css" href="bower_components/ng-sortable/dist/ng-sortable.style.min.css">

		<style>
			.container
			{
				width:800px;
			}
			.col-1
			{
				background-color:#E6E6E6;
			}
			.col-0
			{
				background-color:#F3F3F3;
			}
			.row{
				overflow: hidden; 
			}

			[class*="col-"]{
				margin-bottom: -99999px;
				padding-bottom: 99999px;
			}
			.status
			{
				position:absolute;
				top:0px;
				left:0px;
				background-color:green;
				width:10px;
				height:1000px;
			}
			ol
			{
				margin-top:10px;
				list-style-type:none;
				padding-left:0px;
				min-height: 40px;
			}
			li
			{
				position:relative;
				padding-left:10px;
				margin-bottom:10px;
				background-color:white;
				overflow:hidden;
			}
			.progress-bar-info{
				background-color:#CCCCCC;
				background-image: linear-gradient(#CCCCCC 0px, #B8B8B8 100%);
			}
			.progress{
				margin-bottom:5px;
			}
			.progress, h3, .users, .icons{
				margin-left:5px;
				margin-right:5px;
			}
			.users{
				padding-right:30%;
			}
			.users, .icons{
				margin-bottom:5px;
			}
			.icons{
				font-size:16px;
				color:#818181;
			}
			.user{
				background-color:#818181;
				display:inline-block;
				text-align: center;
				width:20px;
				heigh:20px;
				color:white;
				margin-right:5px;
			}
			.icons{
				float:right;
				width:30%;
				text-align:right;
				margin-left:5px;
			}
			.btn-link{
				color:#818181;
			}
			h1{
				margin-top:0px;
				text-align:right;
			}
			h3{
				margin-top:25px;
			}
			.edit-container{
				float:right;
			}
			.assigneesModal, .iconsModal{
				margin-top:5px;
				margin-bottom:5px;
			}
		</style>
		
		<script>
		
			//Angular js
			var boardApp = angular.module('boardApp', ['ui.sortable']);
			boardApp.filter('firstLetter', function() {
			  return function(input) {
				return input.charAt(0);
			  };
			})
			
			//Returns array of items in input array that are not in selectedArray
			boardApp.filter('nonSelected', function() {
			  return function(input, selectedArray) {
				
				if (typeof input != 'undefined' && typeof selectedArray != 'undefined' )
				{
					input = input.slice();
					for(var i = input.length - 1; i >= 0 ; i--)
					{
						for(var j = selectedArray.length - 1; j >= 0 ; j--)
						{
							if(input[i].id == selectedArray[j].id){
								input.splice(i, 1);
							}
						}
					}
				}
				return input;
			  };
			})
			boardApp.controller('boardController', function ($scope, $http) {

				$http.get("http://api.kanbanboard.local/boards.php?sprint_id=a&id=a")
				.success(function(response) {
				
					//Put response data in format that makes data binding convenient 
					var swimlanes = response.swimlanes;
					for(var s = 0; s < swimlanes.length; s++)
					{
						swimlanes[s].queues = $.extend(true, [], response.queues);
						for(var q = 0; q < swimlanes[s].queues.length; q++)
						{
							swimlanes[s].queues[q]["swimlane_id"] = swimlanes[s].id;
							swimlanes[s].queues[q]["tiles"] = [];
							for(var t = 0; t < response.tiles.length; t++)
							{
								if(response.tiles[t]["swimlane_id"] == swimlanes[s]["id"] &&
									response.tiles[t]["queue_id"] == swimlanes[s].queues[q]["id"] )
								{
									var tiles = swimlanes[s].queues[q]["tiles"];
									tiles[tiles.length] = $.extend(true, [], response.tiles[t]);
								}
							}
						}
					}
					$scope.queues = response.queues;
					$scope.colors = response.colors;
					$scope.swimlanes = swimlanes;
				});
				

				
				$http.get("http://api.kanbanboard.local/icons.php")
				.success(function(response) {
					$scope.icons = response;
				});
				
				$http.get("http://api.kanbanboard.local/users.php")
				.success(function(response) {
					$scope.users = response;
				});
				
				$http.get("http://api.kanbanboard.local/sizes.php")
				.success(function(response) {
					$scope.sizes = response;
				});
				
				$http.get("http://api.kanbanboard.local/sprints.php")
				.success(function(response) {
					$scope.sprints = response;
				});
				
				
				$scope.dragControlListeners = {
					accept: function (sourceItemHandleScope, destSortableScope) {return true},
					itemMoved: function (event) {
						console.log("Item Moved");
						var tile = event.source.itemScope.tile;
						tile.order = event.dest.index;
						tile.queue_id = event.dest.sortableScope["$parent"].queue.id;
						tile.swimlane_id = event.dest.sortableScope["$parent"].queue.swimlane_id;
						console.log(tile);
						$http({
							method:"put",
							url:"http://api.kanbanboard.local/tiles.php",
							headers: {'Content-Type': 'application/x-www-form-urlencoded'},
							transformRequest: function(obj) {
								var str = [];
								for(var p in obj)
								str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
								return str.join("&");
							},
							data:{
								id:tile.id,
								swimlane_id:tile.swimlane_id,
								queue_id:tile.queue_id,
								order:tile.order
							}
						});
					},
					orderChanged: function(event) {
						console.log("Order Changed");
						console.log(event);
					},
				};
				$scope.editTile = function(tile) {
					$scope.modalTile = $.extend(true, [], tile);
					$("#tile-modal-title").html("Edit Tile");
					$("#modal-done-button").html("Save Changes");
					$('#tileModal').modal('show');
				}
				$scope.createTile = function(){
					$scope.modalTile = {'id': '','sprint_id':'','summary':'','size':'','percent_done':'','color_id':'',
										'description':'','queue_id':'','swimlane_id':'','assignees':[],'icons':[]};
					$("#tile-modal-title").html("Create Tile");
					$("#modal-done-button").html("Create");
					$('#tileModal').modal('show');
				}
				$scope.addAssigneeChanged = function(){
					$scope.modalTile.assignees.push($scope.addAssignee);
				}
			});
			
			//jquery ready
			$(function() {
				 $('[data-toggle="tooltip"]').tooltip({
					container:"body"
				 });
			});
		  
		  
		</script>
	</head>
	<body ng-controller="boardController">
		<div class=".container">
			<div class="row">
				<div class="col-md-2"><br/></div>
				<div  ng-repeat="queue in queues" class="col-md-2 col-{{$index%2}}">
					<h2>{{queue.name}}</h2>
				</div>
			</div>
			<div ng-repeat="swimLane in swimlanes" class="row">
				<div class="col-md-2">
					<h1>{{swimLane.name}}</h1>
				</div>
				<div  ng-repeat="queue in swimLane.queues" class="col-md-2 col-{{$index%2}}">
					<ol data-as-sortable="dragControlListeners" class="connectedSortable" data-ng-model="queue.tiles">
						<li data-ng-repeat="tile in queue.tiles"  data-as-sortable-item >
							<div data-as-sortable-item-handle>
								<div class="edit-container">
								<button type="button" ng-click="editTile(tile)" class="btn-link edit"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
								</div>
								<h3>{{tile.summary}}</h3>
								<div class="progress">
								  <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{tile.percent_done}}" aria-valuemin="0" aria-valuemax="100" style="width: {{tile.percent_done}}%">
								  </div>
								</div>
								<div class="icons">
									<span ng-repeat="icon in tile.icons" class="glyphicon glyphicon-{{icon.icon_name}}" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="{{icon.name}}"></span>
								</div>
								<div class="users">
									<span ng-repeat="assignee in tile.assignees" class="user" data-toggle="tooltip" data-placement="top" title="{{assignee.first_name}} {{assignee.last_name}}">{{assignee.first_name | firstLetter}}</span> 
								</div>
								
								<div class="status"></div>
							</div>
							
						</li>
					</ol>
					<button type="button" class="btn-link create" ng-click="createTile()" ><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
			</div>
		<div>
		
		<!-- Tile Modal -->
		<div class="modal fade" id="tileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 id="tile-modal-title" class="modal-title">Create Tile</h4>
			  </div>
			  <div class="modal-body">
				<div class="form-group">
					<label for="modalSummary">Summary</label>
					<input type="text" class="form-control" id="modalSummary" placeholder="Enter Summary" ng-model="modalTile.summary">
				 </div>
				<div class="form-group">
					<label for="modalDescription">Description</label>
					<textarea class="form-control" rows="3" id="modalDescription" ng-model="modalTile.description"></textarea>
				</div>
				<div class="form-group">
					<label for="modalPercentDone">Percent Done</label>
					<input type="text" class="form-control" id="modalPercentDone" placeholder="Enter Percent Done (0-100)" ng-model="modalTile.percent_done">
				 </div>
				 <div class="form-group">
					<label for="modalSize">Size</label>
					<select ng-options="size.value as size.value for size in sizes" class="form-control" id="modalSize" ng-model="modalTile.size"></select>
				 </div>
				 <div class="form-group">
					<label for="modalSwimLane">Swim Lane</label>
					<select ng-options="swimlane.id as swimlane.name for swimlane in swimlanes" class="form-control" id="modalSwimLane"  ng-model="modalTile.swimlane_id"></select>
				 </div>
				 <div class="form-group">
					<label for="modalSprint">Sprint</label>
					<select ng-options="sprint.id as sprint.name for sprint in sprints" class="form-control" id="modalSprint"  ng-model="modalTile.sprint_id"></select>
				 </div>
				 <div class="form-group">
					<label for="modalQueue">Queue</label>
					<select ng-options="queue.id as queue.name for queue in queues" class="form-control" id="modalQueue"  ng-model="modalTile.queue_id"></select>
				 </div>
				 <div class="form-group">
					<label for="modalColor">Color</label>
					<select ng-options="color.id as color.name for color in colors" class="form-control" id="modalColor"  ng-model="modalTile.color_id"></select>
				 </div>
				 <div class="form-group">
					<label for="modalAddAssignee">Asignees</label>
					<select class="form-control" id="modalAddAssignee" placeholder="Add Assignee..." ng-change="addAssigneeChanged()" ng-model="addAssignee">
					  <option value="" selected disabled>Add Assignee...</option>
					  <option ng-value="user" ng-repeat="user in users | nonSelected:modalTile.assignees" >{{user.first_name}} {{user.last_name}}</option>
					</select>
					<div class="assigneesModal">
						<span ng-repeat="assignee in modalTile.assignees" >{{assignee.first_name}} {{assignee.last_name}} <button type="button" class="btn">X</button></span>
					</div>
				 </div>
				 <div class="form-group">
					<label for="createAddIcon">Icons</label>
					<select class="form-control" id="createAddIcon" placeholder="Add Icon..." ng-change="addIconChanged()" ng-model="addIcon">
					  <option value="" selected disabled>Add Icon...</option>
					  <option ng-value="icon" ng-repeat="icon in icons | nonSelected:modalTile.icons" >{{icon.name}}</option>
					</select>
					<div class="assigneesModal">
						<span ng-repeat="icon in modalTile.icons" >{{icon.name}} <span class="glyphicon glyphicon-{{icon.icon_name}}" aria-hidden="true"> </span><button type="button" class="btn">X</button></span>
					</div>
				 </div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" id="modal-done-button" class="btn btn-primary">Create</button>
			  </div>
			</div>
		  </div>
		</div>
	</body>
</html>