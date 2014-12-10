<?php
//print("test");

?>
<!doctype html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="index.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
		
		<link rel="import" href="elements/kanban-board/kanban-board.html">
		<link rel="import" href="elements/kanban-create/kanban-create.html">
		<!--<script>
		  $(function() {
			$( ".taskList" ).sortable({
			  connectWith: ".taskList"
			}).disableSelection();
			$(".boardTitle").click(function(){
				if($(this).next(".board").is(":visible") )
				{
					$(this).next(".board").hide(400);
				}
				else
				{
					$(this).next(".board").show(400);
				}
				
			});
		  });
		</script>-->
	</head>
	<body>
		<kanban-board></kanban-board>
		<kanban-create></kanban-create>
		<!--<h1>KanBan Board Demo</h1>
		<h2 class="boardTitle">Ben Landes</h2>
		<div class="board">
			<div class="list">
				<h1>To Do</h1>

				<ol class="taskList">
					<li>
						<h2>1234 - Title</h2>
						<p>Schedule a meeting with Jim Miller to discuss concept and design</p>
					</li>
					<li>
						<h2>1234 - Title</h2>
						<p>Follow the link to design and implement the Code Project KanBan board with any modifications described by Jim</p>
					</li>
					<li>
						<h2>1234 - Title</h2>
						<p>Convert this example to use Polymer</p>
					</li>
					<li>Item 3</li>
				</ol>
			</div>
			<div class="list">
				<h1>In Progress</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
			<div class="list">
				<h1>Test</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
			<div class="list">
				<h1>Done</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
		</div>
		<h2 class="boardTitle">Aaron High</h2>
		<div class="board">
			<div class="list">
				<h1>To Do</h1>

				<ol class="taskList">
					<li>
						<h2>1234 - Title</h2>
						<p>Schedule a meeting with Jim Miller to discuss concept and design</p>
					</li>
					<li>
						<h2>1234 - Title</h2>
						<p>Follow the link to design and implement the Code Project KanBan board with any modifications described by Jim</p>
					</li>
					<li>
						<h2>1234 - Title</h2>
						<p>Convert this example to use Polymer</p>
					</li>
					<li>Item 3</li>
				</ol>
			</div>
			<div class="list">
				<h1>In Progress</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
			<div class="list">
				<h1>Test</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
			<div class="list">
				<h1>Done</h1>
				<ol class="taskList">
					<li>Item 1</li>
				</ol>
			</div>
		</div>
		-->
	</body>
</html>