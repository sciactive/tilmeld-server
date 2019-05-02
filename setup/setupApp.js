angular.module('setupApp', ['ngRoute'])
.service('Nymph', function() {
  return window['nymph-client'].Nymph;
})
.service('User', function() {
  return window['tilmeld-client'].User;
})
.service('Group', function() {
  return window['tilmeld-client'].Group;
})

.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
  $locationProvider.hashPrefix('!');

  $routeProvider
    .when('/instructions', {
      controller: 'InstructionsController',
      templateUrl: window.TilmeldOptions.tilmeldURL+'setup/instructions.html'
    })
    .when('/user', {
      controller: 'UserController',
      templateUrl: window.TilmeldOptions.tilmeldURL+'setup/user.html'
    })
    .when('/group', {
      controller: 'GroupController',
      templateUrl: window.TilmeldOptions.tilmeldURL+'setup/group.html'
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
    ability: '',
    entitySearch: '',
    primaryGroupSearch: '',
    secondayGroupSearch: '',
    verifyPassword: '',
    passwordVerified: null,
    usernameVerified: null,
    usernameVerifiedMessage: null,
    emailVerified: null,
    emailVerifiedMessage: null,
    success: null
  };

  $scope.setEntity = function(entity) {
    $scope.entity = entity ? entity : new User();
  };

  function setup() {
    $scope.setEntity();
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

  $scope.entities = [];
  $scope.searchEntities = function() {
    $scope.entities = null;
    var query = $scope.uiState.entitySearch;
    if (!query.match(/[_%]/)) {
      query += '%';
    }
    Nymph.getEntities({"class": User.class}, {
      'type': '|',
      'ilike': [
        ['name', query],
        ['username', query]
      ],
    }).then(function(entities) {
      $scope.entities = entities;
      $scope.$apply();
    });
  };

  $scope.primaryGroups = [];
  $scope.searchPrimaryGroups = function() {
    $scope.primaryGroups = null;
    var query = $scope.uiState.primaryGroupSearch;
    if (!query.match(/[_%]/)) {
      query += '%';
    }
    Group.getPrimaryGroups(query).then(function(groups) {
      $scope.primaryGroups = groups.filter(function(group) {
        return !group.is($scope.entity.data.group);
      });
      $scope.$apply();
    });
  };

  $scope.secondaryGroups = [];
  $scope.searchSecondaryGroups = function() {
    $scope.secondaryGroups = null;
    var query = $scope.uiState.secondayGroupSearch;
    if (!query.match(/[_%]/)) {
      query += '%';
    }
    Group.getSecondaryGroups(query).then(function(groups) {
      $scope.secondaryGroups = groups.filter(function(group) {
        return !group.inArray($scope.entity.data.groups);
      });
      $scope.$apply();
    });
  };

  $scope.$watch('entity.data.email', function() {
    if ($scope.entity) {
      $scope.entity.getAvatar().then(function(avatar) {
        $scope.avatar = avatar;
        $scope.$apply();
      });
      $scope.entity.readyAll(1).then(function() {
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
      $scope.entity.readyAll(1).then(function() {
        $scope.$apply();
      });
    }, function(errObj) {
      console.log("errObj:",errObj);
      alert("Error: "+errObj.message);
    });
  };

  $scope.deleteEntity = function() {
    if (confirm('Are you sure you want to delete this?')) {
      $scope.entity.delete().then(function() {
        setup();
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
    entitySearch: '',
    parentSearch: '',
    ability: '',
    groupnameVerified: null,
    groupnameVerifiedMessage: null,
    emailVerified: null,
    emailVerifiedMessage: null,
    success: null
  };

  $scope.setEntity = function(entity) {
    $scope.entity = entity ? entity : new Group();
  };

  function setup() {
    $scope.setEntity();
  }
  setup();

  $scope.entities = [];
  $scope.searchEntities = function() {
    $scope.entities = null;
    var query = $scope.uiState.entitySearch;
    if (!query.match(/[_%]/)) {
      query += '%';
    }
    Nymph.getEntities({"class": Group.class}, {
      'type': '|',
      'ilike': [
        ['name', query],
        ['groupname', query]
      ],
    }).then(function(entities) {
      $scope.entities = entities;
      $scope.$apply();
    });
  };

  $scope.parents = [];
  $scope.searchParents = function() {
    $scope.parents = null;
    var query = $scope.uiState.parentSearch;
    if (!query.match(/[_%]/)) {
      query += '%';
    }
    Nymph.getEntities({"class": Group.class}, {
      'type': '|',
      'ilike': [
        ['name', query],
        ['groupname', query]
      ],
    }).then(function(groups) {
      $scope.parents = groups.filter(function(group) {
        return !group.is($scope.entity) && !group.is($scope.entity.data.parent);
      });
      $scope.$apply();
    });
  };

  $scope.$watch('entity.data.email', function() {
    if ($scope.entity) {
      $scope.entity.readyAll(1).then(function() {
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
      $scope.entity.readyAll(1).then(function() {
        $scope.$apply();
      });
    }, function(errObj) {
      console.log("errObj:",errObj);
      alert("Error: "+errObj.message);
    });
  };

  $scope.deleteEntity = function() {
    if (confirm('Are you sure you want to delete this?')) {
      $scope.entity.delete().then(function() {
        setup();
        $scope.$apply();
      }, function(err) {
        alert("An error occurred: "+err.textStatus);
      });
    }
  };
}]);
