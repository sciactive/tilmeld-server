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
		this.data.abilities = [];
		this.data.address_type = 'us';
	};
	Group.prototype = new Entity();

	var thisClass = {
		// === The Name of the Class ===
		class: '\\Tilmeld\\Group',

		// === Class Variables ===
		etype: "group",

		// === Class Methods ===

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

	return Group;
}));
