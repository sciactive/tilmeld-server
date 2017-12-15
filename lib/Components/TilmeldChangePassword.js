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
		global.TilmeldChangePassword = mod.exports;
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
			layout: 'normal', // 'normal', 'small', or 'compact'
			compactText: 'Change password', // The text used to toggle the dialog when 'compact' layout is selected.
			classInput: '',
			classSubmit: '',
			classButton: '',
			__user: null,
			__showDialog: false,
			__changing: false,

			// These are all user provided details.
			oldPassword: '',
			password: '',
			password2: ''
		};
	};

	var methods = {
		changePassword: function changePassword() {
			var _this = this;

			if (this.get('oldPassword') === '') {
				this.set({ __failureMessage: 'You need to enter your current password' });
				return;
			}
			if (this.get('password') != this.get('password2')) {
				this.set({ __failureMessage: 'Your passwords do not match.' });
				return;
			}
			if (this.get('password') === '') {
				this.set({ __failureMessage: 'You need to enter a new password' });
				return;
			}

			this.set({
				__failureMessage: null,
				__changing: true
			});
			// Get the current user again, in case their data has changed.
			_User2.default.current().then(function (user) {
				_this.set({ __user: user });

				if (!user || !user.guid) {
					_this.set({
						__failureMessage: 'You must be logged in.',
						__changing: false
					});
					return;
				}

				// Create a new user.
				user.changePassword({ 'oldPassword': _this.get('oldPassword'), 'password': _this.get('password') }).then(function (data) {
					if (!data.result) {
						_this.set({ __failureMessage: data.message });
					} else {
						_this.set({ __successChangedMessage: data.message });
					}
					_this.set({ __changing: false });
				}, function () {
					_this.set({
						__failureMessage: 'An error occurred.',
						__changing: false
					});
				});
			});
		}
	};

	function oncreate() {
		var _this2 = this;

		_User2.default.current().then(function (user) {
			_this2.set({ __user: user });
		});

		_User2.default.on('login', function (user) {
			_this2.set({ __user: user });
		});
		_User2.default.on('logout', function () {
			_this2.set({ __user: null });
		});

		this.observe('__showDialog', function (value) {
			if (!value) {
				_this2.set({
					__changing: false,
					__failureMessage: null,
					__successChangedMessage: null,
					oldPassword: '',
					password: '',
					password2: ''
				});
			}
		});
	};

	function encapsulateStyles(node) {
		setAttribute(node, "svelte-3543035577", "");
	}

	function add_css() {
		var style = createElement("style");
		style.id = 'svelte-3543035577-style';
		style.textContent = "[svelte-3543035577].change-password-dialog-container,[svelte-3543035577] .change-password-dialog-container{display:flex;align-items:center}[svelte-3543035577].change-password-dialog-container.layout-compact,[svelte-3543035577] .change-password-dialog-container.layout-compact{justify-content:center;position:fixed;top:0;left:0;bottom:0;right:0;z-index:1000}[svelte-3543035577].change-password-dialog-overlay,[svelte-3543035577] .change-password-dialog-overlay{display:none}[svelte-3543035577].change-password-dialog-container.layout-compact .change-password-dialog-overlay,[svelte-3543035577] .change-password-dialog-container.layout-compact .change-password-dialog-overlay{display:block;position:absolute;top:0;left:0;bottom:0;right:0;background-color:rgba(0, 0, 0, 0.1);z-index:1}[svelte-3543035577].change-password-dialog,[svelte-3543035577] .change-password-dialog{display:flex;flex-direction:column}[svelte-3543035577].change-password-dialog-container.layout-compact .change-password-dialog,[svelte-3543035577] .change-password-dialog-container.layout-compact .change-password-dialog{padding:2em;box-shadow:0px 5px 36px 0px rgba(0,0,0,0.25);background-color:#fff;max-height:80vh;max-width:80vw;overflow:auto;z-index:2}[svelte-3543035577].change-password-dialog-container.layout-compact .change-password-dialog.loading,[svelte-3543035577] .change-password-dialog-container.layout-compact .change-password-dialog.loading{width:90vw;height:90vh;max-width:260px;max-height:100px;justify-content:center;align-items:center}[svelte-3543035577].change-password-dialog-title,[svelte-3543035577] .change-password-dialog-title{padding-top:0;margin-top:0}[svelte-3543035577].close-button-container,[svelte-3543035577] .close-button-container{text-align:right;margin-top:1em}";
		appendNode(style, document.head);
	}

	function create_main_fragment(state, component) {
		var if_block_anchor;

		var current_block_type = select_block_type_1(state);
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
				if (current_block_type === (current_block_type = select_block_type_1(state)) && if_block) {
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

	// (2:2) {{#if layout === 'compact'}}
	function create_if_block_1(state, component) {
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

	// (17:12) {{#if __changing}}
	function create_if_block_4(state, component) {
		var text;

		return {
			c: function create() {
				text = createText("This will just take a second...");
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

	// (27:10) {{#if layout === 'compact'}}
	function create_if_block_6(state, component) {
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

	// (35:10) {{#if layout === 'compact'}}
	function create_if_block_8(state, component) {
		var div, h2, text;

		return {
			c: function create() {
				div = createElement("div");
				h2 = createElement("h2");
				text = createText(state.compactText);
				this.h();
			},

			h: function hydrate() {
				h2.className = "change-password-dialog-title";
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

	// (59:10) {{#if __failureMessage}}
	function create_if_block_9(state, component) {
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

	// (71:12) {{#if layout === 'compact'}}
	function create_if_block_10(state, component) {
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

	// (9:6) {{#if __changing}}
	function create_if_block_3(state, component) {
		var div, span, svg, path, animateTransform, text;

		var if_block = state.__changing && create_if_block_4(state, component);

		return {
			c: function create() {
				div = createElement("div");
				span = createElement("span");
				svg = createSvgElement("svg");
				path = createSvgElement("path");
				animateTransform = createSvgElement("animateTransform");
				text = createText("\n            ");
				if (if_block) if_block.c();
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
				div.className = "change-password-dialog loading";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(span, div);
				appendNode(svg, span);
				appendNode(path, svg);
				appendNode(animateTransform, path);
				appendNode(text, span);
				if (if_block) if_block.m(span, null);
			},

			p: function update(changed, state) {
				if (state.__changing) {
					if (!if_block) {
						if_block = create_if_block_4(state, component);
						if_block.c();
						if_block.m(span, null);
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

	// (22:40) 
	function create_if_block_5(state, component) {
		var div, div_1, text, text_2;

		var if_block = state.layout === 'compact' && create_if_block_6(state, component);

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText(state.__successChangedMessage);
				text_2 = createText("\n          ");
				if (if_block) if_block.c();
				this.h();
			},

			h: function hydrate() {
				div.className = "change-password-dialog";
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div_1);
				appendNode(text_2, div);
				if (if_block) if_block.m(div, null);
			},

			p: function update(changed, state) {
				if (changed.__successChangedMessage) {
					text.data = state.__successChangedMessage;
				}

				if (state.layout === 'compact') {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_6(state, component);
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

	// (33:6) {{else}}
	function create_if_block_7(state, component) {
		var form,
		    text,
		    div,
		    label,
		    span,
		    text_2,
		    input,
		    input_updating = false,
		    input_class_value,
		    text_5,
		    div_1,
		    label_1,
		    span_1,
		    text_7,
		    input_1,
		    input_1_updating = false,
		    input_1_class_value,
		    text_10,
		    div_2,
		    label_2,
		    span_2,
		    text_12,
		    input_2,
		    input_2_updating = false,
		    input_2_class_value,
		    text_15,
		    text_16,
		    div_3,
		    button,
		    button_class_value,
		    text_18,
		    div_3_class_value,
		    form_class_value;

		var if_block = state.layout === 'compact' && create_if_block_8(state, component);

		function input_input_handler() {
			input_updating = true;
			component.set({ oldPassword: input.value });
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

		var if_block_1 = state.__failureMessage && create_if_block_9(state, component);

		function click_handler(event) {
			component.changePassword();
		}

		var if_block_2 = state.layout === 'compact' && create_if_block_10(state, component);

		return {
			c: function create() {
				form = createElement("form");
				if (if_block) if_block.c();
				text = createText("\n          ");
				div = createElement("div");
				label = createElement("label");
				span = createElement("span");
				span.textContent = "Old Password";
				text_2 = createText("\n              ");
				input = createElement("input");
				text_5 = createText("\n          ");
				div_1 = createElement("div");
				label_1 = createElement("label");
				span_1 = createElement("span");
				span_1.textContent = "New Password";
				text_7 = createText("\n              ");
				input_1 = createElement("input");
				text_10 = createText("\n          ");
				div_2 = createElement("div");
				label_2 = createElement("label");
				span_2 = createElement("span");
				span_2.textContent = "Re-enter New Password";
				text_12 = createText("\n              ");
				input_2 = createElement("input");
				text_15 = createText("\n\n          ");
				if (if_block_1) if_block_1.c();
				text_16 = createText("\n\n          ");
				div_3 = createElement("div");
				button = createElement("button");
				button.textContent = "Change Password";
				text_18 = createText("\n            ");
				if (if_block_2) if_block_2.c();
				this.h();
			},

			h: function hydrate() {
				span.className = "pf-label";
				addListener(input, "input", input_input_handler);
				input.className = input_class_value = "pf-field " + state.classInput;
				input.type = "password";
				input.name = "oldPassword";
				input.size = "24";
				div.className = "pf-element";
				span_1.className = "pf-label";
				addListener(input_1, "input", input_1_input_handler);
				input_1.className = input_1_class_value = "pf-field " + state.classInput;
				input_1.type = "password";
				input_1.name = "password";
				input_1.size = "24";
				div_1.className = "pf-element";
				span_2.className = "pf-label";
				addListener(input_2, "input", input_2_input_handler);
				input_2.className = input_2_class_value = "pf-field " + state.classInput;
				input_2.type = "password";
				input_2.name = "password2";
				input_2.size = "24";
				div_2.className = "pf-element";
				button.className = button_class_value = "pf-button " + state.classSubmit;
				button.type = "submit";
				addListener(button, "click", click_handler);
				div_3.className = div_3_class_value = "pf-element " + (state.layout === 'small' ? '' : 'pf-buttons');
				setAttribute(form, "onsubmit", "return false;");
				form.className = form_class_value = "change-password-dialog pf-form " + (state.layout === 'small' ? 'pf-layout-block' : '');
			},

			m: function mount(target, anchor) {
				insertNode(form, target, anchor);
				if (if_block) if_block.m(form, null);
				appendNode(text, form);
				appendNode(div, form);
				appendNode(label, div);
				appendNode(span, label);
				appendNode(text_2, label);
				appendNode(input, label);

				input.value = state.oldPassword;

				appendNode(text_5, form);
				appendNode(div_1, form);
				appendNode(label_1, div_1);
				appendNode(span_1, label_1);
				appendNode(text_7, label_1);
				appendNode(input_1, label_1);

				input_1.value = state.password;

				appendNode(text_10, form);
				appendNode(div_2, form);
				appendNode(label_2, div_2);
				appendNode(span_2, label_2);
				appendNode(text_12, label_2);
				appendNode(input_2, label_2);

				input_2.value = state.password2;

				appendNode(text_15, form);
				if (if_block_1) if_block_1.m(form, null);
				appendNode(text_16, form);
				appendNode(div_3, form);
				appendNode(button, div_3);
				appendNode(text_18, div_3);
				if (if_block_2) if_block_2.m(div_3, null);
				component.refs.form = form;
			},

			p: function update(changed, state) {
				if (state.layout === 'compact') {
					if (if_block) {
						if_block.p(changed, state);
					} else {
						if_block = create_if_block_8(state, component);
						if_block.c();
						if_block.m(form, text);
					}
				} else if (if_block) {
					if_block.u();
					if_block.d();
					if_block = null;
				}

				if (!input_updating) input.value = state.oldPassword;
				if (changed.classInput && input_class_value !== (input_class_value = "pf-field " + state.classInput)) {
					input.className = input_class_value;
				}

				if (!input_1_updating) input_1.value = state.password;
				if (changed.classInput && input_1_class_value !== (input_1_class_value = "pf-field " + state.classInput)) {
					input_1.className = input_1_class_value;
				}

				if (!input_2_updating) input_2.value = state.password2;
				if (changed.classInput && input_2_class_value !== (input_2_class_value = "pf-field " + state.classInput)) {
					input_2.className = input_2_class_value;
				}

				if (state.__failureMessage) {
					if (if_block_1) {
						if_block_1.p(changed, state);
					} else {
						if_block_1 = create_if_block_9(state, component);
						if_block_1.c();
						if_block_1.m(form, text_16);
					}
				} else if (if_block_1) {
					if_block_1.u();
					if_block_1.d();
					if_block_1 = null;
				}

				if (changed.classSubmit && button_class_value !== (button_class_value = "pf-button " + state.classSubmit)) {
					button.className = button_class_value;
				}

				if (state.layout === 'compact') {
					if (if_block_2) {
						if_block_2.p(changed, state);
					} else {
						if_block_2 = create_if_block_10(state, component);
						if_block_2.c();
						if_block_2.m(div_3, null);
					}
				} else if (if_block_2) {
					if_block_2.u();
					if_block_2.d();
					if_block_2 = null;
				}

				if (changed.layout && div_3_class_value !== (div_3_class_value = "pf-element " + (state.layout === 'small' ? '' : 'pf-buttons'))) {
					div_3.className = div_3_class_value;
				}

				if (changed.layout && form_class_value !== (form_class_value = "change-password-dialog pf-form " + (state.layout === 'small' ? 'pf-layout-block' : ''))) {
					form.className = form_class_value;
				}
			},

			u: function unmount() {
				detachNode(form);
				if (if_block) if_block.u();
				if (if_block_1) if_block_1.u();
				if (if_block_2) if_block_2.u();
			},

			d: function destroy() {
				if (if_block) if_block.d();
				removeListener(input, "input", input_input_handler);
				removeListener(input_1, "input", input_1_input_handler);
				removeListener(input_2, "input", input_2_input_handler);
				if (if_block_1) if_block_1.d();
				removeListener(button, "click", click_handler);
				if (if_block_2) if_block_2.d();
				if (component.refs.form === form) component.refs.form = null;
			}
		};
	}

	// (6:2) {{#if layout !== 'compact' || __showDialog}}
	function create_if_block_2(state, component) {
		var div, div_1, text, div_class_value;

		function click_handler(event) {
			component.set({ __showDialog: false });
		}

		var current_block_type = select_block_type(state);
		var if_block = current_block_type(state, component);

		return {
			c: function create() {
				div = createElement("div");
				div_1 = createElement("div");
				text = createText("\n      ");
				if_block.c();
				this.h();
			},

			h: function hydrate() {
				encapsulateStyles(div);
				div_1.className = "change-password-dialog-overlay";
				addListener(div_1, "click", click_handler);
				div.className = div_class_value = "change-password-dialog-container layout-" + state.layout;
			},

			m: function mount(target, anchor) {
				insertNode(div, target, anchor);
				appendNode(div_1, div);
				appendNode(text, div);
				if_block.m(div, null);
			},

			p: function update(changed, state) {
				if (current_block_type === (current_block_type = select_block_type(state)) && if_block) {
					if_block.p(changed, state);
				} else {
					if_block.u();
					if_block.d();
					if_block = current_block_type(state, component);
					if_block.c();
					if_block.m(div, null);
				}

				if (changed.layout && div_class_value !== (div_class_value = "change-password-dialog-container layout-" + state.layout)) {
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

	// (1:0) {{#if __user && __user.guid}}
	function create_if_block(state, component) {
		var text, if_block_1_anchor;

		var if_block = state.layout === 'compact' && create_if_block_1(state, component);

		var if_block_1 = (state.layout !== 'compact' || state.__showDialog) && create_if_block_2(state, component);

		return {
			c: function create() {
				if (if_block) if_block.c();
				text = createText("\n\n  ");
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
						if_block = create_if_block_1(state, component);
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
						if_block_1 = create_if_block_2(state, component);
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

	// (79:0) {{else}}
	function create_if_block_11(state, component) {
		var text;

		return {
			c: function create() {
				text = createText("You must be logged in to change your password.");
			},

			m: function mount(target, anchor) {
				insertNode(text, target, anchor);
			},

			p: noop,

			u: function unmount() {
				detachNode(text);
			},

			d: noop
		};
	}

	function select_block_type(state) {
		if (state.__changing) return create_if_block_3;
		if (state.__successChangedMessage) return create_if_block_5;
		return create_if_block_7;
	}

	function select_block_type_1(state) {
		if (state.__user && state.__user.guid) return create_if_block;
		return create_if_block_11;
	}

	function TilmeldChangePassword(options) {
		init(this, options);
		this.refs = {};
		this._state = assign(data(), options.data);

		if (!document.getElementById("svelte-3543035577-style")) add_css();

		var _oncreate = oncreate.bind(this);

		if (!options.root) {
			this._oncreate = [_oncreate];
		} else {
			this.root._oncreate.push(_oncreate);
		}

		this._fragment = create_main_fragment(this._state, this);

		if (options.target) {
			this._fragment.c();
			this._fragment.m(options.target, options.anchor || null);

			callAll(this._oncreate);
		}
	}

	assign(TilmeldChangePassword.prototype, methods, {
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

	TilmeldChangePassword.prototype._recompute = noop;

	function setAttribute(node, attribute, value) {
		node.setAttribute(attribute, value);
	}

	function createElement(name) {
		return document.createElement(name);
	}

	function appendNode(node, target) {
		target.appendChild(node);
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

	function createText(data) {
		return document.createTextNode(data);
	}

	function addListener(node, event, handler) {
		node.addEventListener(event, handler, false);
	}

	function removeListener(node, event, handler) {
		node.removeEventListener(event, handler, false);
	}

	function noop() {}

	function setStyle(node, key, value) {
		node.style.setProperty(key, value);
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
	exports.default = TilmeldChangePassword;
});
