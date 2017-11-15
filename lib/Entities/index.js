(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define(["exports", "./User", "./Group"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("./User"), require("./Group"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.User, global.Group);
    global.index = mod.exports;
  }
})(this, function (exports, _User, _Group) {
  "use strict";

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.Group = exports.User = undefined;

  var _User2 = _interopRequireDefault(_User);

  var _Group2 = _interopRequireDefault(_Group);

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule ? obj : {
      default: obj
    };
  }

  exports.User = _User2.default;
  exports.Group = _Group2.default;
});