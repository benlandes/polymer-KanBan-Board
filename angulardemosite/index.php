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

		<link rel="stylesheet" type="text/css" href="index.css">
		
		<script src="index.js"></script>
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
					<select ng-options="user as (user.first_name+' '+user.last_name) for user in users | nonSelected:modalTile.assignees" class="form-control" id="modalAddAssignee" ng-change="addAssigneeChanged()" ng-model="addAssignee">
					  <option value="" selected disabled>Add Assignee...</option>
					</select>
					<div class="assigneesModal">
						<span ng-repeat="assignee in modalTile.assignees" >{{assignee.first_name}} {{assignee.last_name}} <button type="button" class="btn" ng-click="removeAssignee(assignee.id)">X</button></span>
					</div>
				 </div>
				 <div class="form-group">
					<label for="createAddIcon">Icons</label>
					<select  ng-options="icon as icon.name for icon in icons | nonSelected:modalTile.icons" class="form-control" id="createAddIcon" ng-change="addIconChanged()" ng-model="addIcon">
					  <option value="" selected disabled>Add Icon...</option>
					</select>
					<div class="iconsModal">
						<span ng-repeat="icon in modalTile.icons" >{{icon.name}} <span class="glyphicon glyphicon-{{icon.icon_name}}" aria-hidden="true"> </span><button type="button" class="btn"  ng-click="removeIcon(icon.id)">X</button></span>
					</div>
				 </div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" id="modal-done-button" class="btn btn-primary"  ng-click="doneClicked()">Create</button>
			  </div>
			</div>
		  </div>
		</div>
	</body>
</html>