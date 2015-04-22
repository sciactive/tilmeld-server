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
		sort: 'nameFirst',
		entities: [],
		timezones: tilmeldTimezones,
		ability: '',
		verifyPassword: '',
		passwordVerified: null,
		usernameVerified: null,
		usernameVerifiedMessage: null,
		emailVerified: null,
		emailVerifiedMessage: null,
		success: null
	};
	$scope.entity = new User();
	$scope.currentUser = null;
	$scope.primaryGroups = [];
	$scope.secondaryGroups = [];

	User.current().then(function(user){
		if (user) {
			$scope.currentUser = user;
			$scope.$apply();
		}
	});
	Group.getPrimaryGroups().then(function(groups){
		$scope.primaryGroups = groups;
		$scope.$apply();
	});
	Group.getSecondaryGroups().then(function(groups){
		$scope.secondaryGroups = groups;
		$scope.$apply();
	});
	Nymph.getEntities({"class": '\\Tilmeld\\User'}).subscribe(function(entities){
		Nymph.updateArray($scope.uiState.entities, entities);
		Nymph.sort($scope.uiState.entities, $scope.uiState.sort);
		$scope.$apply();
	});

	var usernameTimer = null;
	$scope.$watch('entity.data.username', function(newValue, oldValue){
		if (newValue === oldValue) {
			return;
		}
		if (usernameTimer) {
			$timeout.cancel(usernameTimer);
		}
		usernameTimer = $timeout(function(){
			if (newValue === '') {
				$scope.uiState.usernameVerified = null;
				$scope.uiState.usernameVerifiedMessage = null;
				return;
			}
			$scope.entity.checkUsername().then(function(data){
				$scope.uiState.usernameVerified = data.result;
				$scope.uiState.usernameVerifiedMessage = data.message;
				$scope.$apply();
			});
		}, 400);
	});
	var emailTimer = null;
	$scope.$watch('entity.data.email', function(newValue, oldValue){
		if (newValue === oldValue) {
			return;
		}
		if (emailTimer) {
			$timeout.cancel(emailTimer);
		}
		emailTimer = $timeout(function(){
			if (newValue === '') {
				$scope.uiState.emailVerified = null;
				$scope.uiState.emailVerifiedMessage = null;
				return;
			}
			$scope.entity.checkEmail().then(function(data){
				$scope.uiState.emailVerified = data.result;
				$scope.uiState.emailVerifiedMessage = data.message;
				$scope.$apply();
			});
		}, 400);
	});

	$scope.verifyPassword = function(){
		if (
				(typeof $scope.entity.data.passwordTemp === 'undefined' || $scope.entity.data.passwordTemp === '') &&
				(typeof $scope.uiState.verifyPassword === 'undefined' || $scope.uiState.verifyPassword === '')
			) {
			$scope.uiState.passwordVerified = null;
		}
		$scope.uiState.passwordVerified = $scope.entity.data.passwordTemp === $scope.uiState.verifyPassword;
	};

	$scope.addAbility = function(){
		if ($scope.uiState.ability === '') {
			return;
		}
		$scope.entity.data.abilities.push($scope.uiState.ability);
		$scope.uiState.ability = '';
	};

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
		sort: 'name',
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
