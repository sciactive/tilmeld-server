(function (global, factory) {
	if (typeof define === "function" && define.amd) {
		define(['exports', 'Nymph', 'User'], factory);
	} else if (typeof exports !== "undefined") {
		factory(exports, require('Nymph'), require('User'));
	} else {
		var mod = {
			exports: {}
		};
		factory(mod.exports, global.Nymph, global.User);
		global.TilmeldRecover = mod.exports;
	}
})(this, function (exports, _Nymph, _User) {
	'use strict';

	Object.defineProperty(exports, "__esModule", {
		value: true
	});

	var _Nymph2 = _interopRequireDefault(_Nymph);

	var _User2 = _interopRequireDefault(_User);

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

	function data() {
		return {
			linkText: 'I can\'t access my account.', // The text used to toggle the dialog.
			autofocus: true, // Give focus to the account box when the form is ready.
			recoveryType: 'password',
			__clientConfig: {
				'reg_fields': [],
				'email_usernames': true,
				'allow_registration': true,
				'pw_recovery': true,
				'timezones': []
			},
			__showDialog: false,
			__recovering: false,
			__hasSentSecret: false,

			// These are all user provided details.
			account: '',
			secret: '',
			password: '',
			password2: ''
		};
	};

	var methods = {
		sendRecoveryLink: function sendRecoveryLink() {
			var _this = this;

			if (this.get('account') === '') {
				this.set({ __failureMessage: 'You need to enter ' + (this.get('__clientConfig').email_usernames || this.get('recoveryType') === 'username' ? 'an email address' : 'a username') + '.' });
				return;
			}

			this.set({
				__failureMessage: null,
				__recovering: true
			});
			_User2.default.sendRecoveryLink({ 'recoveryType': this.get('recoveryType'), 'account': this.get('account') }).then(function (data) {
				if (!data.result) {
					_this.set({ __failureMessage: data.message });
				} else {
					if (_this.get('recoveryType') === 'username') {
						_this.set({ __successRecoveredMessage: data.message });
					} else if (_this.get('recoveryType') === 'password') {
						_this.set({ __hasSentSecret: true });
					}
				}
				_this.set({ __recovering: false });
			}, function () {
				_this.set({
					__failureMessage: 'An error occurred.',
					__recovering: false
				});
			});
		},
		recover: function recover() {
			var _this2 = this;

			if (this.get('account') === '') {
				this.set({ __failureMessage: 'You need to enter ' + (this.get('__clientConfig').email_usernames || this.get('recoveryType') === 'username' ? 'an email address' : 'a username') + '.' });
				return;
			}
			if (this.get('password') != this.get('password2')) {
				this.set({ __failureMessage: 'Your passwords do not match.' });
				return;
			}
			if (this.get('password') === '') {
				this.set({ __failureMessage: 'You need to enter a password.' });
				return;
			}

			this.set({
				__failureMessage: null,
				__recovering: true
			});
			_User2.default.recover({ 'username': this.get('account'), 'secret': this.get('secret'), 'password': this.get('password') }).then(function (data) {
				if (!data.result) {
					_this2.set({ __failureMessage: data.message });
				} else {
					_this2.set({ __successRecoveredMessage: data.message });
				}
				_this2.set({ __recovering: false });
			}, function () {
				_this2.set({
					__failureMessage: 'An error occurred.',
					__recovering: false
				});
			});
		}
	};

	function oncreate() {
		var _this3 = this;

		_User2.default.getClientConfig().then(function (__clientConfig) {
			_this3.set({ __clientConfig: __clientConfig });
		});

		this.observe('__showDialog', function (value) {
			if (value && _this3.get('autofocus') && _this3.refs.account) {
				_this3.refs.account.focus();
			}
		});
	};

	function encapsulateStyles(node) {
		setAttribute(node, "svelte-1453595330", "");
	}

	function add_css() {
		var style = createElement("style");
		style.id = 'svelte-1453595330-style';
		style.textContent = "[svelte-1453595330].recovery-dialog-container,[svelte-1453595330] .recovery-dialog-container{display:flex;align-items:center}[svelte-1453595330].recovery-dialog-container.layout-compact,[svelte-1453595330] .recovery-dialog-container.layout-compact{justify-content:center;position:fixed;top:0;left:0;bottom:0;right:0;z-index:1000}[svelte-1453595330].recovery-dialog-overlay,[svelte-1453595330] .recovery-dialog-overlay{display:none}[svelte-1453595330].recovery-dialog-container.layout-compact .recovery-dialog-overlay,[svelte-1453595330] .recovery-dialog-container.layout-compact .recovery-dialog-overlay{display:block;position:absolute;top:0;left:0;bottom:0;right:0;background-color:rgba(0, 0, 0, 0.1);z-index:1}[svelte-1453595330].recovery-dialog,[svelte-1453595330] .recovery-dialog{display:flex;flex-direction:column}[svelte-1453595330].recovery-dialog-container.layout-compact .recovery-dialog,[svelte-1453595330] .recovery-dialog-container.layout-compact .recovery-dialog{padding:2em;box-shadow:0px 5px 36px 0px rgba(0,0,0,0.25);background-color:#fff;max-height:80vh;max-width:80vw;overflow:auto;z-index:2}[svelte-1453595330].recovery-dialog-container.layout-compact .recovery-dialog.loading,[svelte-1453595330] .recovery-dialog-container.layout-compact .recovery-dialog.loading{width:90vw;height:90vh;max-width:260px;max-height:100px;justify-content:center;align-items:center}[svelte-1453595330].recovery-dialog-title,[svelte-1453595330] .recovery-dialog-title{padding-top:0;margin-top:0}[svelte-1453595330].close-button-container,[svelte-1453595330] .close-button-container{text-align:right;margin-top:1em}";
		appendNode(style, document.head);
	}

	function create_main_fragment(state, component) {
		var a, text, text_1, if_block_anchor;

		function click_handler(event) {
			component.set({ __showDialog: true });
		}

		var if_block = state.__showDialog && create_if_block(state, component);

		return {
			c: function create() {
				a = createElement("a");
				text = createText(state.linkText);
				text_1 = createText("\n\n");
				if (if_block) if_block.c();
				if_block_anchor = createComment();
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
				insertNode(text_1, target, anchor);
				if (if_block) if_block.m(target, anchor);
				insertNode(if_block_anchor, target, anchor);
			},

			p: function update(changed, state) {
				if (changed.linkText) {
					text.data = state.linkText;
				}

				if (state.__showDialog) {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block(state, component);
						if_block.c();
						if_block.m(if_block_anchor.parentNode, if_block_anchor);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}
			},

			u: function unmount() {
				detachNode(a);
				detachNode(text_1);
				if (if_block) if_block.u();
				detachNode(if_block_anchor);
			},

			d: function destroy() {
				removeListener(a, "click", click_handler);
				if (if_block) if_block.d();
			}
		};
	}

	// (32:10) {{#if !__clientConfig.email_usernames}}
	function create_if_block_5(state, component) {
		var div, span, text_1, label, input, text_2, text_3, label_1, input_1, text_4;

		function input_change_handler() {
			if (!input.checked) return;
			component.set({ recoveryType: input.__value });
		}

		function input_1_change_handler() {
			if (!input_1.checked) return;
			component.set({ recoveryType: input_1.__value });
		}

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				span.textContent = "Recovery Type";
				text_1 = createText("\n              ");
				label = createElement("label");
				input = createElement("input");
				text_2 = createText(" I forgot my password.");
				text_3 = createText("\n              ");
				label_1 = createElement("label");
				input_1 = createElement("input");
				text_4 = createText(" I forgot my username.");
				this.h();
			},

			h: function hydrate() {
				div.className = "pf-element";
				span.className = "pf-label";
				input.className = "pf-field";
				input.type = "radio";
				input.name = "type";
				input.__value = "password";
				input.value = input.__value;
				component._bindingGroups[0].push(input);
				addListener(input, "change", input_change_handler);
				input_1.className = "pf-field";
				input_1.type = "radio";
				input_1.name = "type";
				input_1.__value = "username";
				input_1.value = input_1.__value;
				component._bindingGroups[0].push(input_1);
				addListener(input_1, "change", input_1_change_handler);
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(text_1, div);
				appendNode(label, div);
				appendNode(input, label);

				input.checked = input.__value === state.recoveryType;

				appendNode(text_2, label);
				appendNode(text_3, div);
				appendNode(label_1, div);
				appendNode(input_1, label_1);

				input_1.checked = input_1.__value === state.recoveryType;

				appendNode(text_4, label_1);
			},

			p: function update(changed, state) {
				input.checked = input.__value === state.recoveryType;

				input_1.checked = input_1.__value === state.recoveryType;
			},

			u: function unmount() {
				detachNode(div);
			},

			d: function destroy() {
				component._bindingGroups[0].splice(component._bindingGroups[0].indexOf(input), 1);

				removeListener(input, "change", input_change_handler);

				component._bindingGroups[0].splice(component._bindingGroups[0].indexOf(input_1), 1);

				removeListener(input_1, "change", input_1_change_handler);
			}
		};
	}

	// (40:12) {{#if recoveryType === 'password'}}
	function create_if_block_6(state, component) {
		var p,
		    text,
		    text_1_value = state.__clientConfig.email_usernames ? 'email' : 'username',
		    text_1,
		    text_2;

		return {
			c: function create() {
				p = createElement("p");
				text = createText("To reset your password, type the ");
				text_1 = createText(text_1_value);
				text_2 = createText(" you use to sign in below.");
			},

			m: function mount(target, anchor) {
				insertNode(p, target, anchor);
				appendNode(text, p);
				appendNode(text_1, p);
				appendNode(text_2, p);
			},

			p: function update(changed, state) {
				if (changed.__clientConfig && text_1_value !== (text_1_value = state.__clientConfig.email_usernames ? 'email' : 'username')) {
					text_1.data = text_1_value;
				}
			},

			u: function unmount() {
				detachNode(p);
			},

			d: noop
		};
	}

	// (43:12) {{#if recoveryType === 'username'}}
	function create_if_block_7(state, component) {
		var p;

		return {
			c: function create() {
				p = createElement("p");
				p.textContent = "To get your username, type your email as you entered it when creating your account.";
			},

			m: function mount(target, anchor) {
				insertNode(p, target, anchor);
			},

			u: function unmount() {
				detachNode(p);
			},

			d: noop
		};
	}

	// (49:14) {{#if recoveryType === 'password'}}
	function create_if_block_8(state, component) {
		var span,
		    text_value = state.__clientConfig.email_usernames ? 'Email Address' : 'Username',
		    text;

		return {
			c: function create() {
				span = createElement("span");
				text = createText(text_value);
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
			},

			m: function mount(target, anchor) {
				insertNode(span, target, anchor);
				appendNode(text, span);
			},

			p: function update(changed, state) {
				if (changed.__clientConfig && text_value !== (text_value = state.__clientConfig.email_usernames ? 'Email Address' : 'Username')) {
					text.data = text_value;
				}
			},

			u: function unmount() {
				detachNode(span);
			},

			d: noop
		};
	}

	// (52:14) {{#if recoveryType === 'username'}}
	function create_if_block_9(state, component) {
		var span;

		return {
			c: function create() {
				span = createElement("span");
				span.textContent = "Email Address";
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
			},

			m: function mount(target, anchor) {
				insertNode(span, target, anchor);
			},

			u: function unmount() {
				detachNode(span);
			},

			d: noop
		};
	}

	// (31:8) {{#if !__hasSentSecret}}
	function create_if_block_4(state, component) {
		var text,
		    div,
		    text_1,
		    text_3,
		    div_1,
		    label,
		    text_4,
		    text_5,
		    input,
		    input_updating = false;

		var if_block = !state.__clientConfig.email_usernames && create_if_block_5(state, component);

		var if_block_1 = state.recoveryType === 'password' && create_if_block_6(state, component);

		var if_block_2 = state.recoveryType === 'username' && create_if_block_7(state, component);

		var if_block_3 = state.recoveryType === 'password' && create_if_block_8(state, component);

		var if_block_4 = state.recoveryType === 'username' && create_if_block_9(state, component);

		function input_input_handler() {
			input_updating = true;
			component.set({ account: input.value });
			input_updating = false;
		}

		return {
			c: function create() {
				if (if_block) if_block.c();
				text = createText("\n          ");
				div = createElement("div");
				if (if_block_1) if_block_1.c();
				text_1 = createText("\n            ");
				if (if_block_2) if_block_2.c();
				text_3 = createText("\n          ");
				div_1 = createElement("div");
				label = createElement("label");
				if (if_block_3) if_block_3.c();
				text_4 = createText("\n              ");
				if (if_block_4) if_block_4.c();
				text_5 = createText("\n              ");
				input = createElement("input");
				this.h();
			},

			h: function hydrate() {
				div.className = "pf-element";
				div_1.className = "pf-element";
				input.className = "pf-field";
				input.type = "text";
				input.size = "24";
				addListener(input, "input", input_input_handler);
			},

			m: function mount(target, anchor) {
				if (if_block) if_block.m(target, anchor);
				insertNode(text, target, anchor);
				insertNode(div, target, anchor);
				if (if_block_1) if_block_1.m(div, null);
				appendNode(text_1, div);
				if (if_block_2) if_block_2.m(div, null);
				insertNode(text_3, target, anchor);
				insertNode(div_1, target, anchor);
				appendNode(label, div_1);
				if (if_block_3) if_block_3.m(label, null);
				appendNode(text_4, label);
				if (if_block_4) if_block_4.m(label, null);
				appendNode(text_5, label);
				appendNode(input, label);
				component.refs.account = input;

				input.value = state.account;
			},

			p: function update(changed, state) {
				if (!state.__clientConfig.email_usernames) {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_5(state, component);
						if_block.c();
						if_block.m(text.parentNode, text);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (state.recoveryType === 'password') {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_6(state, component);
						if_block_1.c();
						if_block_1.m(div, text_1);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}

				if (state.recoveryType === 'username') {
					if (!if_block_2) {
						if_block_2 = create_if_block_7(state, component);
						if_block_2.c();
						if_block_2.m(div, null);
					}
				} else if (if_block_2) {
					if_block_2.u();
					if_block_2.d();
					if_block_2 = null;
				}

				if (state.recoveryType === 'password') {
					if (if_block_3) {
						if_block_3.p(changed, state);
					} else {
						if_block_3 = create_if_block_8(state, component);
						if_block_3.c();
						if_block_3.m(label, text_4);
					}
				} else if (if_block_3) {
					if_block_3.u();
					if_block_3.d();
					if_block_3 = null;
				}

				if (state.recoveryType === 'username') {
					if (!if_block_4) {
						if_block_4 = create_if_block_9(state, component);
						if_block_4.c();
						if_block_4.m(label, text_5);
					}
				} else if (if_block_4) {
					if_block_4.u();
					if_block_4.d();
					if_block_4 = null;
				}

				if (!input_updating) {
					input.value = state.account;
				}
			},

			u: function unmount() {
				if (if_block) if_block.u();
				detachNode(text);
				detachNode(div);
				if (if_block_1) if_block_1.u();
				if (if_block_2) if_block_2.u();
				detachNode(text_3);
				detachNode(div_1);
				if (if_block_3) if_block_3.u();
				if (if_block_4) if_block_4.u();
			},

			d: function destroy() {
				if (if_block) if_block.d();
				if (if_block_1) if_block_1.d();
				if (if_block_2) if_block_2.d();
				if (if_block_3) if_block_3.d();
				if (if_block_4) if_block_4.d();
				removeListener(input, "input", input_input_handler);
				if (component.refs.account === input) component.refs.account = null;
			}
		};
	}

	// (58:8) {{else}}
	function create_if_block_10(state, component) {
		var div,
		    text_2,
		    div_1,
		    label,
		    span,
		    text_4,
		    input,
		    input_updating = false,
		    text_7,
		    div_2,
		    label_1,
		    span_1,
		    text_9,
		    input_1,
		    input_1_updating = false,
		    text_12,
		    div_3,
		    label_2,
		    span_2,
		    text_14,
		    input_2,
		    input_2_updating = false;

		function input_input_handler() {
			input_updating = true;
			component.set({ secret: input.value });
			input_updating = false;
		}

		function input_1_input_handler() {
			input_1_updating = true;
			component.set({ password: input_1.value });
			input_1_updating = false;
		}

		function input_2_input_handler() {
			input_2_updating = true;
			component.set({ password2: input_2.value });
			input_2_updating = false;
		}

		return {
			c: function create() {
				div = createElement("div");
				div.innerHTML = "<p>A code has been sent to you by email. Enter that code here, and a new password for your account.</p>";
				text_2 = createText("\n          ");
				div_1 = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Recovery Code";
				text_4 = createText("\n              ");
				input = createElement("input");
				text_7 = createText("\n          ");
				div_2 = createElement("div");
				label_1 = createElement("label");
				span_1 = createElement("span");
				span_1.textContent = "Password";
				text_9 = createText("\n              ");
				input_1 = createElement("input");
				text_12 = createText("\n          ");
				div_3 = createElement("div");
				label_2 = createElement("label");
				span_2 = createElement("span");
				span_2.textContent = "Re-enter Password";
				text_14 = createText("\n              ");
				input_2 = createElement("input");
				this.h();
			},

			h: function hydrate() {
				div.className = "pf-element";
				div_1.className = "pf-element";
				span.className = "pf-label";
				input.className = "pf-field";
				input.type = "text";
				input.size = "24";
				addListener(input, "input", input_input_handler);
				div_2.className = "pf-element";
				span_1.className = "pf-label";
				input_1.className = "pf-field";
				input_1.type = "password";
				input_1.size = "24";
				addListener(input_1, "input", input_1_input_handler);
				div_3.className = "pf-element";
				span_2.className = "pf-label";
				input_2.className = "pf-field";
				input_2.type = "password";
				input_2.size = "24";
				addListener(input_2, "input", input_2_input_handler);
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				insertNode(text_2, target, anchor);
				insertNode(div_1, target, anchor);
				appendNode(label, div_1);
				appendNode(span, label);
				appendNode(text_4, label);
				appendNode(input, label);

				input.value = state.secret;

				insertNode(text_7, target, anchor);
				insertNode(div_2, target, anchor);
				appendNode(label_1, div_2);
				appendNode(span_1, label_1);
				appendNode(text_9, label_1);
				appendNode(input_1, label_1);

				input_1.value = state.password;

				insertNode(text_12, target, anchor);
				insertNode(div_3, target, anchor);
				appendNode(label_2, div_3);
				appendNode(span_2, label_2);
				appendNode(text_14, label_2);
				appendNode(input_2, label_2);

				input_2.value = state.password2;
			},

			p: function update(changed, state) {
				if (!input_updating) {
					input.value = state.secret;
				}

				if (!input_1_updating) {
					input_1.value = state.password;
				}

				if (!input_2_updating) {
					input_2.value = state.password2;
				}
			},

			u: function unmount() {
				detachNode(div);
				detachNode(text_2);
				detachNode(div_1);
				detachNode(text_7);
				detachNode(div_2);
				detachNode(text_12);
				detachNode(div_3);
			},

			d: function destroy() {
				removeListener(input, "input", input_input_handler);
				removeListener(input_1, "input", input_1_input_handler);
				removeListener(input_2, "input", input_2_input_handler);
			}
		};
	}

	// (84:8) {{#if __failureMessage}}
	function create_if_block_11(state, component) {
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
				div.className = "pf-element pf-full-width";
				span.className = "pf-group pf-full-width";
				span_1.className = "pf-field";
				setStyle(span_1, "display", "block");
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

	// (95:10) {{#if !__hasSentSecret}}
	function create_if_block_12(state, component) {
		var button;

		function click_handler(event) {
			component.sendRecoveryLink();
		}

		return {
			c: function create() {
				button = createElement("button");
				button.textContent = "Send Recovery Link";
				this.h();
			},

			h: function hydrate() {
				button.className = "pf-button";
				button.type = "submit";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(button, target, anchor);
			},

			u: function unmount() {
				detachNode(button);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (97:10) {{else}}
	function create_if_block_13(state, component) {
		var button;

		function click_handler(event) {
			component.recover();
		}

		return {
			c: function create() {
				button = createElement("button");
				button.textContent = "Reset Password";
				this.h();
			},

			h: function hydrate() {
				button.className = "pf-button";
				button.type = "submit";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(button, target, anchor);
			},

			u: function unmount() {
				detachNode(button);
			},

			d: function destroy() {
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (6:4) {{#if __recovering}}
	function create_if_block_1(state, component) {
		var div;

		return {
			c: function create() {
				div = createElement("div");
				div.innerHTML = "<span><svg style=\"display: inline;\" width=\"16\" height=\"16\" viewBox=\"0 0 300 300\" xmlns=\"http://www.w3.org/2000/svg\" version=\"1.1\"><path d=\"M 150,0 a 150,150 0 0,1 106.066,256.066 l -35.355,-35.355 a -100,-100 0 0,0 -70.711,-170.711 z\" fill=\"#000000\"><animateTransform attributeName=\"transform\" attributeType=\"XML\" type=\"rotate\" from=\"0 150 150\" to=\"360 150 150\" begin=\"0s\" dur=\"1s\" fill=\"freeze\" repeatCount=\"indefinite\"></animateTransform>\n            </path>\n          </svg>\n          This will just take a second...</span>";
				this.h();
			},

			h: function hydrate() {
				div.className = "recovery-dialog loading";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
			},

			p: noop,

			u: function unmount() {
				detachNode(div);
			},

			d: noop
		};
	}

	// (17:40) 
	function create_if_block_2(state, component) {
		var div, div_1, text, text_2, div_2, button;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText(state.__successRecoveredMessage);
				text_2 = createText("\n        ");
				div_2 = createElement("div");
				button = createElement("button");
				button.textContent = "Close";
				this.h();
			},

			h: function hydrate() {
				div.className = "recovery-dialog";
				div_2.className = "close-button-container";
				button.className = "pf-button";
				button.type = "button";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div_1);
				appendNode(text_2, div);
				appendNode(div_2, div);
				appendNode(button, div_2);
			},

			p: function update(changed, state) {
				if (changed.__successRecoveredMessage) {
					text.data = state.__successRecoveredMessage;
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

	// (26:4) {{else}}
	function create_if_block_3(state, component) {
		var form, div, text_2, text_3, text_4, div_1, text_5, button;

		var current_block_type = select_block_type(state);
		var if_block = current_block_type(state, component);

		var if_block_1 = state.__failureMessage && create_if_block_11(state, component);

		var current_block_type_1 = select_block_type_1(state);
		var if_block_2 = current_block_type_1(state, component);

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		return {
			c: function create() {
				form = createElement("form");
				div = createElement("div");
				div.innerHTML = "<h2 class=\"recovery-dialog-title\">Account Recovery</h2>";
				text_2 = createText("\n        ");
				if_block.c();
				text_3 = createText("\n\n        ");
				if (if_block_1) if_block_1.c();
				text_4 = createText("\n\n        ");
				div_1 = createElement("div");
				if_block_2.c();
				text_5 = createText("\n          ");
				button = createElement("button");
				button.textContent = "Close";
				this.h();
			},

			h: function hydrate() {
				setAttribute(form, "onsubmit", "return false;");
				form.className = "recovery-dialog pf-form";
				div.className = "pf-element pf-heading";
				div_1.className = "pf-element pf-buttons";
				button.className = "pf-button";
				button.type = "button";
				addListener(button, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(form, target, anchor);
				component.refs.form = form;
				appendNode(div, form);
				appendNode(text_2, form);
				if_block.m(form, null);
				appendNode(text_3, form);
				if (if_block_1) if_block_1.m(form, null);
				appendNode(text_4, form);
				appendNode(div_1, form);
				if_block_2.m(div_1, null);
				appendNode(text_5, div_1);
				appendNode(button, div_1);
			},

			p: function update(changed, state) {
				if (current_block_type === (current_block_type = select_block_type(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(form, text_3);
				}

				if (state.__failureMessage) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_11(state, component);
						if_block_1.c();
						if_block_1.m(form, text_4);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}

				if (current_block_type_1 !== (current_block_type_1 = select_block_type_1(state))) {
					if_block_2.u();
					if_block_2.d();
					if_block_2 = current_block_type_1(state, component);
					if_block_2.c();
					if_block_2.m(div_1, text_5);
				}
			},

			u: function unmount() {
				detachNode(form);
				if_block.u();
				if (if_block_1) if_block_1.u();
				if_block_2.u();
			},

			d: function destroy() {
				if (component.refs.form === form) component.refs.form = null;
				if_block.d();
				if (if_block_1) if_block_1.d();
				if_block_2.d();
				removeListener(button, "click", click_handler);
			}
		};
	}

	// (3:0) {{#if __showDialog}}
	function create_if_block(state, component) {
		var div, div_1, text;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		var current_block_type = select_block_type_2(state);
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
				div.className = "recovery-dialog-container layout-compact";
				div_1.className = "recovery-dialog-overlay";
				addListener(div_1, "click", click_handler);
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div);
				if_block.m(div, null);
			},

			p: function update(changed, state) {
				if (current_block_type === (current_block_type = select_block_type_2(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(div, null);
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
		if (!state.__hasSentSecret) return create_if_block_4;
		return create_if_block_10;
	}

	function select_block_type_1(state) {
		if (!state.__hasSentSecret) return create_if_block_12;
		return create_if_block_13;
	}

	function select_block_type_2(state) {
		if (state.__recovering) return create_if_block_1;
		if (state.__successRecoveredMessage) return create_if_block_2;
		return create_if_block_3;
	}

	function TilmeldRecover(options) {
		init(this, options);
		this.refs = {};
		this._state = assign(data(), options.data);
		this._bindingGroups = [[]];

		if (!document.getElementById("svelte-1453595330-style")) add_css();

		var _oncreate = oncreate.bind(this);

		if (!options._root) {
			this._oncreate = [_oncreate];
		} else {
			this._root._oncreate.push(_oncreate);
		}

		this._fragment = create_main_fragment(this._state, this);

		if (options.target) {
			this._fragment.c();
			this._fragment.m(options.target, options.anchor || null);

			callAll(this._oncreate);
		}
	}

	assign(TilmeldRecover.prototype, methods, {
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

	TilmeldRecover.prototype._recompute = noop;

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

	function addListener(node, event, handler) {
		node.addEventListener(event, handler, false);
	}

	function insertNode(node, target, anchor) {
		target.insertBefore(node, anchor);
	}

	function detachNode(node) {
		node.parentNode.removeChild(node);
	}

	function removeListener(node, event, handler) {
		node.removeEventListener(event, handler, false);
	}

	function noop() {}

	function setStyle(node, key, value) {
		node.style.setProperty(key, value);
	}

	function init(component, options) {
		component.options = options;

		component._observers = { pre: blankObject(), post: blankObject() };
		component._handlers = blankObject();
		component._root = options._root || component;
		component._bind = options._bind;
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
		if (this._root._lock) return;
		this._root._lock = true;
		callAll(this._root._beforecreate);
		callAll(this._root._oncreate);
		callAll(this._root._aftercreate);
		this._root._lock = false;
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
		dispatchObservers(this, this._observers.pre, changed, this._state, oldState);
		this._fragment.p(changed, this._state);
		dispatchObservers(this, this._observers.post, changed, this._state, oldState);
	}

	function _mount(target, anchor) {
		this._fragment.m(target, anchor);
	}

	function _unmount() {
		this._fragment.u();
	}

	function blankObject() {
		return Object.create(null);
	}

	function differs(a, b) {
		return a !== b || a && (typeof a === 'undefined' ? 'undefined' : _typeof(a)) === 'object' || typeof a === 'function';
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
	exports.default = TilmeldRecover;
});
