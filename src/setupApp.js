angular.module('setupApp', ['ngRoute'])
.controller('MainController', ['$scope', '$route', '$routeParams', '$location', function ($scope, $route, $routeParams, $location) {
	$scope.$route = $route;
	$scope.$location = $location;
	$scope.$routeParams = $routeParams;
}])

.controller('UserController', ['$scope', '$routeParams', '$timeout', function ($scope, $routeParams, $timeout) {
	$scope.params = $routeParams;
	$scope.definitions = Definitions;
	$scope.examples = Examples;
	$scope.entities = [];
	$scope.success = null;

	Nymph.getEntities({'class': '\\Tilmeld\\User'}).then(function(entities){
		$scope.entities = entities;
		$scope.$apply();
	});

	$scope.askDefaultContent = function(){
		if (Definitions[$scope.entity.data.definition].html && Definitions[$scope.entity.data.definition].subject) {
			if (confirm("Would you like to start with this definition's default content and subject?")) {
				$scope.entity.data.subject = Definitions[$scope.entity.data.definition].subject;
				$scope.entity.data.content = Definitions[$scope.entity.data.definition].html;
			}
		} else if (Definitions[$scope.entity.data.definition].html) {
			if (confirm("Would you like to start with this definition's default content?")) {
				$scope.entity.data.content = Definitions[$scope.entity.data.definition].html;
			}
		} else if (Definitions[$scope.entity.data.definition].subject) {
			if (confirm("Would you like to start with this definition's default subject?")) {
				$scope.entity.data.subject = Definitions[$scope.entity.data.definition].subject;
			}
		}
	};

	$scope.entity = new User();

	$scope.saveEntity = function(){
		$scope.entity.save().then(function(success){
			if (success) {
				if (!$scope.entity.inArray($scope.entities)) {
					$scope.entities.push($scope.entity);
				}
				$scope.success = true;
				$timeout(function(){
					$scope.success = null;
				}, 1000);
				$scope.$apply();
			} else {
				alert("Error saving user.");
			}
		}, function(){
			alert("Error communicating data.");
		});
	};

	$scope.checkNewEntity = function(){
		if (!$scope.entity) {
			$scope.entity = new User();
		}
	};
}])

.controller('GroupController', ['$scope', '$routeParams', '$timeout', function ($scope, $routeParams, $timeout) {
	$scope.params = $routeParams;
	$scope.examples = Examples;
	$scope.entities = [];
	$scope.success = null;

	Nymph.getEntities({'class': '\\Tilmeld\\Group'}).then(function(entities){
		$scope.entities = entities;
	});

	$scope.entity = new Group();
	$scope.entity.defaultContent().then(function(){
		$scope.$apply();
	});

	$scope.saveEntity = function(){
		$scope.entity.save().then(function(success){
			if (success) {
				if (!$scope.entity.inArray($scope.entities)) {
					$scope.entities.push($scope.entity);
				}
				$scope.success = true;
				$timeout(function(){
					$scope.success = null;
				}, 1000);
				$scope.$apply();
			} else {
				alert("Error saving group.");
			}
		}, function(){
			alert("Error communicating data.");
		});
	};

	$scope.checkNewEntity = function(){
		if (!$scope.entity) {
			$scope.entity = new Group();
			$scope.entity.defaultContent().then(function(){
				$scope.$apply();
			});
		}
	};
}])

.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	$routeProvider
		.when('/', {
			groupUrl: baseURL+'html/instructions.html'
		})
		.when('/user/:entityId?', {
			groupUrl: baseURL+'html/user.html',
			controller: 'UserController'
		})
		.when('/group/:entityId?', {
			groupUrl: baseURL+'html/group.html',
			controller: 'GroupController'
		});

	$locationProvider.html5Mode(false);
}]);
