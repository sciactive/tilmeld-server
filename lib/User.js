(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define(["exports", "Nymph", "NymphEntity"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Nymph"), require("NymphEntity"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Nymph, global.NymphEntity);
    global.User = mod.exports;
  }
})(this, function (exports, _Nymph, _NymphEntity) {
  "use strict";

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.User = undefined;

  var _Nymph2 = _interopRequireDefault(_Nymph);

  var _NymphEntity2 = _interopRequireDefault(_NymphEntity);

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule ? obj : {
      default: obj
    };
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  var _createClass = function () {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ("value" in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function (Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  }();

  function _possibleConstructorReturn(self, call) {
    if (!self) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return call && (typeof call === "object" || typeof call === "function") ? call : self;
  }

  function _inherits(subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
  }

  var User = function (_Entity) {
    _inherits(User, _Entity);

    // === Constructor ===

    // === Static Properties ===

    function User(id) {
      _classCallCheck(this, User);

      var _this = _possibleConstructorReturn(this, (User.__proto__ || Object.getPrototypeOf(User)).call(this, id));

      _this.data.enabled = true;
      _this.data.abilities = [];
      _this.data.groups = [];
      _this.data.inheritAbilities = true;
      _this.data.addressType = 'us';
      return _this;
    }

    // === Instance Methods ===

    // The name of the server class


    _createClass(User, [{
      key: "checkUsername",
      value: function checkUsername() {
        for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }

        return this.serverCall('checkUsername', args);
      }
    }, {
      key: "checkEmail",
      value: function checkEmail() {
        for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
          args[_key2] = arguments[_key2];
        }

        return this.serverCall('checkEmail', args);
      }
    }, {
      key: "checkPhone",
      value: function checkPhone() {
        for (var _len3 = arguments.length, args = Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
          args[_key3] = arguments[_key3];
        }

        return this.serverCall('checkPhone', args);
      }
    }, {
      key: "getAvatar",
      value: function getAvatar() {
        for (var _len4 = arguments.length, args = Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
          args[_key4] = arguments[_key4];
        }

        return this.serverCall('getAvatar', args);
      }
    }, {
      key: "register",
      value: function register() {
        for (var _len5 = arguments.length, args = Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
          args[_key5] = arguments[_key5];
        }

        return this.serverCall('register', args);
      }
    }, {
      key: "logout",
      value: function logout() {
        for (var _len6 = arguments.length, args = Array(_len6), _key6 = 0; _key6 < _len6; _key6++) {
          args[_key6] = arguments[_key6];
        }

        return this.serverCall('logout', args);
      }
    }, {
      key: "login",
      value: function login() {
        for (var _len7 = arguments.length, args = Array(_len7), _key7 = 0; _key7 < _len7; _key7++) {
          args[_key7] = arguments[_key7];
        }

        return this.serverCall('login', args);
      }
    }, {
      key: "gatekeeper",
      value: function gatekeeper() {
        for (var _len8 = arguments.length, args = Array(_len8), _key8 = 0; _key8 < _len8; _key8++) {
          args[_key8] = arguments[_key8];
        }

        return this.serverCall('gatekeeper', args);
      }
    }, {
      key: "recover",
      value: function recover() {
        for (var _len9 = arguments.length, args = Array(_len9), _key9 = 0; _key9 < _len9; _key9++) {
          args[_key9] = arguments[_key9];
        }

        return this.serverCall('recover', args);
      }
    }], [{
      key: "current",
      value: function current(returnObjectIfNotExist) {
        return User.serverCallStatic('current', [returnObjectIfNotExist]);
      }
    }, {
      key: "getClientConfig",
      value: function getClientConfig() {
        for (var _len10 = arguments.length, args = Array(_len10), _key10 = 0; _key10 < _len10; _key10++) {
          args[_key10] = arguments[_key10];
        }

        return User.serverCallStatic('getClientConfig', args);
      }
    }]);

    return User;
  }(_NymphEntity2.default);

  User.etype = "tilmeld_user";
  User.class = "Tilmeld\\Entities\\User";
  exports.default = User;


  _Nymph2.default.setEntityClass(User.class, User);
  exports.User = User;
});