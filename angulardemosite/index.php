<html>
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
		
		<style>
			.container
			{
				width:800px;
			}
			.odd-col
			{
				background-color:#E6E6E6;
			}
			.even-col
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
			}
			.icons{
				float:right;
				width:30%;
				text-align:right;
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
		  $(function() {
			$( ".connectedSortable" ).sortable({
				connectWith: ".connectedSortable",
				appendTo: "body",
				helper: function(event,$item){
					var $helper = $('<ol></ol>');
					return $helper.append($item.clone());
				}
			}).disableSelection();
			 $('[data-toggle="tooltip"]').tooltip({
				container:"body"
			 });
			 $(".edit").click(function(){
				$('#editTileModal').modal('show')
			 });
			 $(".create").click(function(){
				$('#createTileModal').modal('show')
			 });
		  });
		</script>
	</head>
	<body>
		<div class=".container">
			<div class="row">
				<div class="col-md-2"><br/></div>
				<div class="col-md-2 odd-col">
					<h2>To Do</h2>
				</div>
				<div class="col-md-2 even-col">
					<h2>In Progress</h2>
				</div>
				<div class="col-md-2 odd-col">
					<h2>Test</h2>
				</div>
				<div class="col-md-2 even-col">
					<h2>Done</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<h1>Swim Lane 1</h1>
				</div>
				<div class="col-md-2 odd-col">
					<ol class="connectedSortable">
						<li>
							<div class="edit-container">
								<button type="button" class="btn-link edit"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</div>
							<h3>Test Story Summary</h3>
							<div class="progress">
							  <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
							  </div>
							</div>
							<div class="icons">
								<span class="glyphicon glyphicon-star" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Starred"></span>
								<span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Confusion"></span>
								<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Important"></span>
							</div>
							<div class="users">
								<span class="user" data-toggle="tooltip" data-placement="top" title="Ben Landes">B</span> 
								<span class="user" data-toggle="tooltip" data-placement="top" title="Darcy Davidson">D</span> 
								<span class="user" data-toggle="tooltip" data-placement="top" title="Aaron High">A</span> 
							</div>
							
							<div class="status"></div>
						</li>
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 even-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 odd-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 even-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<h1>Swim Lane 2</h1>
				</div>
				<div class="col-md-2 odd-col">
					<ol class="connectedSortable">
						<li>
							
							<h3>Test Story Summary</h3>
							<div class="status"></div>

						</li>
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 even-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 odd-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
				<div class="col-md-2 even-col">
					<ol class="connectedSortable">
					</ol>
					<button type="button" class="btn-link create"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add a tile...</button>
				</div>
			</div>
		<div>
		
		<!-- Edit Tile Modal -->
		<div class="modal fade" id="editTileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Tile</h4>
			  </div>
			  <div class="modal-body">
				<form>
				
				</form>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			  </div>
			</div>
		  </div>
		</div>
		
		<!-- Create Tile Modal -->
		<div class="modal fade" id="createTileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Create Tile</h4>
			  </div>
			  <div class="modal-body">
				<div class="form-group">
					<label for="createSummary">Summary</label>
					<input type="text" class="form-control" id="createSummary" placeholder="Enter Summary">
				 </div>
				<div class="form-group">
					<label for="createDescription">Description</label>
					<textarea class="form-control" rows="3" id="createDescription"></textarea>
				</div>
				<div class="form-group">
					<label for="createPercentDone">Percent Done</label>
					<input type="text" class="form-control" id="createPercentDone" placeholder="Enter Percent Done (0-100)">
				 </div>
				 <div class="form-group">
					<label for="createSize">Size</label>
					<select class="form-control" id="createSize">
					  <option>XS</option>
					  <option>S</option>
					  <option>M</option>
					  <option>L</option>
					  <option>XL</option>
					</select>
				 </div>
				 <div class="form-group">
					<label for="createSwimLane">Swim Lane</label>
					<select class="form-control" id="createSwimLane">
					  <option>Swim Lane 1</option>
					  <option>Swim Lane 2</option>
					</select>
				 </div>
				 <div class="form-group">
					<label for="createSprint">Sprint</label>
					<select class="form-control" id="createSprint">
					  <option>Sprint 1</option>
					  <option>Sprint 2</option>
					</select>
				 </div>
				 <div class="form-group">
					<label for="createQueue">Queue</label>
					<select class="form-control" id="createQueue">
					  <option>To Do</option>
					  <option>In Progress</option>
					  <option>Test</option>
					  <option>Done</option>
					</select>
				 </div>
				 <div class="form-group">
					<label for="createStatus">Status</label>
					<select class="form-control" id="createStatus">
					  <option>Defect</option>
					  <option>Important</option>
					  <option>Unplanned</option>
					</select>
				 </div>
				 <div class="form-group">
					<label for="createAddAssignee">Asignees</label>
					<select class="form-control" id="createAddAssignee" placeholder="Add Assignee...">
					  <option value="" selected disabled>Add Assignee...</option>
					  <option>Jim Miller</option>
					  <option>Kevin Richardson</option>
					  <option>Amy Arbeit</option>
					</select>
					<div class="assigneesModal">Ben Landes <button type="button" class="btn">X</button> Darcy Davidson <button type="button" class="btn">X</button> </div>
				 </div>
				 <div class="form-group">
					<label for="createAddIcon">Icons</label>
					<select class="form-control" id="createAddIcon" placeholder="Add Icon...">
					  <option value="" selected disabled>Add Icon...</option>
					  <option>Starred</option>
					  <option>Confusion</span></option>
					</select>
					<div class="assigneesModal">
						Starred <span class="glyphicon glyphicon-star" aria-hidden="true"> </span><button type="button" class="btn">X</button> 
						Confusion <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> <button type="button" class="btn">X</button> 
						Important <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> <button type="button" class="btn">X</button> 
					</div>
				 </div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Create</button>
			  </div>
			</div>
		  </div>
		</div>
	</body>
</html>