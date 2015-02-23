var boardApp = angular.module('kanbanTest', ['ui.sortable','ui.bootstrap']);
boardApp.directive('kanbanBoard', function() {
  return {
    restrict: 'AE',
    templateUrl: 'elements/kanban-board.html',
	scope: {
      src: '@'
    },
	controller: ['$scope', '$http', function($scope, $http) {
		$scope.requestBoard = function(){
			$http.get($scope.src+"/boards?sprint_id=a&id=a")
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
								response.tiles[t].collapsed = true;
								for(var c = 0; c < response.colors.length; c++)
								{
									if(response.colors[c].id == response.tiles[t].color_id){
										response.tiles[t].color = response.colors[c];
									}
								}
								tiles[tiles.length] = $.extend(true, [], response.tiles[t]);
							}
							
							
						}
					}
				}
				$scope.queues = response.queues;
				$scope.colors = response.colors;
				$scope.swimlanes = swimlanes;
			});
		};
		$scope.requestBoard();
		

		
		$http.get($scope.src+"/icons")
		.success(function(response) {
			$scope.icons = response;
		});
		
		$http.get($scope.src+"/users")
		.success(function(response) {
			$scope.users = response;
		});
		
		$http.get($scope.src+"/sizes")
		.success(function(response) {
			$scope.sizes = response;
		});
		
		$http.get($scope.src+"/sprints")
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
					url:$scope.src+"/tiles",
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
		$scope.removeAssignee = function(assigneeID){
			for(var i = $scope.modalTile.assignees.length - 1; i >= 0 ; i--)
			{
				if($scope.modalTile.assignees[i].id == assigneeID){
					$scope.modalTile.assignees.splice(i, 1);
					break;
				}
			}
			$scope.addAssignee = "";
		}
		$scope.addIconChanged = function(){
			$scope.modalTile.icons.push($scope.addIcon);
		}
		$scope.removeIcon = function(iconID){
			for(var i = $scope.modalTile.icons.length - 1; i >= 0 ; i--)
			{
				if($scope.modalTile.icons[i].id == iconID){
					$scope.modalTile.icons.splice(i, 1);
					break;
				}
			}
			$scope.addIcon = "";
		}
		$scope.doneClicked = function(){
			var assigneeIDs = [];
			for(var i = 0; i < $scope.modalTile.assignees.length; i++)
			{
				assigneeIDs.push($scope.modalTile.assignees[i].id);
			}
			var iconIDs = [];
			for(var i = 0; i < $scope.modalTile.icons.length; i++)
			{
				iconIDs.push($scope.modalTile.icons[i].id);
			}
			var data = {
						id:$scope.modalTile.id,
						sprint_id:$scope.modalTile.sprint_id,
						summary:$scope.modalTile.summary,
						size:$scope.modalTile.size,
						percent_done:$scope.modalTile.percent_done,
						color_id:$scope.modalTile.color_id,
						description:$scope.modalTile.description,
						swimlane_id:$scope.modalTile.swimlane_id,
						queue_id:$scope.modalTile.queue_id,
						assignees:assigneeIDs.join(),
						icons:iconIDs.join()
					};
			var method = "post";
			if($scope.modalTile.id != ""){
				//Edit Tile
				data.id = $scope.modalTile.id;
				method = "put";
			}
			$http({
				method:method,
				url:$scope.src+"/tiles",
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				transformRequest: function(obj) {
					var str = [];
					for(var p in obj)
					str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
					return str.join("&");
				},
				data:data
			}).success(function(response) {
				$scope.requestBoard();
				$('#tileModal').modal('hide');
			});
		}
    }],
  }
});
//Returns first letter of string
boardApp.filter('firstLetter', function() {
  return function(input) {
	return input.charAt(0);
  };
});

boardApp.filter('initials', function() {
  return function(user) {
	return user.first_name.charAt(0)+user.last_name.charAt(0);
  };
});

boardApp.config(['$tooltipProvider', function($tooltipProvider){
  $tooltipProvider.setTriggers({container:"body"});
}]);

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
					break;
				}
			}
		}
	}
	return input;
  };
})