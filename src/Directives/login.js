
(function(){
	if (typeof tilmeldModule === 'undefined') {
		tilmeldModule = angular.module('TilmeldDirectives', []);
	}

	tilmeldModule.directive('tilmeldLoginForm', function(){
		return {
			restrict: 'EA',
			transclude: true,
			scope: {
				entity: '='
			},
			controller: ['$scope', '$attrs', function($scope, $attrs){
				$scope.autosave = typeof $attrs.autosave !== "undefined";
				$scope.horizontal = typeof $attrs.horizontal !== "undefined";
				$scope.inline = typeof $attrs.inline !== "undefined";

				this.type = function(){
					if ($scope.horizontal) {
						return 'horizontal';
					} else if ($scope.inline) {
						return 'inline';
					} else {
						return 'default';
					}
				};

				this.autosave = function(){
					return $scope.autosave;
				};

				this.entity = function(){
					return $scope.entity;
				};
			}],
			templateURL: TilmeldOptions.tilmeldURL+'html/login.html'
		};
	});
})();