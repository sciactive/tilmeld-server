// Uses AMD or browser globals.
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as a module.
    define('NymphGroup', ['NymphEntity'], factory);
  } else {
    // Browser globals
    factory(Entity);
  }
}(function(Entity){
  Group = function(id){
    this.constructor.call(this, id);
    // Defaults.
    this.data.enabled = true;
    this.data.abilities = [];
    this.data.addressType = 'us';
    this.info.avatar = '//secure.gravatar.com/avatar/?d=mm&s=40';
  };
  Group.prototype = new Entity();

  var thisClass = {
    // === The Name of the Server Class ===
    class: '\\Tilmeld\\Group',

    // === Class Variables ===
    etype: "group",

    // === Class Methods ===

    checkGroupname: function(){
      return this.serverCall('checkGroupname', arguments);
    },

    checkEmail: function(){
      return this.serverCall('checkEmail', arguments);
    },

    getChildren: function(){
      return this.serverCall('getChildren', arguments);
    },

    getDescendants: function(){
      return this.serverCall('getDescendants', arguments);
    },

    getLevel: function(){
      return this.serverCall('getLevel', arguments);
    },

    isDescendant: function(){
      return this.serverCall('isDescendant', arguments);
    }
  };
  for (var p in thisClass) {
    if (thisClass.hasOwnProperty(p)) {
      Group.prototype[p] = thisClass[p];
    }
  }
  Group.getPrimaryGroups = function(){
    return this.prototype.serverCallStatic('getPrimaryGroups', arguments);
  };
  Group.getSecondaryGroups = function(){
    return this.prototype.serverCallStatic('getSecondaryGroups', arguments);
  };

  return Group;
}));
