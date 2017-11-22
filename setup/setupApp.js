angular.module('setupApp', ['ngRoute'])
.service('Nymph', function() {
  return Nymph.default;
})
.service('User', function() {
  return User.default;
})
.service('Group', function() {
  return Group.default;
})

.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
  $locationProvider.hashPrefix('!');

  $routeProvider
    .when('/instructions', {
      controller: 'InstructionsController',
      templateUrl: TilmeldOptions.tilmeldURL+'setup/instructions.html'
    })
    .when('/user', {
      controller: 'UserController',
      templateUrl: TilmeldOptions.tilmeldURL+'setup/user.html'
    })
    .when('/group', {
      controller: 'GroupController',
      templateUrl: TilmeldOptions.tilmeldURL+'setup/group.html'
    })
    .otherwise('/instructions');

  $locationProvider.html5Mode(false);
}])

.controller('MainController', ['$scope', '$route', '$routeParams', '$location', function($scope, $route, $routeParams, $location) {
  $scope.$route = $route;
  $scope.$location = $location;
  $scope.$routeParams = $routeParams;
}])

.controller('InstructionsController', ['$scope', function($scope) {
  $scope.name = 'InstructionsController';
}])

.controller('UserController', ['$scope', '$routeParams', '$timeout', 'Nymph', 'User', 'Group', function($scope, $routeParams, $timeout, Nymph, User, Group) {
  $scope.name = 'UserController';
  $scope.params = $routeParams;
  $scope.clientConfig = {};
  $scope.uiState = {
    loading: false,
    sort: 'nameFirst',
    entities: [],
    ability: '',
    verifyPassword: '',
    passwordVerified: null,
    usernameVerified: null,
    usernameVerifiedMessage: null,
    emailVerified: null,
    emailVerifiedMessage: null,
    success: null
  };
  $scope.primaryGroups = [];
  $scope.secondaryGroups = [];

  function setup() {
    $scope.entity = new User();
    $scope.currentUser = null;
    $scope.avatar = '//secure.gravatar.com/avatar/?d=mm&s=40';
    User.current().then(function(user) {
      if (user) {
        $scope.currentUser = user;
        $scope.$apply();
      }
    });
  }
  setup();

  $scope.$watch('entity.data.email', function() {
    if ($scope.entity) {
      $scope.entity.getAvatar().then(function(avatar) {
        $scope.avatar = avatar;
        $scope.$apply();
      });
    }
  });

  User.getClientConfig().then(function(config) {
    if (config) {
      $scope.clientConfig = config;
      $scope.$apply();
    }
  });
  Group.getPrimaryGroups().then(function(groups) {
    $scope.primaryGroups = groups;
    $scope.$apply();
  });
  Group.getSecondaryGroups().then(function(groups) {
    $scope.secondaryGroups = groups;
    $scope.$apply();
  });

  var updateEntitiesCallback = function(entities) {
    var newEntity = new User();
    if ($scope.uiState.entities.length && $scope.uiState.entities[0].guid === null) {
      newEntity = $scope.uiState.entities[0];
      $scope.uiState.entities.splice(0, 1);
    }
    Nymph.updateArray($scope.uiState.entities, entities);
    Nymph.sort($scope.uiState.entities, $scope.uiState.sort);
    $scope.uiState.entities.splice(0, 0, newEntity);
    $scope.$apply();
  };
  var updateEntities = function() {
    var entitiesPromise = Nymph.getEntities({"class": User.class});
    if (TilmeldOptions.pubsub) {
      entitiesPromise.subscribe(updateEntitiesCallback);
    } else {
      entitiesPromise.then(updateEntitiesCallback);
    }
  };
  updateEntities();

  var usernameTimer = null;
  $scope.$watch('entity.data.username', function(newValue, oldValue) {
    if (newValue === oldValue) {
      return;
    }
    if (usernameTimer) {
      $timeout.cancel(usernameTimer);
    }
    usernameTimer = $timeout(function() {
      if (newValue === '') {
        $scope.uiState.usernameVerified = null;
        $scope.uiState.usernameVerifiedMessage = null;
        return;
      }
      $scope.entity.checkUsername().then(function(data) {
        $scope.uiState.usernameVerified = data.result;
        $scope.uiState.usernameVerifiedMessage = data.message;
        $scope.$apply();
      });
    }, 400);
  });
  var emailTimer = null;
  $scope.$watch('entity.data.email', function(newValue, oldValue) {
    if (newValue === oldValue) {
      return;
    }
    if (emailTimer) {
      $timeout.cancel(emailTimer);
    }
    emailTimer = $timeout(function() {
      if (newValue === '') {
        $scope.uiState.emailVerified = null;
        $scope.uiState.emailVerifiedMessage = null;
        return;
      }
      $scope.entity.checkEmail().then(function(data) {
        $scope.uiState.emailVerified = data.result;
        $scope.uiState.emailVerifiedMessage = data.message;
        $scope.$apply();
      });
    }, 400);
  });

  $scope.verifyPassword = function() {
    if (
        (typeof $scope.entity.data.passwordTemp === 'undefined' || $scope.entity.data.passwordTemp === '') &&
        (typeof $scope.uiState.verifyPassword === 'undefined' || $scope.uiState.verifyPassword === '')
      ) {
      $scope.uiState.passwordVerified = null;
    }
    $scope.uiState.passwordVerified = $scope.entity.data.passwordTemp === $scope.uiState.verifyPassword;
  };

  $scope.addAbility = function() {
    if ($scope.uiState.ability === '') {
      return;
    }
    $scope.entity.data.abilities.push($scope.uiState.ability);
    $scope.uiState.ability = '';
  };

  $scope.addSysAdminAbility = function() {
    if ($scope.entity.data.abilities.indexOf('system/admin') === -1) {
      $scope.entity.data.abilities.push('system/admin');
    }
  };

  $scope.saveEntity = function() {
    $scope.entity.save().then(function(success) {
      if (success) {
        $scope.success = true;
        $timeout(function() {
          $scope.success = null;
        }, 1000);
        $scope.$apply();
      } else {
        alert("Error saving user.");
      }
      if (!TilmeldOptions.pubsub) {
        updateEntities();
      }
    }, function(errObj) {
      // Todo: handle exceptions.
      console.log("errObj:",errObj);
      alert("Error communicating data.");
    });
  };

  $scope.deleteEntity = function() {
    if (confirm('Are you sure you want to delete this?')) {
      $scope.entity.delete().then(function() {
        setup();
        if (!TilmeldOptions.pubsub) {
          updateEntities();
        }
        $scope.$apply();
      }, function(err) {
        alert("An error occurred: "+err.textStatus);
      });
    }
  };
}])

.controller('GroupController', ['$scope', '$routeParams', '$timeout', 'Nymph', 'User', 'Group', function($scope, $routeParams, $timeout, Nymph, User, Group) {
  $scope.name = 'GroupController';
  $scope.params = $routeParams;
  $scope.clientConfig = {};
  $scope.uiState = {
    loading: false,
    sort: 'name',
    entities: [],
    parents: [],
    ability: '',
    groupnameVerified: null,
    groupnameVerifiedMessage: null,
    emailVerified: null,
    emailVerifiedMessage: null,
    success: null
  };

  function setup() {
    $scope.entity = new Group();
  }
  setup();

  User.getClientConfig().then(function(config) {
    if (config) {
      $scope.clientConfig = config;
      $scope.$apply();
    }
  });

  var updateEntitiesCallback = function(entities) {
    var newEntity = new Group();
    if ($scope.uiState.entities.length && $scope.uiState.entities[0].guid === null) {
      newEntity = $scope.uiState.entities[0];
      $scope.uiState.entities.splice(0, 1);
    }
    Nymph.updateArray($scope.uiState.entities, entities);
    Nymph.sort($scope.uiState.entities, $scope.uiState.sort);
    $scope.uiState.parents = $scope.uiState.entities.filter(function(group) {
      return !(group.guid === null || group.guid === $scope.entity.guid);
    });
    $scope.uiState.entities.splice(0, 0, newEntity);
    $scope.$apply();
  };
  var updateEntities = function() {
    var entitiesPromise = Nymph.getEntities({"class": Group.class});
    if (TilmeldOptions.pubsub) {
      entitiesPromise.subscribe(updateEntitiesCallback);
    } else {
      entitiesPromise.then(updateEntitiesCallback);
    }
  };
  updateEntities();

  var groupnameTimer = null;
  $scope.$watch('entity.data.groupname', function(newValue, oldValue) {
    if (newValue === oldValue) {
      return;
    }
    if (groupnameTimer) {
      $timeout.cancel(groupnameTimer);
    }
    groupnameTimer = $timeout(function() {
      if (newValue === '') {
        $scope.uiState.groupnameVerified = null;
        $scope.uiState.groupnameVerifiedMessage = null;
        return;
      }
      $scope.entity.checkGroupname().then(function(data) {
        $scope.uiState.groupnameVerified = data.result;
        $scope.uiState.groupnameVerifiedMessage = data.message;
        $scope.$apply();
      });
    }, 400);
  });
  var emailTimer = null;
  $scope.$watch('entity.data.email', function(newValue, oldValue) {
    if (newValue === oldValue) {
      return;
    }
    if (emailTimer) {
      $timeout.cancel(emailTimer);
    }
    emailTimer = $timeout(function() {
      if (newValue === '') {
        $scope.uiState.emailVerified = null;
        $scope.uiState.emailVerifiedMessage = null;
        return;
      }
      $scope.entity.checkEmail().then(function(data) {
        $scope.uiState.emailVerified = data.result;
        $scope.uiState.emailVerifiedMessage = data.message;
        $scope.$apply();
      });
    }, 400);
  });
  var cacheUser = null;
  $scope.$watch('entity.data.user', function(newValue, oldValue) {
    if (
        newValue === oldValue
        || newValue === undefined
        || !newValue.isASleepingReference
      ) {
      return;
    }
    if ($scope.entity.data.user.isASleepingReference
        && !$scope.entity.data.user.readyPromise
      ) {
      if (cacheUser !== null && cacheUser.is($scope.entity.data.user)) {
        $scope.entity.data.user.init(cacheUser.toJSON());
      } else {
        $scope.entity.data.user.ready().then(function(user) {
          cacheUser = user;
          $scope.$apply();
        });
      }
    }
  }, true);

  $scope.addAbility = function() {
    if ($scope.uiState.ability === '') {
      return;
    }
    $scope.entity.data.abilities.push($scope.uiState.ability);
    $scope.uiState.ability = '';
  };

  $scope.addSysAdminAbility = function() {
    if ($scope.entity.data.abilities.indexOf('system/admin') === -1) {
      $scope.entity.data.abilities.push('system/admin');
    }
  };

  $scope.saveEntity = function() {
    $scope.entity.save().then(function(success) {
      if (success) {
        $scope.success = true;
        $timeout(function() {
          $scope.success = null;
        }, 1000);
        $scope.$apply();
      } else {
        alert("Error saving group.");
      }
      if (!TilmeldOptions.pubsub) {
        updateEntities();
      }
    }, function() {
      alert("Error communicating data.");
    });
  };

  $scope.deleteEntity = function() {
    if (confirm('Are you sure you want to delete this?')) {
      $scope.entity.delete().then(function() {
        setup();
        if (!TilmeldOptions.pubsub) {
          updateEntities();
        }
        $scope.$apply();
      }, function(err) {
        alert("An error occurred: "+err.textStatus);
      });
    }
  };
}]);
