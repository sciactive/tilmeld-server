// Uses AMD or browser globals.
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as a module.
        define('NymphUser', ['NymphEntity'], factory);
    } else {
        // Browser globals
        factory(Entity);
    }
}(function(Entity){
	User = function(id){
		this.constructor.call(this, id);
		// Defaults.
		this.data.enabled = true;
		this.data.abilities = [];
		this.data.groups = [];
		this.data.inherit_abilities = true;
		this.data.address_type = 'us';
		this.info.avatar = '//secure.gravatar.com/avatar/?d=mm&s=40';
	};
	User.prototype = new Entity();

	var thisClass = {
		// === The Name of the Class ===
		class: '\\Tilmeld\\User',

		// === Class Variables ===
		etype: "user",

		// === Class Methods ===

		checkUsername: function(){
			return this.serverCall('checkUsername', arguments);
		},

		checkEmail: function(){
			return this.serverCall('checkEmail', arguments);
		},

		checkPhone: function(){
			return this.serverCall('checkPhone', arguments);
		},

		register: function(){
			return this.serverCall('register', arguments);
		},

		logout: function(){
			return this.serverCall('logout', arguments);
		},

		login: function(){
			return this.serverCall('login', arguments);
		},

		gatekeeper: function(){
			return this.serverCall('gatekeeper', arguments);
		},

		recover: function(){
			return this.serverCall('recover', arguments);
		}
	};
	for (var p in thisClass) {
		if (thisClass.hasOwnProperty(p)) {
			User.prototype[p] = thisClass[p];
		}
	}

	return User;
}));
