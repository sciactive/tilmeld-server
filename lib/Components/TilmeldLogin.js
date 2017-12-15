(function (global, factory) {
	if (typeof define === "function" && define.amd) {
		define(['exports', 'Nymph', 'User', './TilmeldRecover.html'], factory);
	} else if (typeof exports !== "undefined") {
		factory(exports, require('Nymph'), require('User'), require('./TilmeldRecover.html'));
	} else {
		var mod = {
			exports: {}
		};
		factory(mod.exports, global.Nymph, global.User, global.TilmeldRecover);
		global.TilmeldLogin = mod.exports;
	}
})(this, function (exports, _Nymph, _User, _TilmeldRecover) {
	'use strict';

	Object.defineProperty(exports, "__esModule", {
		value: true
	});

	var _Nymph2 = _interopRequireDefault(_Nymph);

	var _User2 = _interopRequireDefault(_User);

	var _TilmeldRecover2 = _interopRequireDefault(_TilmeldRecover);

	function _interopRequireDefault(obj) {
		return obj && obj.__esModule ? obj : {
			default: obj
		};
	}

	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
		return typeof obj;
	} : function (obj) {
		return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
	};

	function nameFirst(name) {
		return name.match(/^(.*?)(?: ([^ ]+))?$/)[1] || '';
	}

	function nameLast(name) {
		return name.match(/^(.*?)(?: ([^ ]+))?$/)[2] || '';
	}

	function data() {
		return {
			layout: 'normal', // 'normal', 'small', or 'compact'
			compactText: 'Log in/Sign up', // The text used to toggle the dialog when 'compact' layout is selected.
			hideRecovery: false, // Hide the recovery link that only appears if password recovery is on.
			autofocus: true, // Give focus to the username box (or email box) when the form is ready.
			existingUser: true, // This determines whether the 'I have an account.' box is checked and the registration form is shown.
			showExistingUserCheckbox: true, // Whether to show the 'I have an account.' box.
			states: [// The states available when address type is US.
			['AL', 'Alabama'], ['AK', 'Alaska'], ['AZ', 'Arizona'], ['AR', 'Arkansas'], ['CA', 'California'], ['CO', 'Colorado'], ['CT', 'Connecticut'], ['DE', 'Delaware'], ['DC', 'DC'], ['FL', 'Florida'], ['GA', 'Georgia'], ['HI', 'Hawaii'], ['ID', 'Idaho'], ['IL', 'Illinois'], ['IN', 'Indiana'], ['IA', 'Iowa'], ['KS', 'Kansas'], ['KY', 'Kentucky'], ['LA', 'Louisiana'], ['ME', 'Maine'], ['MD', 'Maryland'], ['MA', 'Massachusetts'], ['MI', 'Michigan'], ['MN', 'Minnesota'], ['MS', 'Mississippi'], ['MO', 'Missouri'], ['MT', 'Montana'], ['NE', 'Nebraska'], ['NV', 'Nevada'], ['NH', 'New Hampshire'], ['NJ', 'New Jersey'], ['NM', 'New Mexico'], ['NY', 'New York'], ['NC', 'North Carolina'], ['ND', 'North Dakota'], ['OH', 'Ohio'], ['OK', 'Oklahoma'], ['OR', 'Oregon'], ['PA', 'Pennsylvania'], ['RI', 'Rhode Island'], ['SC', 'South Carolina'], ['SD', 'South Dakota'], ['TN', 'Tennessee'], ['TX', 'Texas'], ['UT', 'Utah'], ['VT', 'Vermont'], ['VA', 'Virginia'], ['WA', 'Washington'], ['WV', 'West Virginia'], ['WI', 'Wisconsin'], ['WY', 'Wyoming'], ['AA', 'Armed Forces (AA)'], ['AE', 'Armed Forces (AE)'], ['AP', 'Armed Forces (AP)']],
			classCheckbox: '',
			classInput: '',
			classRadio: '',
			classSelect: '',
			classTextarea: '',
			classSubmit: '',
			classButton: '',
			__clientConfig: {
				'reg_fields': [],
				'email_usernames': true,
				'allow_registration': true,
				'pw_recovery': true,
				'timezones': []
			},
			__showDialog: false,
			__registering: false,

			// These are all user provided details.
			username: '',
			password: '',
			password2: '',
			name: '',
			email: '',
			phone: '',
			timezone: '',
			addressType: 'us',
			addressStreet: '',
			addressStreet2: '',
			addressCity: '',
			addressState: 'AL',
			addressZip: '',
			addressInternational: ''
		};
	};

	var methods = {
		login: function login() {
			var _this = this;

			if (this.get('username') === '') {
				this.set({ __failureMessage: 'You need to enter a username.' });
				return;
			}
			if (this.get('password') === '') {
				this.set({ __failureMessage: 'You need to enter a password' });
				return;
			}

			this.set({
				__failureMessage: null,
				__loggingin: true
			});
			_User2.default.loginUser({ username: this.get('username'), password: this.get('password') }).then(function (data) {
				if (!data.result) {
					_this.set({ __failureMessage: data.message });
				} else {
					_this.set({
						__successLoginMessage: data.message,
						__showDialog: false
					});
					_this.fire('login', { user: data.user });
				}
				_this.set({ __loggingin: false });
			}, function () {
				_this.set({
					__failureMessage: 'An error occurred.',
					__loggingin: false
				});
			});
		},
		register: function register() {
			var _this2 = this;

			if (this.get('username') === '') {
				this.set({ __failureMessage: 'You need to enter a username.' });
				return;
			}
			if (!this.get('__usernameVerified')) {
				this.set({ __failureMessage: 'The username you entered is not valid.' });
				return;
			}
			if (this.get('password') != this.get('password2')) {
				this.set({ __failureMessage: 'Your passwords do not match.' });
				return;
			}
			if (this.get('password') === '') {
				this.set({ __failureMessage: 'You need to enter a password' });
				return;
			}

			// Create a new user.
			var user = new _User2.default();
			user.set('username', this.get('username'));
			if (this.get('__clientConfig').email_usernames) {
				user.set('email', this.get('username'));
			} else if (this.get('__clientConfig').reg_fields.indexOf('email') !== -1) {
				user.set('email', this.get('email'));
			}
			if (this.get('__clientConfig').reg_fields.indexOf('name') !== -1) {
				user.set({
					'nameFirst': this.get('nameFirst'),
					'nameLast': this.get('nameLast')
				});
			}
			if (this.get('__clientConfig').reg_fields.indexOf('phone') !== -1) {
				user.set('phone', this.get('phone'));
			}
			if (this.get('__clientConfig').reg_fields.indexOf('timezone') !== -1) {
				user.set('timezone', this.get('timezone'));
			}
			if (this.get('__clientConfig').reg_fields.indexOf('address') !== -1) {
				user.set({
					'addressType': this.get('addressType'),
					'addressStreet': this.get('addressStreet'),
					'addressStreet2': this.get('addressStreet2'),
					'addressCity': this.get('addressCity'),
					'addressState': this.get('addressState'),
					'addressZip': this.get('addressZip'),
					'addressInternational': this.get('addressInternational')
				});
			}

			this.set({
				__failureMessage: null,
				__registering: true
			});
			user.register({ 'password': this.get('password') }).then(function (data) {
				if (!data.result) {
					_this2.set({ __failureMessage: data.message });
				} else {
					_this2.set({ __successRegisteredMessage: data.message });
					_this2.fire('register', { user: user });
				}
				if (data.loggedin) {
					_this2.set({
						__successLoginMessage: data.message,
						__showDialog: false
					});
					_this2.fire('login', { user: user });
				}
				_this2.set({ __registering: false });
			}, function () {
				_this2.set({
					__failureMessage: 'An error occurred.',
					__registering: false
				});
			});
		}
	};

	function oncreate() {
		var _this3 = this;

		_User2.default.getClientConfig().then(function (__clientConfig) {
			_this3.set({ __clientConfig: __clientConfig });
			if (!__clientConfig.allow_registration) {
				_this3.set({ existingUser: true });
			}
			if (_this3.get('autofocus') && _this3.refs.username) {
				_this3.refs.username.focus();
			}
		});

		var checkUsername = function checkUsername(newValue, oldValue) {
			if (newValue === oldValue) {
				return;
			}
			_this3.set({
				__usernameVerified: null,
				__usernameVerifiedMessage: null
			});
			var __usernameTimer = _this3.get('__usernameTimer');
			if (__usernameTimer) {
				clearTimeout(__usernameTimer);
				_this3.set({ __usernameTimer: null });
			}
			if (newValue === '' || _this3.get('existingUser')) {
				return;
			}
			__usernameTimer = setTimeout(function () {
				var user = new _User2.default();
				user.set('username', newValue);
				if (_this3.get('__clientConfig').email_usernames) {
					user.set('email', newValue);
				}
				user.checkUsername().then(function (data) {
					_this3.set({
						__usernameVerified: data.result,
						__usernameVerifiedMessage: data.result ? '' : data.message
					});
				}, function () {
					_this3.set({
						__usernameVerified: false,
						__usernameVerifiedMessage: 'Error checking username.'
					});
				});
			}, 400);
			_this3.set({ __usernameTimer: __usernameTimer });
		};

		this.observe('existingUser', function (value) {
			if (!value) {
				checkUsername(_this3.get('username'));
			}
		});

		this.observe('username', checkUsername);
	};

	function encapsulateStyles(node) {
		setAttribute(node, "svelte-3142936818", "");
	}

	function add_css() {
		var style = createElement("style");
		style.id = 'svelte-3142936818-style';
		style.textContent = "[svelte-3142936818].login-dialog-container,[svelte-3142936818] .login-dialog-container{display:flex;align-items:center}[svelte-3142936818].login-dialog-container.layout-compact,[svelte-3142936818] .login-dialog-container.layout-compact{justify-content:center;position:fixed;top:0;left:0;bottom:0;right:0;z-index:1000}[svelte-3142936818].login-dialog-overlay,[svelte-3142936818] .login-dialog-overlay{display:none}[svelte-3142936818].login-dialog-container.layout-compact .login-dialog-overlay,[svelte-3142936818] .login-dialog-container.layout-compact .login-dialog-overlay{display:block;position:absolute;top:0;left:0;bottom:0;right:0;background-color:rgba(0, 0, 0, 0.1);z-index:1}[svelte-3142936818].login-dialog,[svelte-3142936818] .login-dialog{display:flex;flex-direction:column}[svelte-3142936818].login-dialog-container.layout-compact .login-dialog,[svelte-3142936818] .login-dialog-container.layout-compact .login-dialog{padding:2em;box-shadow:0px 5px 36px 0px rgba(0,0,0,0.25);background-color:#fff;max-height:80vh;max-width:80vw;overflow:auto;z-index:2}[svelte-3142936818].login-dialog-container.layout-compact .login-dialog.loading,[svelte-3142936818] .login-dialog-container.layout-compact .login-dialog.loading{width:90vw;height:90vh;max-width:260px;max-height:100px;justify-content:center;align-items:center}[svelte-3142936818].login-dialog-title,[svelte-3142936818] .login-dialog-title{padding-top:0;margin-top:0}[svelte-3142936818].close-button-container,[svelte-3142936818] .close-button-container{text-align:right;margin-top:1em}";
		appendNode(style, document.head);
	}

	function create_main_fragment(state, component) {
		var text, if_block_1_anchor;

		var if_block = state.layout === 'compact' && create_if_block(state, component);

		var if_block_1 = (state.layout !== 'compact' || state.__showDialog) && create_if_block_3(state, component);

		return {
			c: function create() {
				if (if_block) if_block.c();
				text = createText("\n\n");
				if (if_block_1) if_block_1.c();
				if_block_1_anchor = createComment();
			},

			m: function mount(target, anchor) {
				if (if_block) if_block.m(target, anchor);
				insertNode(text, target, anchor);
				if (if_block_1) if_block_1.m(target, anchor);
				insertNode(if_block_1_anchor, target, anchor);
			},

			p: function update(changed, state) {
				if (state.layout === 'compact') {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block(state, component);
						if_block.c();
						if_block.m(text.parentNode, text);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (state.layout !== 'compact' || state.__showDialog) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_3(state, component);
						if_block_1.c();
						if_block_1.m(if_block_1_anchor.parentNode, if_block_1_anchor);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}
			},

			u: function unmount() {
				if (if_block) if_block.u();
				detachNode(text);
				if (if_block_1) if_block_1.u();
				detachNode(if_block_1_anchor);
			},

			d: function destroy() {
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
			}
		};
	}

	// (2:2) {{#if __successLoginMessage}}
	function create_if_block_1(state, component) {
		var text;

		return {
			c: function create() {
				text = createText(state.__successLoginMessage);
			},

			m: function mount(target, anchor) {
				insertNode(text, target, anchor);
			},

			p: function update(changed, state) {
				if (changed.__successLoginMessage) {
					text.data = state.__successLoginMessage;
				}
			},

			u: function unmount() {
				detachNode(text);
			},

			d: noop
		};
	}

	// (4:2) {{else}}
	function create_if_block_2(state, component) {
		var a, text;

		function click_handler(event) {
			component.set({ __showDialog: true });
		}

		return {
			c: function create() {
				a = createElement("a");
				text = createText(state.compactText);
				this.h();
			},

			h: function hydrate() {
				encapsulateStyles(a);
				a.href = "javascript:void(0);";
				addListener(a, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(a, target, anchor);
				appendNode(text, a);
			},

			p: function update(changed, state) {
				if (changed.compactText) {
					text.data = state.compactText;
				}
			},

			u: function unmount() {
				detachNode(a);
			},

			d: function destroy() {
				removeListener(a, "click", click_handler);
			}
		};
	}

	// (1:0) {{#if layout === 'compact'}}
	function create_if_block(state, component) {
		var if_block_anchor;

		var current_block_type = select_block_type(state);
		var if_block = current_block_type(state, component);

		return {
			c: function create() {
				if_block.c();
				if_block_anchor = createComment();
			},

			m: function mount(target, anchor) {
				if_block.m(target, anchor);
				insertNode(if_block_anchor, target, anchor);
			},

			p: function update(changed, state) {
				if (current_block_type === (current_block_type = select_block_type(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			},

			u: function unmount() {
				if_block.u();
				detachNode(if_block_anchor);
			},

			d: function destroy() {
				if_block.d();
			}
		};
	}

	// (20:10) {{#if __registering}}
	function create_if_block_5(state, component) {
		var text;

		return {
			c: function create() {
				text = createText("Registering...");
			},

			m: function mount(target, anchor) {
				insertNode(text, target, anchor);
			},

			u: function unmount() {
				detachNode(text);
			},

			d: noop
		};
	}

	// (23:10) {{#if __loggingin}}
	function create_if_block_6(state, component) {
		var text;

		return {
			c: function create() {
				text = createText("Logging in...");
			},

			m: function mount(target, anchor) {
				insertNode(text, target, anchor);
			},

			u: function unmount() {
				detachNode(text);
			},

			d: noop
		};
	}

	// (33:8) {{#if layout === 'compact'}}
	function create_if_block_8(state, component) {
		var div, button, button_class_value;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		return {
			c: function create() {
				div = createElement("div");
				button = createElement("button");
				button.textContent = "Close";
				this.h();
			},

			h: function hydrate() {
				button.className = button_class_value = "pf-button " + state.classButton;
				button.type = "button";
				addListener(button, "click", click_handler);
				div.className = "close-button-container";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(button, div);
			},

			p: function update(changed, state) {
				if (changed.classButton && button_class_value !== (button_class_value = "pf-button " + state.classButton)) {
					button.className = button_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (47:8) {{#if layout === 'compact'}}
	function create_if_block_11(state, component) {
		var div, h2, text;

		return {
			c: function create() {
				div = createElement("div");
				h2 = createElement("h2");
				text = createText(state.compactText);
				this.h();
			},

			h: function hydrate() {
				h2.className = "login-dialog-title";
				div.className = "pf-element pf-heading";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(h2, div);
				appendNode(text, h2);
			},

			p: function update(changed, state) {
				if (changed.compactText) {
					text.data = state.compactText;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: noop
		};
	}

	// (53:8) {{#if __clientConfig.allow_registration && showExistingUserCheckbox}}
	function create_if_block_12(state, component) {
		var div, span, label, input, input_class_value, text, span_class_value;

		function input_change_handler() {
			component.set({ existingUser: input.checked });
		}

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				label = createElement("label");
				input = createElement("input");
				text = createText(" I have an account.");
				this.h();
			},

			h: function hydrate() {
				addListener(input, "change", input_change_handler);
				input.className = input_class_value = "pf-field " + state.classCheckbox;
				input.type = "checkbox";
				span.className = span_class_value = state.layout !== 'small' ? 'pf-group' : '';
				setStyle(span, "display", state.layout !== 'small' ? 'block' : 'in-line');
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(label, span);
				appendNode(input, label);

				input.checked = state.existingUser;

				appendNode(text, label);
			},

			p: function update(changed, state) {
				input.checked = state.existingUser;
				if (changed.classCheckbox && input_class_value !== (input_class_value = "pf-field " + state.classCheckbox)) {
					input.className = input_class_value;
				}

				if (changed.layout && span_class_value !== (span_class_value = state.layout !== 'small' ? 'pf-group' : '')) {
					span.className = span_class_value;
				}

				if (changed.layout) {
					setStyle(span, "display", state.layout !== 'small' ? 'block' : 'in-line');
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(input, "change", input_change_handler);
			}
		};
	}

	// (65:14) {{#if __clientConfig.email_usernames}}
	function create_if_block_13(state, component) {
		var input,
		    input_updating = false,
		    input_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ username: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "email";
				input.name = "email";
				input.size = "24";
			},

			m: function mount(target, anchor) {
				insertNode(input, target, anchor);
				component.refs.username = input;

				input.value = state.username;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.username;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}
			},

			u: function unmount() {
				detachNode(input);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
				if (component.refs.username === input) component.refs.username = null;
			}
		};
	}

	// (67:14) {{else}}
	function create_if_block_14(state, component) {
		var input,
		    input_updating = false,
		    input_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ username: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "text";
				input.name = "username";
				input.size = "24";
			},

			m: function mount(target, anchor) {
				insertNode(input, target, anchor);
				component.refs.username = input;

				input.value = state.username;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.username;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}
			},

			u: function unmount() {
				detachNode(input);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
				if (component.refs.username === input) component.refs.username = null;
			}
		};
	}

	// (71:16) {{#if layout === 'small' || layout === 'compact'}}
	function create_if_block_16(state, component) {
		var br;

		return {
			c: function create() {
				br = createElement("br");
				this.h();
			},

			h: function hydrate() {
				br.className = "pf-clearing";
			},

			m: function mount(target, anchor) {
				insertNode(br, target, anchor);
			},

			u: function unmount() {
				detachNode(br);
			},

			d: noop
		};
	}

	// (74:16) {{#if __usernameVerifiedMessage}}
	function create_if_block_17(state, component) {
		var span, text;

		return {
			c: function create() {
				span = createElement("span");
				text = createText(state.__usernameVerifiedMessage);
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-field";
			},

			m: function mount(target, anchor) {
				insertNode(span, target, anchor);
				appendNode(text, span);
			},

			p: function update(changed, state) {
				if (changed.__usernameVerifiedMessage) {
					text.data = state.__usernameVerifiedMessage;
				}
			},

			u: function unmount() {
				detachNode(span);
			},

			d: noop
		};
	}

	// (70:14) {{#if !existingUser}}
	function create_if_block_15(state, component) {
		var text, if_block_1_anchor;

		var if_block = (state.layout === 'small' || state.layout === 'compact') && create_if_block_16(state, component);

		var if_block_1 = state.__usernameVerifiedMessage && create_if_block_17(state, component);

		return {
			c: function create() {
				if (if_block) if_block.c();
				text = createText("\n                ");
				if (if_block_1) if_block_1.c();
				if_block_1_anchor = createComment();
			},

			m: function mount(target, anchor) {
				if (if_block) if_block.m(target, anchor);
				insertNode(text, target, anchor);
				if (if_block_1) if_block_1.m(target, anchor);
				insertNode(if_block_1_anchor, target, anchor);
			},

			p: function update(changed, state) {
				if (state.layout === 'small' || state.layout === 'compact') {
					if (!if_block) {
						if_block = create_if_block_16(state, component);
						if_block.c();
						if_block.m(text.parentNode, text);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (state.__usernameVerifiedMessage) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_17(state, component);
						if_block_1.c();
						if_block_1.m(if_block_1_anchor.parentNode, if_block_1_anchor);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}
			},

			u: function unmount() {
				if (if_block) if_block.u();
				detachNode(text);
				if (if_block_1) if_block_1.u();
				detachNode(if_block_1_anchor);
			},

			d: function destroy() {
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
			}
		};
	}

	// (94:10) {{#if __clientConfig.reg_fields.indexOf('name') !== -1}}
	function create_if_block_19(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    input,
		    input_updating = false,
		    input_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ name: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Name";
				text_1 = createText("\n                ");
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "text";
				input.name = "name";
				input.size = "24";
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(input, label);

				input.value = state.name;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.name;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
			}
		};
	}

	// (102:10) {{#if !__clientConfig.email_usernames && __clientConfig.reg_fields.indexOf('email') !== -1}}
	function create_if_block_20(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    input,
		    input_updating = false,
		    input_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ email: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Email";
				text_1 = createText("\n                ");
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "email";
				input.name = "email";
				input.size = "24";
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(input, label);

				input.value = state.email;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.email;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
			}
		};
	}

	// (110:10) {{#if __clientConfig.reg_fields.indexOf('phone') !== -1}}
	function create_if_block_21(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    input,
		    input_updating = false,
		    input_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ phone: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Phone Number";
				text_1 = createText("\n                ");
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "tel";
				input.name = "phone";
				input.size = "24";
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(input, label);

				input.value = state.phone;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.phone;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
			}
		};
	}

	// (126:20) {{#each __clientConfig.timezones as tz}}
	function create_each_block(state, timezones, tz, tz_index, component) {
		var option,
		    text_value = tz,
		    text,
		    option_value_value;

		return {
			c: function create() {
				option = createElement("option");
				text = createText(text_value);
				this.h();
			},

			h: function hydrate() {
				option.__value = option_value_value = tz;
				option.value = option.__value;
			},

			m: function mount(target, anchor) {
				insertNode(option, target, anchor);
				appendNode(text, option);
			},

			p: function update(changed, state, timezones, tz, tz_index) {
				if (changed.__clientConfig && text_value !== (text_value = tz)) {
					text.data = text_value;
				}

				if (changed.__clientConfig && option_value_value !== (option_value_value = tz)) {
					option.__value = option_value_value;
				}

				option.value = option.__value;
			},

			u: function unmount() {
				detachNode(option);
			},

			d: noop
		};
	}

	// (118:10) {{#if __clientConfig.reg_fields.indexOf('timezone') !== -1}}
	function create_if_block_22(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    span_1,
		    text_3,
		    span_2,
		    select,
		    option,
		    text_4,
		    select_updating = false,
		    select_style_value,
		    span_2_class_value,
		    div_class_value;

		var timezones = state.__clientConfig.timezones;

		var each_blocks = [];

		for (var i = 0; i < timezones.length; i += 1) {
			each_blocks[i] = create_each_block(state, timezones, timezones[i], i, component);
		}

		function select_change_handler() {
			select_updating = true;
			component.set({ timezone: selectValue(select) });
			select_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Timezone";
				text_1 = createText("\n                ");
				span_1 = createElement("span");
				span_1.textContent = "This overrides the primary group's timezone.";
				text_3 = createText("\n                ");
				span_2 = createElement("span");
				select = createElement("select");
				option = createElement("option");
				text_4 = createText("--Default--");

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].c();
				}
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				span_1.className = "pf-note";
				option.__value = '';
				option.value = option.__value;
				addListener(select, "change", select_change_handler);
				if (!('timezone' in state)) component.root._beforecreate.push(select_change_handler);
				select.className = "pf-field";
				select.name = "timezone";
				select.style.cssText = select_style_value = state.layout === 'small' ? 'max-width: 95%;' : '';
				span_2.className = span_2_class_value = state.layout === 'compact' ? 'pf-group' : '';
				setStyle(span_2, "display", state.layout === 'compact' ? 'block' : 'in-line');
				div.className = div_class_value = "pf-element " + (state.layout === 'small' ? 'pf-full-width' : '');
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(span_1, label);
				appendNode(text_3, label);
				appendNode(span_2, label);
				appendNode(select, span_2);
				appendNode(option, select);
				appendNode(text_4, option);

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].m(select, null);
				}

				selectOption(select, state.timezone);
			},

			p: function update(changed, state) {
				var timezones = state.__clientConfig.timezones;

				if (changed.__clientConfig) {
					for (var i = 0; i < timezones.length; i += 1) {
						if (each_blocks[i]) {
							each_blocks[i].p(changed, state, timezones, timezones[i], i);
						} else {
							each_blocks[i] = create_each_block(state, timezones, timezones[i], i, component);
							each_blocks[i].c();
							each_blocks[i].m(select, null);
						}
					}

					for (; i < each_blocks.length; i += 1) {
						each_blocks[i].u();
						each_blocks[i].d();
					}
					each_blocks.length = timezones.length;
				}

				if (!select_updating) selectOption(select, state.timezone);
				if (changed.layout && select_style_value !== (select_style_value = state.layout === 'small' ? 'max-width: 95%;' : '')) {
					select.style.cssText = select_style_value;
				}

				if (changed.layout && span_2_class_value !== (span_2_class_value = state.layout === 'compact' ? 'pf-group' : '')) {
					span_2.className = span_2_class_value;
				}

				if (changed.layout) {
					setStyle(span_2, "display", state.layout === 'compact' ? 'block' : 'in-line');
				}

				if (changed.layout && div_class_value !== (div_class_value = "pf-element " + (state.layout === 'small' ? 'pf-full-width' : ''))) {
					div.className = div_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].u();
				}
			},

			d: function destroy() {
				destroyEach(each_blocks);

				removeListener(select, "change", select_change_handler);
			}
		};
	}

	// (158:20) {{#each states as state}}
	function create_each_block_1(state, states, state_1, state_index, component) {
		var option,
		    text_value = state_1[1],
		    text,
		    option_value_value;

		return {
			c: function create() {
				option = createElement("option");
				text = createText(text_value);
				this.h();
			},

			h: function hydrate() {
				option.__value = option_value_value = state_1[0];
				option.value = option.__value;
			},

			m: function mount(target, anchor) {
				insertNode(option, target, anchor);
				appendNode(text, option);
			},

			p: function update(changed, state, states, state_1, state_index) {
				if (changed.states && text_value !== (text_value = state_1[1])) {
					text.data = text_value;
				}

				if (changed.states && option_value_value !== (option_value_value = state_1[0])) {
					option.__value = option_value_value;
				}

				option.value = option.__value;
			},

			u: function unmount() {
				detachNode(option);
			},

			d: noop
		};
	}

	// (140:12) {{#if addressType === 'us'}}
	function create_if_block_24(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    input,
		    input_updating = false,
		    input_class_value,
		    text_4,
		    div_1,
		    label_1,
		    span_1,
		    text_6,
		    input_1,
		    input_1_updating = false,
		    input_1_class_value,
		    text_9,
		    div_2,
		    span_2,
		    text_11,
		    span_3,
		    input_2,
		    input_2_updating = false,
		    input_2_class_value,
		    input_2_size_value,
		    text_12,
		    select,
		    select_updating = false,
		    select_class_value,
		    select_style_value,
		    span_3_class_value,
		    span_3_style_value,
		    div_2_class_value,
		    text_15,
		    div_3,
		    label_2,
		    span_4,
		    text_17,
		    input_3,
		    input_3_updating = false,
		    input_3_class_value;

		function input_input_handler() {
			input_updating = true;
			component.set({ addressStreet: input.value });
			input_updating = false;
		}

		function input_1_input_handler() {
			input_1_updating = true;
			component.set({ addressStreet2: input_1.value });
			input_1_updating = false;
		}

		function input_2_input_handler() {
			input_2_updating = true;
			component.set({ addressCity: input_2.value });
			input_2_updating = false;
		}

		var states = state.states;

		var each_blocks = [];

		for (var i = 0; i < states.length; i += 1) {
			each_blocks[i] = create_each_block_1(state, states, states[i], i, component);
		}

		function select_change_handler() {
			select_updating = true;
			component.set({ addressState: selectValue(select) });
			select_updating = false;
		}

		function input_3_input_handler() {
			input_3_updating = true;
			component.set({ addressZip: input_3.value });
			input_3_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Address 1";
				text_1 = createText("\n                  ");
				input = createElement("input");
				text_4 = createText("\n              ");
				div_1 = createElement("div");
				label_1 = createElement("label");
				span_1 = createElement("span");
				span_1.textContent = "Address 2";
				text_6 = createText("\n                  ");
				input_1 = createElement("input");
				text_9 = createText("\n              ");
				div_2 = createElement("div");
				span_2 = createElement("span");
				span_2.textContent = "City, State";
				text_11 = createText("\n                ");
				span_3 = createElement("span");
				input_2 = createElement("input");
				text_12 = createText("\n                  ");
				select = createElement("select");

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].c();
				}

				text_15 = createText("\n              ");
				div_3 = createElement("div");
				label_2 = createElement("label");
				span_4 = createElement("span");
				span_4.textContent = "Zip";
				text_17 = createText("\n                  ");
				input_3 = createElement("input");
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "text";
				input.name = "street";
				input.size = "24";
				div.className = "pf-element";
				span_1.className = "pf-label";
				addListener(input_1, "input", input_1_input_handler);
				input_1.className = input_1_class_value = "pf-field " + state.classInput;
				input_1.type = "text";
				input_1.name = "street2";
				input_1.size = "24";
				div_1.className = "pf-element";
				span_2.className = "pf-label";
				addListener(input_2, "input", input_2_input_handler);
				input_2.className = input_2_class_value = "pf-field " + state.classInput;
				input_2.type = "text";
				input_2.name = "city";
				input_2.size = input_2_size_value = state.layout === 'small' ? '10' : '15';
				addListener(select, "change", select_change_handler);
				if (!('addressState' in state)) component.root._beforecreate.push(select_change_handler);
				select.className = select_class_value = "pf-field " + state.classSelect;
				select.name = "state";
				select.style.cssText = select_style_value = state.layout === 'small' ? 'max-width: 95%;' : '';
				span_3.className = span_3_class_value = state.layout === 'compact' ? 'pf-group' : '';
				span_3.style.cssText = span_3_style_value = state.layout === 'compact' ? 'white-space: nowrap; margin-right: 16px; display: block;' : 'display: in-line;';
				div_2.className = div_2_class_value = "pf-element " + (state.layout === 'small' ? 'pf-full-width' : '');
				span_4.className = "pf-label";
				addListener(input_3, "input", input_3_input_handler);
				input_3.className = input_3_class_value = "pf-field " + state.classInput;
				input_3.type = "text";
				input_3.name = "zip";
				input_3.size = "24";
				div_3.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(input, label);

				input.value = state.addressStreet;

				insertNode(text_4, target, anchor);
				insertNode(div_1, target, anchor);
				appendNode(label_1, div_1);
				appendNode(span_1, label_1);
				appendNode(text_6, label_1);
				appendNode(input_1, label_1);

				input_1.value = state.addressStreet2;

				insertNode(text_9, target, anchor);
				insertNode(div_2, target, anchor);
				appendNode(span_2, div_2);
				appendNode(text_11, div_2);
				appendNode(span_3, div_2);
				appendNode(input_2, span_3);

				input_2.value = state.addressCity;

				appendNode(text_12, span_3);
				appendNode(select, span_3);

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].m(select, null);
				}

				selectOption(select, state.addressState);

				insertNode(text_15, target, anchor);
				insertNode(div_3, target, anchor);
				appendNode(label_2, div_3);
				appendNode(span_4, label_2);
				appendNode(text_17, label_2);
				appendNode(input_3, label_2);

				input_3.value = state.addressZip;
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.addressStreet;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}

				if (!input_1_updating) input_1.value = state.addressStreet2;
				if (changed.classInput && input_1_class_value !== (input_1_class_value = "pf-field " + state.classInput)) {
					input_1.className = input_1_class_value;
				}

				if (!input_2_updating) input_2.value = state.addressCity;
				if (changed.classInput && input_2_class_value !== (input_2_class_value = "pf-field " + state.classInput)) {
					input_2.className = input_2_class_value;
				}

				if (changed.layout && input_2_size_value !== (input_2_size_value = state.layout === 'small' ? '10' : '15')) {
					input_2.size = input_2_size_value;
				}

				var states = state.states;

				if (changed.states) {
					for (var i = 0; i < states.length; i += 1) {
						if (each_blocks[i]) {
							each_blocks[i].p(changed, state, states, states[i], i);
						} else {
							each_blocks[i] = create_each_block_1(state, states, states[i], i, component);
							each_blocks[i].c();
							each_blocks[i].m(select, null);
						}
					}

					for (; i < each_blocks.length; i += 1) {
						each_blocks[i].u();
						each_blocks[i].d();
					}
					each_blocks.length = states.length;
				}

				if (!select_updating) selectOption(select, state.addressState);
				if (changed.classSelect && select_class_value !== (select_class_value = "pf-field " + state.classSelect)) {
					select.className = select_class_value;
				}

				if (changed.layout && select_style_value !== (select_style_value = state.layout === 'small' ? 'max-width: 95%;' : '')) {
					select.style.cssText = select_style_value;
				}

				if (changed.layout && span_3_class_value !== (span_3_class_value = state.layout === 'compact' ? 'pf-group' : '')) {
					span_3.className = span_3_class_value;
				}

				if (changed.layout && span_3_style_value !== (span_3_style_value = state.layout === 'compact' ? 'white-space: nowrap; margin-right: 16px; display: block;' : 'display: in-line;')) {
					span_3.style.cssText = span_3_style_value;
				}

				if (changed.layout && div_2_class_value !== (div_2_class_value = "pf-element " + (state.layout === 'small' ? 'pf-full-width' : ''))) {
					div_2.className = div_2_class_value;
				}

				if (!input_3_updating) input_3.value = state.addressZip;
				if (changed.classInput && input_3_class_value !== (input_3_class_value = "pf-field " + state.classInput)) {
					input_3.className = input_3_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
				detachNode(text_4);
				detachNode(div_1);
				detachNode(text_9);
				detachNode(div_2);

				for (var i = 0; i < each_blocks.length; i += 1) {
					each_blocks[i].u();
				}

				detachNode(text_15);
				detachNode(div_3);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
				removeListener(input_1, "input", input_1_input_handler);
				removeListener(input_2, "input", input_2_input_handler);

				destroyEach(each_blocks);

				removeListener(select, "change", select_change_handler);
				removeListener(input_3, "input", input_3_input_handler);
			}
		};
	}

	// (170:12) {{else}}
	function create_if_block_25(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    span_1,
		    span_2,
		    textarea,
		    textarea_updating = false;

		function textarea_input_handler() {
			textarea_updating = true;
			component.set({ addressInternational: textarea.value });
			textarea_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Address";
				text_1 = createText("\n                  ");
				span_1 = createElement("span");
				span_2 = createElement("span");
				textarea = createElement("textarea");
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(textarea, "input", textarea_input_handler);
				textarea.className = state.classTextarea;
				setStyle(textarea, "width", "100%");
				textarea.rows = "3";
				textarea.cols = "35";
				textarea.name = "address";
				span_2.className = "pf-field";
				setStyle(span_2, "display", "block");
				span_1.className = "pf-group pf-full-width";
				div.className = "pf-element pf-full-width";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(span_1, label);
				appendNode(span_2, span_1);
				appendNode(textarea, span_2);

				textarea.value = state.addressInternational;
			},

			p: function update(changed, state) {
				if (!textarea_updating) textarea.value = state.addressInternational;
				if (changed.classTextarea) {
					textarea.className = state.classTextarea;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				removeListener(textarea, "input", textarea_input_handler);
			}
		};
	}

	// (134:10) {{#if __clientConfig.reg_fields.indexOf('address') !== -1}}
	function create_if_block_23(state, component) {
		var div, span, text_1, label, input, input_class_value, text_2, text_3, label_1, input_1, input_1_class_value, text_4, text_6, if_block_anchor;

		function input_change_handler() {
			component.set({ addressType: input.__value });
		}

		function input_1_change_handler() {
			component.set({ addressType: input_1.__value });
		}

		var current_block_type = select_block_type_2(state);
		var if_block = current_block_type(state, component);

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				span.textContent = "Address Type";
				text_1 = createText("\n              ");
				label = createElement("label");
				input = createElement("input");
				text_2 = createText(" US");
				text_3 = createText("\n              ");
				label_1 = createElement("label");
				input_1 = createElement("input");
				text_4 = createText(" International");
				text_6 = createText("\n            ");
				if_block.c();
				if_block_anchor = createComment();
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				component._bindingGroups[0].push(input);
				addListener(input, "change", input_change_handler);
				input.className = input_class_value = "pf-field " + state.classRadio;
				input.type = "radio";
				input.name = "addressType";
				input.__value = "us";
				input.value = input.__value;
				component._bindingGroups[0].push(input_1);
				addListener(input_1, "change", input_1_change_handler);
				input_1.className = input_1_class_value = "pf-field " + state.classRadio;
				input_1.type = "radio";
				input_1.name = "addressType";
				input_1.__value = "international";
				input_1.value = input_1.__value;
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(text_1, div);
				appendNode(label, div);
				appendNode(input, label);

				input.checked = input.__value === state.addressType;

				appendNode(text_2, label);
				appendNode(text_3, div);
				appendNode(label_1, div);
				appendNode(input_1, label_1);

				input_1.checked = input_1.__value === state.addressType;

				appendNode(text_4, label_1);
				insertNode(text_6, target, anchor);
				if_block.m(target, anchor);
				insertNode(if_block_anchor, target, anchor);
			},

			p: function update(changed, state) {
				input.checked = input.__value === state.addressType;
				if (changed.classRadio && input_class_value !== (input_class_value = "pf-field " + state.classRadio)) {
					input.className = input_class_value;
				}

				input_1.checked = input_1.__value === state.addressType;
				if (changed.classRadio && input_1_class_value !== (input_1_class_value = "pf-field " + state.classRadio)) {
					input_1.className = input_1_class_value;
				}

				if (current_block_type === (current_block_type = select_block_type_2(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			},

			u: function unmount() {
				detachNode(div);
				detachNode(text_6);
				if_block.u();
				detachNode(if_block_anchor);
			},

			d: function destroy() {
				component._bindingGroups[0].splice(component._bindingGroups[0].indexOf(input), 1);
				removeListener(input, "change", input_change_handler);
				component._bindingGroups[0].splice(component._bindingGroups[0].indexOf(input_1), 1);
				removeListener(input_1, "change", input_1_change_handler);
				if_block.d();
			}
		};
	}

	// (87:8) {{#if __clientConfig.allow_registration && !existingUser}}
	function create_if_block_18(state, component) {
		var div,
		    label,
		    span,
		    text_1,
		    input,
		    input_updating = false,
		    input_class_value,
		    text_4,
		    text_5,
		    text_6,
		    text_7,
		    text_8,
		    if_block_4_anchor;

		function input_input_handler() {
			input_updating = true;
			component.set({ password2: input.value });
			input_updating = false;
		}

		var if_block = state.__clientConfig.reg_fields.indexOf('name') !== -1 && create_if_block_19(state, component);

		var if_block_1 = !state.__clientConfig.email_usernames && state.__clientConfig.reg_fields.indexOf('email') !== -1 && create_if_block_20(state, component);

		var if_block_2 = state.__clientConfig.reg_fields.indexOf('phone') !== -1 && create_if_block_21(state, component);

		var if_block_3 = state.__clientConfig.reg_fields.indexOf('timezone') !== -1 && create_if_block_22(state, component);

		var if_block_4 = state.__clientConfig.reg_fields.indexOf('address') !== -1 && create_if_block_23(state, component);

		return {
			c: function create() {
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Re-enter Password";
				text_1 = createText("\n              ");
				input = createElement("input");
				text_4 = createText("\n          ");
				if (if_block) if_block.c();
				text_5 = createText("\n          ");
				if (if_block_1) if_block_1.c();
				text_6 = createText("\n          ");
				if (if_block_2) if_block_2.c();
				text_7 = createText("\n          ");
				if (if_block_3) if_block_3.c();
				text_8 = createText("\n          ");
				if (if_block_4) if_block_4.c();
				if_block_4_anchor = createComment();
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "password";
				input.name = "password2";
				input.size = "24";
				div.className = "pf-element";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_1, label);
				appendNode(input, label);

				input.value = state.password2;

				insertNode(text_4, target, anchor);
				if (if_block) if_block.m(target, anchor);
				insertNode(text_5, target, anchor);
				if (if_block_1) if_block_1.m(target, anchor);
				insertNode(text_6, target, anchor);
				if (if_block_2) if_block_2.m(target, anchor);
				insertNode(text_7, target, anchor);
				if (if_block_3) if_block_3.m(target, anchor);
				insertNode(text_8, target, anchor);
				if (if_block_4) if_block_4.m(target, anchor);
				insertNode(if_block_4_anchor, target, anchor);
			},

			p: function update(changed, state) {
				if (!input_updating) input.value = state.password2;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}

				if (state.__clientConfig.reg_fields.indexOf('name') !== -1) {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_19(state, component);
						if_block.c();
						if_block.m(text_5.parentNode, text_5);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (!state.__clientConfig.email_usernames && state.__clientConfig.reg_fields.indexOf('email') !== -1) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_20(state, component);
						if_block_1.c();
						if_block_1.m(text_6.parentNode, text_6);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}

				if (state.__clientConfig.reg_fields.indexOf('phone') !== -1) {
					if (if_block_2) {
						if_block_2.p(changed, state);
					} else {
						if_block_2 = create_if_block_21(state, component);
						if_block_2.c();
						if_block_2.m(text_7.parentNode, text_7);
					}
				} else if (if_block_2) {
					if_block_2.u();
					if_block_2.d();
					if_block_2 = null;
				}

				if (state.__clientConfig.reg_fields.indexOf('timezone') !== -1) {
					if (if_block_3) {
						if_block_3.p(changed, state);
					} else {
						if_block_3 = create_if_block_22(state, component);
						if_block_3.c();
						if_block_3.m(text_8.parentNode, text_8);
					}
				} else if (if_block_3) {
					if_block_3.u();
					if_block_3.d();
					if_block_3 = null;
				}

				if (state.__clientConfig.reg_fields.indexOf('address') !== -1) {
					if (if_block_4) {
						if_block_4.p(changed, state);
					} else {
						if_block_4 = create_if_block_23(state, component);
						if_block_4.c();
						if_block_4.m(if_block_4_anchor.parentNode, if_block_4_anchor);
					}
				} else if (if_block_4) {
					if_block_4.u();
					if_block_4.d();
					if_block_4 = null;
				}
			},

			u: function unmount() {
				detachNode(div);
				detachNode(text_4);
				if (if_block) if_block.u();
				detachNode(text_5);
				if (if_block_1) if_block_1.u();
				detachNode(text_6);
				if (if_block_2) if_block_2.u();
				detachNode(text_7);
				if (if_block_3) if_block_3.u();
				detachNode(text_8);
				if (if_block_4) if_block_4.u();
				detachNode(if_block_4_anchor);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
				if (if_block_2) if_block_2.d();
				if (if_block_3) if_block_3.d();
				if (if_block_4) if_block_4.d();
			}
		};
	}

	// (185:8) {{#if __failureMessage}}
	function create_if_block_26(state, component) {
		var div, span, span_1, text;

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				span_1 = createElement("span");
				text = createText(state.__failureMessage);
				this.h();
			},

			h: function hydrate() {
				span_1.className = "pf-field";
				setStyle(span_1, "display", "block");
				span.className = "pf-group pf-full-width";
				div.className = "pf-element pf-full-width";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(span_1, span);
				appendNode(text, span_1);
			},

			p: function update(changed, state) {
				if (changed.__failureMessage) {
					text.data = state.__failureMessage;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: noop
		};
	}

	// (196:10) {{#if existingUser}}
	function create_if_block_27(state, component) {
		var button, button_class_value;

		function click_handler(event) {
			component.login();
		}

		return {
			c: function create() {
				button = createElement("button");
				button.textContent = "Log In";
				this.h();
			},

			h: function hydrate() {
				button.className = button_class_value = "pf-button " + state.classSubmit;
				button.type = "submit";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(button, target, anchor);
			},

			p: function update(changed, state) {
				if (changed.classSubmit && button_class_value !== (button_class_value = "pf-button " + state.classSubmit)) {
					button.className = button_class_value;
				}
			},

			u: function unmount() {
				detachNode(button);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (198:10) {{else}}
	function create_if_block_28(state, component) {
		var button, button_class_value;

		function click_handler(event) {
			component.register();
		}

		return {
			c: function create() {
				button = createElement("button");
				button.textContent = "Create Account";
				this.h();
			},

			h: function hydrate() {
				button.className = button_class_value = "pf-button " + state.classSubmit;
				button.type = "submit";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(button, target, anchor);
			},

			p: function update(changed, state) {
				if (changed.classSubmit && button_class_value !== (button_class_value = "pf-button " + state.classSubmit)) {
					button.className = button_class_value;
				}
			},

			u: function unmount() {
				detachNode(button);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (201:10) {{#if layout === 'compact'}}
	function create_if_block_29(state, component) {
		var button, button_class_value;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		return {
			c: function create() {
				button = createElement("button");
				button.textContent = "Close";
				this.h();
			},

			h: function hydrate() {
				button.className = button_class_value = "pf-button " + state.classButton;
				button.type = "button";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(button, target, anchor);
			},

			p: function update(changed, state) {
				if (changed.classButton && button_class_value !== (button_class_value = "pf-button " + state.classButton)) {
					button.className = button_class_value;
				}
			},

			u: function unmount() {
				detachNode(button);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (206:8) {{#if !hideRecovery && __clientConfig.pw_recovery && existingUser}}
	function create_if_block_30(state, component) {
		var div, span, span_1;

		var tilmeldrecover = new _TilmeldRecover2.default({
			root: component.root,
			data: {
				account: state.username,
				classInput: state.classInput,
				classRadio: state.classRadio,
				classSubmit: state.classSubmit,
				classButton: state.classButton
			}
		});

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				span_1 = createElement("span");
				tilmeldrecover._fragment.c();
				this.h();
			},

			h: function hydrate() {
				span_1.className = "pf-field";
				setStyle(span_1, "display", "block");
				span.className = "pf-group pf-full-width";
				div.className = "pf-element pf-full-width";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(span_1, span);
				tilmeldrecover._mount(span_1, null);
			},

			p: function update(changed, state) {
				var tilmeldrecover_changes = {};
				if (changed.username) tilmeldrecover_changes.account = state.username;
				if (changed.classInput) tilmeldrecover_changes.classInput = state.classInput;
				if (changed.classRadio) tilmeldrecover_changes.classRadio = state.classRadio;
				if (changed.classSubmit) tilmeldrecover_changes.classSubmit = state.classSubmit;
				if (changed.classButton) tilmeldrecover_changes.classButton = state.classButton;
				tilmeldrecover._set(tilmeldrecover_changes);
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				tilmeldrecover.destroy(false);
			}
		};
	}

	// (12:4) {{#if __registering || __loggingin}}
	function create_if_block_4(state, component) {
		var div, span, svg, path, animateTransform, text, text_1;

		var if_block = state.__registering && create_if_block_5(state, component);

		var if_block_1 = state.__loggingin && create_if_block_6(state, component);

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				svg = createSvgElement("svg");
				path = createSvgElement("path");
				animateTransform = createSvgElement("animateTransform");
				text = createText("\n          ");
				if (if_block) if_block.c();
				text_1 = createText("\n          ");
				if (if_block_1) if_block_1.c();
				this.h();
			},

			h: function hydrate() {
				setAttribute(animateTransform, "attributeName", "transform");
				setAttribute(animateTransform, "attributeType", "XML");
				setAttribute(animateTransform, "type", "rotate");
				setAttribute(animateTransform, "from", "0 150 150");
				setAttribute(animateTransform, "to", "360 150 150");
				setAttribute(animateTransform, "begin", "0s");
				setAttribute(animateTransform, "dur", "1s");
				setAttribute(animateTransform, "fill", "freeze");
				setAttribute(animateTransform, "repeatCount", "indefinite");
				setAttribute(path, "d", "M 150,0 a 150,150 0 0,1 106.066,256.066 l -35.355,-35.355 a -100,-100 0 0,0 -70.711,-170.711 z");
				setAttribute(path, "fill", "#000000");
				setStyle(svg, "display", "inline");
				setAttribute(svg, "width", "16");
				setAttribute(svg, "height", "16");
				setAttribute(svg, "viewBox", "0 0 300 300");
				setAttribute(svg, "xmlns", "http://www.w3.org/2000/svg");
				setAttribute(svg, "version", "1.1");
				div.className = "login-dialog loading";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(svg, span);
				appendNode(path, svg);
				appendNode(animateTransform, path);
				appendNode(text, span);
				if (if_block) if_block.m(span, null);
				appendNode(text_1, span);
				if (if_block_1) if_block_1.m(span, null);
			},

			p: function update(changed, state) {
				if (state.__registering) {
					if (!if_block) {
						if_block = create_if_block_5(state, component);
						if_block.c();
						if_block.m(span, text_1);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (state.__loggingin) {
					if (!if_block_1) {
						if_block_1 = create_if_block_6(state, component);
						if_block_1.c();
						if_block_1.m(span, null);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}
			},

			u: function unmount() {
				detachNode(div);
				if (if_block) if_block.u();
				if (if_block_1) if_block_1.u();
			},

			d: function destroy() {
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
			}
		};
	}

	// (28:41) 
	function create_if_block_7(state, component) {
		var div, div_1, text, text_2;

		var if_block = state.layout === 'compact' && create_if_block_8(state, component);

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText(state.__successRegisteredMessage);
				text_2 = createText("\n        ");
				if (if_block) if_block.c();
				this.h();
			},

			h: function hydrate() {
				div.className = "login-dialog";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div_1);
				appendNode(text_2, div);
				if (if_block) if_block.m(div, null);
			},

			p: function update(changed, state) {
				if (changed.__successRegisteredMessage) {
					text.data = state.__successRegisteredMessage;
				}

				if (state.layout === 'compact') {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_8(state, component);
						if_block.c();
						if_block.m(div, null);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}
			},

			u: function unmount() {
				detachNode(div);
				if (if_block) if_block.u();
			},

			d: function destroy() {
				if (if_block) if_block.d();
			}
		};
	}

	// (39:36) 
	function create_if_block_9(state, component) {
		var div, div_1, text;

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText(state.__successLoginMessage);
				this.h();
			},

			h: function hydrate() {
				div.className = "login-dialog";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div_1);
			},

			p: function update(changed, state) {
				if (changed.__successLoginMessage) {
					text.data = state.__successLoginMessage;
				}
			},

			u: function unmount() {
				detachNode(div);
			},

			d: noop
		};
	}

	// (45:4) {{else}}
	function create_if_block_10(state, component) {
		var form,
		    text,
		    text_1,
		    div,
		    label,
		    span,
		    text_2_value = state.__clientConfig.email_usernames ? 'Email' : 'Username',
		    text_2,
		    text_3,
		    span_1,
		    text_4,
		    span_1_class_value,
		    text_8,
		    div_1,
		    label_1,
		    span_2,
		    text_10,
		    input,
		    input_updating = false,
		    input_class_value,
		    text_13,
		    text_14,
		    text_15,
		    div_2,
		    text_16,
		    div_2_class_value,
		    text_18,
		    form_class_value;

		var if_block = state.layout === 'compact' && create_if_block_11(state, component);

		var if_block_1 = state.__clientConfig.allow_registration && state.showExistingUserCheckbox && create_if_block_12(state, component);

		var current_block_type = select_block_type_1(state);
		var if_block_2 = current_block_type(state, component);

		var if_block_3 = !state.existingUser && create_if_block_15(state, component);

		function input_input_handler() {
			input_updating = true;
			component.set({ password: input.value });
			input_updating = false;
		}

		var if_block_4 = state.__clientConfig.allow_registration && !state.existingUser && create_if_block_18(state, component);

		var if_block_5 = state.__failureMessage && create_if_block_26(state, component);

		var current_block_type_1 = select_block_type_3(state);
		var if_block_6 = current_block_type_1(state, component);

		var if_block_7 = state.layout === 'compact' && create_if_block_29(state, component);

		var if_block_8 = !state.hideRecovery && state.__clientConfig.pw_recovery && state.existingUser && create_if_block_30(state, component);

		return {
			c: function create() {
				form = createElement("form");
				if (if_block) if_block.c();
				text = createText("\n\n        ");
				if (if_block_1) if_block_1.c();
				text_1 = createText("\n\n        ");
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				text_2 = createText(text_2_value);
				text_3 = createText("\n            ");
				span_1 = createElement("span");
				if_block_2.c();
				text_4 = createText("\n              ");
				if (if_block_3) if_block_3.c();
				text_8 = createText("\n        ");
				div_1 = createElement("div");
				label_1 = createElement("label");
				span_2 = createElement("span");
				span_2.textContent = "Password";
				text_10 = createText("\n            ");
				input = createElement("input");
				text_13 = createText("\n        ");
				if (if_block_4) if_block_4.c();
				text_14 = createText("\n\n        ");
				if (if_block_5) if_block_5.c();
				text_15 = createText("\n\n        ");
				div_2 = createElement("div");
				if_block_6.c();
				text_16 = createText("\n          ");
				if (if_block_7) if_block_7.c();
				text_18 = createText("\n\n        ");
				if (if_block_8) if_block_8.c();
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				span_1.className = span_1_class_value = state.layout !== 'small' ? 'pf-group' : '';
				setStyle(span_1, "display", state.layout !== 'small' ? 'block' : 'in-line');
				div.className = "pf-element";
				span_2.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "password";
				input.name = "password";
				input.size = "24";
				div_1.className = "pf-element";
				div_2.className = div_2_class_value = "pf-element " + (state.layout === 'small' ? '' : 'pf-buttons');
				setAttribute(form, "onsubmit", "return false;");
				form.className = form_class_value = "login-dialog pf-form " + (state.layout === 'small' ? 'pf-layout-block' : '');
			},

			m: function mount(target, anchor) {
				insertNode(form, target, anchor);
				if (if_block) if_block.m(form, null);
				appendNode(text, form);
				if (if_block_1) if_block_1.m(form, null);
				appendNode(text_1, form);
				appendNode(div, form);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_2, span);
				appendNode(text_3, label);
				appendNode(span_1, label);
				if_block_2.m(span_1, null);
				appendNode(text_4, span_1);
				if (if_block_3) if_block_3.m(span_1, null);
				appendNode(text_8, form);
				appendNode(div_1, form);
				appendNode(label_1, div_1);
				appendNode(span_2, label_1);
				appendNode(text_10, label_1);
				appendNode(input, label_1);

				input.value = state.password;

				appendNode(text_13, form);
				if (if_block_4) if_block_4.m(form, null);
				appendNode(text_14, form);
				if (if_block_5) if_block_5.m(form, null);
				appendNode(text_15, form);
				appendNode(div_2, form);
				if_block_6.m(div_2, null);
				appendNode(text_16, div_2);
				if (if_block_7) if_block_7.m(div_2, null);
				appendNode(text_18, form);
				if (if_block_8) if_block_8.m(form, null);
				component.refs.form = form;
			},

			p: function update(changed, state) {
				if (state.layout === 'compact') {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_11(state, component);
						if_block.c();
						if_block.m(form, text);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (state.__clientConfig.allow_registration && state.showExistingUserCheckbox) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_12(state, component);
						if_block_1.c();
						if_block_1.m(form, text_1);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}

				if (changed.__clientConfig && text_2_value !== (text_2_value = state.__clientConfig.email_usernames ? 'Email' : 'Username')) {
					text_2.data = text_2_value;
				}

				if (current_block_type === (current_block_type = select_block_type_1(state)) && if_block_2) {
					if_block_2.p(changed, state);
				} else {
					if_block_2.u();
					if_block_2.d();
					if_block_2 = current_block_type(state, component);
					if_block_2.c();
					if_block_2.m(span_1, text_4);
				}

				if (!state.existingUser) {
					if (if_block_3) {
						if_block_3.p(changed, state);
					} else {
						if_block_3 = create_if_block_15(state, component);
						if_block_3.c();
						if_block_3.m(span_1, null);
					}
				} else if (if_block_3) {
					if_block_3.u();
					if_block_3.d();
					if_block_3 = null;
				}

				if (changed.layout && span_1_class_value !== (span_1_class_value = state.layout !== 'small' ? 'pf-group' : '')) {
					span_1.className = span_1_class_value;
				}

				if (changed.layout) {
					setStyle(span_1, "display", state.layout !== 'small' ? 'block' : 'in-line');
				}

				if (!input_updating) input.value = state.password;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}

				if (state.__clientConfig.allow_registration && !state.existingUser) {
					if (if_block_4) {
						if_block_4.p(changed, state);
					} else {
						if_block_4 = create_if_block_18(state, component);
						if_block_4.c();
						if_block_4.m(form, text_14);
					}
				} else if (if_block_4) {
					if_block_4.u();
					if_block_4.d();
					if_block_4 = null;
				}

				if (state.__failureMessage) {
					if (if_block_5) {
						if_block_5.p(changed, state);
					} else {
						if_block_5 = create_if_block_26(state, component);
						if_block_5.c();
						if_block_5.m(form, text_15);
					}
				} else if (if_block_5) {
					if_block_5.u();
					if_block_5.d();
					if_block_5 = null;
				}

				if (current_block_type_1 === (current_block_type_1 = select_block_type_3(state)) && if_block_6) {
					if_block_6.p(changed, state);
				} else {
					if_block_6.u();
					if_block_6.d();
					if_block_6 = current_block_type_1(state, component);
					if_block_6.c();
					if_block_6.m(div_2, text_16);
				}

				if (state.layout === 'compact') {
					if (if_block_7) {
						if_block_7.p(changed, state);
					} else {
						if_block_7 = create_if_block_29(state, component);
						if_block_7.c();
						if_block_7.m(div_2, null);
					}
				} else if (if_block_7) {
					if_block_7.u();
					if_block_7.d();
					if_block_7 = null;
				}

				if (changed.layout && div_2_class_value !== (div_2_class_value = "pf-element " + (state.layout === 'small' ? '' : 'pf-buttons'))) {
					div_2.className = div_2_class_value;
				}

				if (!state.hideRecovery && state.__clientConfig.pw_recovery && state.existingUser) {
					if (if_block_8) {
						if_block_8.p(changed, state);
					} else {
						if_block_8 = create_if_block_30(state, component);
						if_block_8.c();
						if_block_8.m(form, null);
					}
				} else if (if_block_8) {
					if_block_8.u();
					if_block_8.d();
					if_block_8 = null;
				}

				if (changed.layout && form_class_value !== (form_class_value = "login-dialog pf-form " + (state.layout === 'small' ? 'pf-layout-block' : ''))) {
					form.className = form_class_value;
				}
			},

			u: function unmount() {
				detachNode(form);
				if (if_block) if_block.u();
				if (if_block_1) if_block_1.u();
				if_block_2.u();
				if (if_block_3) if_block_3.u();
				if (if_block_4) if_block_4.u();
				if (if_block_5) if_block_5.u();
				if_block_6.u();
				if (if_block_7) if_block_7.u();
				if (if_block_8) if_block_8.u();
			},

			d: function destroy() {
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
				if_block_2.d();
				if (if_block_3) if_block_3.d();
				removeListener(input, "input", input_input_handler);
				if (if_block_4) if_block_4.d();
				if (if_block_5) if_block_5.d();
				if_block_6.d();
				if (if_block_7) if_block_7.d();
				if (if_block_8) if_block_8.d();
				if (component.refs.form === form) component.refs.form = null;
			}
		};
	}

	// (9:0) {{#if layout !== 'compact' || __showDialog}}
	function create_if_block_3(state, component) {
		var div, div_1, text, div_class_value;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		var current_block_type = select_block_type_4(state);
		var if_block = current_block_type(state, component);

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText("\n    ");
				if_block.c();
				this.h();
			},

			h: function hydrate() {
				encapsulateStyles(div);
				div_1.className = "login-dialog-overlay";
				addListener(div_1, "click", click_handler);
				div.className = div_class_value = "login-dialog-container layout-" + state.layout;
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div);
				if_block.m(div, null);
			},

			p: function update(changed, state) {
				if (current_block_type === (current_block_type = select_block_type_4(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(div, null);
				}

				if (changed.layout && div_class_value !== (div_class_value = "login-dialog-container layout-" + state.layout)) {
					div.className = div_class_value;
				}
			},

			u: function unmount() {
				detachNode(div);
				if_block.u();
			},

			d: function destroy() {
				removeListener(div_1, "click", click_handler);
				if_block.d();
			}
		};
	}

	function select_block_type(state) {
		if (state.__successLoginMessage) return create_if_block_1;
		return create_if_block_2;
	}

	function select_block_type_1(state) {
		if (state.__clientConfig.email_usernames) return create_if_block_13;
		return create_if_block_14;
	}

	function select_block_type_2(state) {
		if (state.addressType === 'us') return create_if_block_24;
		return create_if_block_25;
	}

	function select_block_type_3(state) {
		if (state.existingUser) return create_if_block_27;
		return create_if_block_28;
	}

	function select_block_type_4(state) {
		if (state.__registering || state.__loggingin) return create_if_block_4;
		if (state.__successRegisteredMessage) return create_if_block_7;
		if (state.__successLoginMessage) return create_if_block_9;
		return create_if_block_10;
	}

	function TilmeldLogin(options) {
		init(this, options);
		this.refs = {};
		this._state = assign(data(), options.data);
		this._recompute({ name: 1 }, this._state);
		this._bindingGroups = [[]];

		if (!document.getElementById("svelte-3142936818-style")) add_css();

		var _oncreate = oncreate.bind(this);

		if (!options.root) {
			this._oncreate = [_oncreate];
			this._beforecreate = [];
			this._aftercreate = [];
		} else {
			this.root._oncreate.push(_oncreate);
		}

		this._fragment = create_main_fragment(this._state, this);

		if (options.target) {
			this._fragment.c();
			this._fragment.m(options.target, options.anchor || null);

			this._lock = true;
			callAll(this._beforecreate);
			callAll(this._oncreate);
			callAll(this._aftercreate);
			this._lock = false;
		}
	}

	assign(TilmeldLogin.prototype, methods, {
		destroy: destroy,
		get: get,
		fire: fire,
		observe: observe,
		on: on,
		set: set,
		teardown: destroy,
		_set: _set,
		_mount: _mount,
		_unmount: _unmount
	});

	TilmeldLogin.prototype._recompute = function _recompute(changed, state) {
		if (changed.name) {
			if (differs(state.nameFirst, state.nameFirst = nameFirst(state.name))) changed.nameFirst = true;
			if (differs(state.nameLast, state.nameLast = nameLast(state.name))) changed.nameLast = true;
		}
	};

	function setAttribute(node, attribute, value) {
		node.setAttribute(attribute, value);
	}

	function createElement(name) {
		return document.createElement(name);
	}

	function appendNode(node, target) {
		target.appendChild(node);
	}

	function createText(data) {
		return document.createTextNode(data);
	}

	function createComment() {
		return document.createComment('');
	}

	function insertNode(node, target, anchor) {
		target.insertBefore(node, anchor);
	}

	function detachNode(node) {
		node.parentNode.removeChild(node);
	}

	function noop() {}

	function addListener(node, event, handler) {
		node.addEventListener(event, handler, false);
	}

	function removeListener(node, event, handler) {
		node.removeEventListener(event, handler, false);
	}

	function setStyle(node, key, value) {
		node.style.setProperty(key, value);
	}

	function selectValue(select) {
		var selectedOption = select.querySelector(':checked') || select.options[0];
		return selectedOption && selectedOption.__value;
	}

	function selectOption(select, value) {
		for (var i = 0; i < select.options.length; i += 1) {
			var option = select.options[i];

			if (option.__value === value) {
				option.selected = true;
				return;
			}
		}
	}

	function destroyEach(iterations) {
		for (var i = 0; i < iterations.length; i += 1) {
			if (iterations[i]) iterations[i].d();
		}
	}

	function createSvgElement(name) {
		return document.createElementNS('http://www.w3.org/2000/svg', name);
	}

	function init(component, options) {
		component._observers = { pre: blankObject(), post: blankObject() };
		component._handlers = blankObject();
		component._bind = options._bind;

		component.options = options;
		component.root = options.root || component;
		component.store = component.root.options.store;
	}

	function assign(target) {
		var k,
		    source,
		    i = 1,
		    len = arguments.length;
		for (; i < len; i++) {
			source = arguments[i];
			for (k in source) {
				target[k] = source[k];
			}
		}

		return target;
	}

	function callAll(fns) {
		while (fns && fns.length) {
			fns.pop()();
		}
	}

	function destroy(detach) {
		this.destroy = noop;
		this.fire('destroy');
		this.set = this.get = noop;

		if (detach !== false) this._fragment.u();
		this._fragment.d();
		this._fragment = this._state = null;
	}

	function get(key) {
		return key ? this._state[key] : this._state;
	}

	function fire(eventName, data) {
		var handlers = eventName in this._handlers && this._handlers[eventName].slice();
		if (!handlers) return;

		for (var i = 0; i < handlers.length; i += 1) {
			handlers[i].call(this, data);
		}
	}

	function observe(key, callback, options) {
		var group = options && options.defer ? this._observers.post : this._observers.pre;

		(group[key] || (group[key] = [])).push(callback);

		if (!options || options.init !== false) {
			callback.__calling = true;
			callback.call(this, this._state[key]);
			callback.__calling = false;
		}

		return {
			cancel: function cancel() {
				var index = group[key].indexOf(callback);
				if (~index) group[key].splice(index, 1);
			}
		};
	}

	function on(eventName, handler) {
		if (eventName === 'teardown') return this.on('destroy', handler);

		var handlers = this._handlers[eventName] || (this._handlers[eventName] = []);
		handlers.push(handler);

		return {
			cancel: function cancel() {
				var index = handlers.indexOf(handler);
				if (~index) handlers.splice(index, 1);
			}
		};
	}

	function set(newState) {
		this._set(assign({}, newState));
		if (this.root._lock) return;
		this.root._lock = true;
		callAll(this.root._beforecreate);
		callAll(this.root._oncreate);
		callAll(this.root._aftercreate);
		this.root._lock = false;
	}

	function _set(newState) {
		var oldState = this._state,
		    changed = {},
		    dirty = false;

		for (var key in newState) {
			if (differs(newState[key], oldState[key])) changed[key] = dirty = true;
		}
		if (!dirty) return;

		this._state = assign({}, oldState, newState);
		this._recompute(changed, this._state);
		if (this._bind) this._bind(changed, this._state);

		if (this._fragment) {
			dispatchObservers(this, this._observers.pre, changed, this._state, oldState);
			this._fragment.p(changed, this._state);
			dispatchObservers(this, this._observers.post, changed, this._state, oldState);
		}
	}

	function _mount(target, anchor) {
		this._fragment.m(target, anchor);
	}

	function _unmount() {
		if (this._fragment) this._fragment.u();
	}

	function differs(a, b) {
		return a !== b || a && (typeof a === 'undefined' ? 'undefined' : _typeof(a)) === 'object' || typeof a === 'function';
	}

	function blankObject() {
		return Object.create(null);
	}

	function dispatchObservers(component, group, changed, newState, oldState) {
		for (var key in group) {
			if (!changed[key]) continue;

			var newValue = newState[key];
			var oldValue = oldState[key];

			var callbacks = group[key];
			if (!callbacks) continue;

			for (var i = 0; i < callbacks.length; i += 1) {
				var callback = callbacks[i];
				if (callback.__calling) continue;

				callback.__calling = true;
				callback.call(component, newValue, oldValue);
				callback.__calling = false;
			}
		}
	}
	exports.default = TilmeldLogin;
});
