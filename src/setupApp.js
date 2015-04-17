angular.module('setupApp', ['ngRoute'])
.controller('MainController', ['$scope', '$route', '$routeParams', '$location', function ($scope, $route, $routeParams, $location) {
	$scope.$route = $route;
	$scope.$location = $location;
	$scope.$routeParams = $routeParams;
}])

.controller('UserController', ['$scope', '$routeParams', '$timeout', function ($scope, $routeParams, $timeout) {
	$scope.params = $routeParams;
	$scope.uiState = {
		loading: false,
		sort: 'first_name',
		entities: [],
		timezones: timezones,
		success: null
	};
	$scope.entity = new User();

	Nymph.getEntities({"class": '\\Tilmeld\\User'}).subscribe(function(entities){
		Nymph.updateArray($scope.uiState.entities, entities);
		Nymph.sort($scope.uiState.entities, $scope.uiState.sort);
		$scope.$apply();
	});

	$scope.saveEntity = function(){
		$scope.entity.save().then(function(success){
			if (success) {
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
	$scope.uiState = {
		loading: false,
		sort: 'first_name',
		entities: [],
		success: null
	};
	$scope.entity = new Group();

	Nymph.getEntities({"class": '\\Tilmeld\\Group'}).subscribe(function(entities){
		Nymph.updateArray($scope.uiState.entities, entities);
		Nymph.sort($scope.uiState.entities, $scope.uiState.sort);
		$scope.$apply();
	});

	$scope.saveEntity = function(){
		$scope.entity.save().then(function(success){
			if (success) {
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
		}
	};
}])

.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
	$routeProvider
		.when('/', {
			templateUrl: baseURL+'html/instructions.html'
		})
		.when('/user/:entityId?', {
			templateUrl: baseURL+'html/user.html',
			controller: 'UserController'
		})
		.when('/group/:entityId?', {
			templateUrl: baseURL+'html/group.html',
			controller: 'GroupController'
		});

	$locationProvider.html5Mode(false);
}]);
