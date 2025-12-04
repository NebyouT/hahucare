/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-47.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-47.use[2]!./node_modules/node-snackbar/dist/snackbar.min.css":
/*!********************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-47.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-47.use[2]!./node_modules/node-snackbar/dist/snackbar.min.css ***!
  \********************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".snackbar-container{transition:all .5s ease;transition-property:top,right,bottom,left,opacity;font-family:Roboto,sans-serif;font-size:14px;min-height:14px;background-color:#070b0e;position:fixed;display:flex;justify-content:space-between;align-items:center;color:#fff;line-height:22px;padding:18px 24px;bottom:-100px;top:-100px;opacity:0;z-index:9999}.snackbar-container .action{background:inherit;display:inline-block;border:none;font-size:inherit;text-transform:uppercase;color:#4caf50;margin:0 0 0 24px;padding:0;min-width:-moz-min-content;min-width:min-content;cursor:pointer}@media (min-width:640px){.snackbar-container{min-width:288px;max-width:568px;display:inline-flex;border-radius:2px;margin:24px}}@media (max-width:640px){.snackbar-container{left:0;right:0;width:100%}}.snackbar-pos.bottom-center{top:auto!important;bottom:0;left:50%;transform:translate(-50%,0)}.snackbar-pos.bottom-left{top:auto!important;bottom:0;left:0}.snackbar-pos.bottom-right{top:auto!important;bottom:0;right:0}.snackbar-pos.top-left{bottom:auto!important;top:0;left:0}.snackbar-pos.top-center{bottom:auto!important;top:0;left:50%;transform:translate(-50%,0)}.snackbar-pos.top-right{bottom:auto!important;top:0;right:0}@media (max-width:640px){.snackbar-pos.bottom-center,.snackbar-pos.top-center{left:0;transform:none}}", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (cssWithMappingToString) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join("");
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === "string") {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, ""]];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

/***/ }),

/***/ "./node_modules/node-snackbar/dist/snackbar.min.css":
/*!**********************************************************!*\
  !*** ./node_modules/node-snackbar/dist/snackbar.min.css ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _css_loader_dist_cjs_js_clonedRuleSet_47_use_1_postcss_loader_dist_cjs_js_clonedRuleSet_47_use_2_snackbar_min_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../css-loader/dist/cjs.js??clonedRuleSet-47.use[1]!../../postcss-loader/dist/cjs.js??clonedRuleSet-47.use[2]!./snackbar.min.css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-47.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-47.use[2]!./node_modules/node-snackbar/dist/snackbar.min.css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_css_loader_dist_cjs_js_clonedRuleSet_47_use_1_postcss_loader_dist_cjs_js_clonedRuleSet_47_use_2_snackbar_min_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_css_loader_dist_cjs_js_clonedRuleSet_47_use_1_postcss_loader_dist_cjs_js_clonedRuleSet_47_use_2_snackbar_min_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/node-snackbar/src/js/snackbar.js":
/*!*******************************************************!*\
  !*** ./node_modules/node-snackbar/src/js/snackbar.js ***!
  \*******************************************************/
/***/ (function(module, exports) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
 * Snackbar v0.1.14
 * http://polonel.com/Snackbar
 *
 * Copyright 2018 Chris Brame and other contributors
 * Released under the MIT license
 * https://github.com/polonel/Snackbar/blob/master/LICENSE
 */

(function(root, factory) {
    'use strict';

    if (true) {
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function() {
            return (root.Snackbar = factory());
        }).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    } else {}
})(this, function() {
    var Snackbar = {};

    Snackbar.current = null;
    var $defaults = {
        text: 'Default Text',
        textColor: '#FFFFFF',
        width: 'auto',
        showAction: true,
        actionText: 'Dismiss',
        actionTextAria: 'Dismiss, Description for Screen Readers',
        alertScreenReader: false,
        actionTextColor: '#4CAF50',
        showSecondButton: false,
        secondButtonText: '',
        secondButtonAria: 'Description for Screen Readers',
        secondButtonTextColor: '#4CAF50',
        backgroundColor: '#323232',
        pos: 'bottom-left',
        duration: 5000,
        customClass: '',
        onActionClick: function(element) {
            element.style.opacity = 0;
        },
        onSecondButtonClick: function(element) {},
        onClose: function(element) {}
    };

    Snackbar.show = function($options) {
        var options = Extend(true, $defaults, $options);

        if (Snackbar.current) {
            Snackbar.current.style.opacity = 0;
            setTimeout(
                function() {
                    var $parent = this.parentElement;
                    if ($parent)
                    // possible null if too many/fast Snackbars
                        $parent.removeChild(this);
                }.bind(Snackbar.current),
                500
            );
        }

        Snackbar.snackbar = document.createElement('div');
        Snackbar.snackbar.className = 'snackbar-container ' + options.customClass;
        Snackbar.snackbar.style.width = options.width;
        var $p = document.createElement('p');
        $p.style.margin = 0;
        $p.style.padding = 0;
        $p.style.color = options.textColor;
        $p.style.fontSize = '14px';
        $p.style.fontWeight = 300;
        $p.style.lineHeight = '1em';
        $p.innerHTML = options.text;
        Snackbar.snackbar.appendChild($p);
        Snackbar.snackbar.style.background = options.backgroundColor;

        if (options.showSecondButton) {
            var secondButton = document.createElement('button');
            secondButton.className = 'action';
            secondButton.innerHTML = options.secondButtonText;
            secondButton.setAttribute('aria-label', options.secondButtonAria);
            secondButton.style.color = options.secondButtonTextColor;
            secondButton.addEventListener('click', function() {
                options.onSecondButtonClick(Snackbar.snackbar);
            });
            Snackbar.snackbar.appendChild(secondButton);
        }

        if (options.showAction) {
            var actionButton = document.createElement('button');
            actionButton.className = 'action';
            actionButton.innerHTML = options.actionText;
            actionButton.setAttribute('aria-label', options.actionTextAria);
            actionButton.style.color = options.actionTextColor;
            actionButton.addEventListener('click', function() {
                options.onActionClick(Snackbar.snackbar);
            });
            Snackbar.snackbar.appendChild(actionButton);
        }

        if (options.duration) {
            setTimeout(
                function() {
                    if (Snackbar.current === this) {
                        Snackbar.current.style.opacity = 0;
                        // When natural remove event occurs let's move the snackbar to its origins
                        Snackbar.current.style.top = '-100px';
                        Snackbar.current.style.bottom = '-100px';
                    }
                }.bind(Snackbar.snackbar),
                options.duration
            );
        }

        if (options.alertScreenReader) {
           Snackbar.snackbar.setAttribute('role', 'alert');
        }

        Snackbar.snackbar.addEventListener(
            'transitionend',
            function(event, elapsed) {
                if (event.propertyName === 'opacity' && this.style.opacity === '0') {
                    if (typeof(options.onClose) === 'function')
                        options.onClose(this);

                    this.parentElement.removeChild(this);
                    if (Snackbar.current === this) {
                        Snackbar.current = null;
                    }
                }
            }.bind(Snackbar.snackbar)
        );

        Snackbar.current = Snackbar.snackbar;

        document.body.appendChild(Snackbar.snackbar);
        var $bottom = getComputedStyle(Snackbar.snackbar).bottom;
        var $top = getComputedStyle(Snackbar.snackbar).top;
        Snackbar.snackbar.style.opacity = 1;
        Snackbar.snackbar.className =
            'snackbar-container ' + options.customClass + ' snackbar-pos ' + options.pos;
    };

    Snackbar.close = function() {
        if (Snackbar.current) {
            Snackbar.current.style.opacity = 0;
        }
    };

    // Pure JS Extend
    // http://gomakethings.com/vanilla-javascript-version-of-jquery-extend/
    var Extend = function() {
        var extended = {};
        var deep = false;
        var i = 0;
        var length = arguments.length;

        if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
            deep = arguments[0];
            i++;
        }

        var merge = function(obj) {
            for (var prop in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                    if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                        extended[prop] = Extend(true, extended[prop], obj[prop]);
                    } else {
                        extended[prop] = obj[prop];
                    }
                }
            }
        };

        for (; i < length; i++) {
            var obj = arguments[i];
            merge(obj);
        }

        return extended;
    };

    return Snackbar;
});


/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js":
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var isOldIE = function isOldIE() {
  var memo;
  return function memorize() {
    if (typeof memo === 'undefined') {
      // Test for IE <= 9 as proposed by Browserhacks
      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
      // Tests for existence of standard globals is to allow style-loader
      // to operate correctly into non-standard environments
      // @see https://github.com/webpack-contrib/style-loader/issues/177
      memo = Boolean(window && document && document.all && !window.atob);
    }

    return memo;
  };
}();

var getTarget = function getTarget() {
  var memo = {};
  return function memorize(target) {
    if (typeof memo[target] === 'undefined') {
      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself

      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
        try {
          // This will throw an exception if access to iframe is blocked
          // due to cross-origin restrictions
          styleTarget = styleTarget.contentDocument.head;
        } catch (e) {
          // istanbul ignore next
          styleTarget = null;
        }
      }

      memo[target] = styleTarget;
    }

    return memo[target];
  };
}();

var stylesInDom = [];

function getIndexByIdentifier(identifier) {
  var result = -1;

  for (var i = 0; i < stylesInDom.length; i++) {
    if (stylesInDom[i].identifier === identifier) {
      result = i;
      break;
    }
  }

  return result;
}

function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];

  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var index = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3]
    };

    if (index !== -1) {
      stylesInDom[index].references++;
      stylesInDom[index].updater(obj);
    } else {
      stylesInDom.push({
        identifier: identifier,
        updater: addStyle(obj, options),
        references: 1
      });
    }

    identifiers.push(identifier);
  }

  return identifiers;
}

function insertStyleElement(options) {
  var style = document.createElement('style');
  var attributes = options.attributes || {};

  if (typeof attributes.nonce === 'undefined') {
    var nonce =  true ? __webpack_require__.nc : 0;

    if (nonce) {
      attributes.nonce = nonce;
    }
  }

  Object.keys(attributes).forEach(function (key) {
    style.setAttribute(key, attributes[key]);
  });

  if (typeof options.insert === 'function') {
    options.insert(style);
  } else {
    var target = getTarget(options.insert || 'head');

    if (!target) {
      throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
    }

    target.appendChild(style);
  }

  return style;
}

function removeStyleElement(style) {
  // istanbul ignore if
  if (style.parentNode === null) {
    return false;
  }

  style.parentNode.removeChild(style);
}
/* istanbul ignore next  */


var replaceText = function replaceText() {
  var textStore = [];
  return function replace(index, replacement) {
    textStore[index] = replacement;
    return textStore.filter(Boolean).join('\n');
  };
}();

function applyToSingletonTag(style, index, remove, obj) {
  var css = remove ? '' : obj.media ? "@media ".concat(obj.media, " {").concat(obj.css, "}") : obj.css; // For old IE

  /* istanbul ignore if  */

  if (style.styleSheet) {
    style.styleSheet.cssText = replaceText(index, css);
  } else {
    var cssNode = document.createTextNode(css);
    var childNodes = style.childNodes;

    if (childNodes[index]) {
      style.removeChild(childNodes[index]);
    }

    if (childNodes.length) {
      style.insertBefore(cssNode, childNodes[index]);
    } else {
      style.appendChild(cssNode);
    }
  }
}

function applyToTag(style, options, obj) {
  var css = obj.css;
  var media = obj.media;
  var sourceMap = obj.sourceMap;

  if (media) {
    style.setAttribute('media', media);
  } else {
    style.removeAttribute('media');
  }

  if (sourceMap && typeof btoa !== 'undefined') {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  } // For old IE

  /* istanbul ignore if  */


  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    while (style.firstChild) {
      style.removeChild(style.firstChild);
    }

    style.appendChild(document.createTextNode(css));
  }
}

var singleton = null;
var singletonCounter = 0;

function addStyle(obj, options) {
  var style;
  var update;
  var remove;

  if (options.singleton) {
    var styleIndex = singletonCounter++;
    style = singleton || (singleton = insertStyleElement(options));
    update = applyToSingletonTag.bind(null, style, styleIndex, false);
    remove = applyToSingletonTag.bind(null, style, styleIndex, true);
  } else {
    style = insertStyleElement(options);
    update = applyToTag.bind(null, style, options);

    remove = function remove() {
      removeStyleElement(style);
    };
  }

  update(obj);
  return function updateStyle(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {
        return;
      }

      update(obj = newObj);
    } else {
      remove();
    }
  };
}

module.exports = function (list, options) {
  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
  // tags it will allow on a page

  if (!options.singleton && typeof options.singleton !== 'boolean') {
    options.singleton = isOldIE();
  }

  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];

    if (Object.prototype.toString.call(newList) !== '[object Array]') {
      return;
    }

    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDom[index].references--;
    }

    var newLastIdentifiers = modulesToDom(newList, options);

    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];

      var _index = getIndexByIdentifier(_identifier);

      if (stylesInDom[_index].references === 0) {
        stylesInDom[_index].updater();

        stylesInDom.splice(_index, 1);
      }
    }

    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/nonce */
/******/ 	(() => {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!****************************************!*\
  !*** ./resources/js/backend-custom.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var node_snackbar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! node-snackbar */ "./node_modules/node-snackbar/src/js/snackbar.js");
/* harmony import */ var node_snackbar__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(node_snackbar__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var node_snackbar_dist_snackbar_min_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! node-snackbar/dist/snackbar.min.css */ "./node_modules/node-snackbar/dist/snackbar.min.css");
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/*
 * Version: 1.1.0
 * Template: Hope-Ui - Responsive Bootstrap 5 Admin Dashboard Template
 * Author: iqonic.design
 * Design and Developed by: iqonic.design
 * NOTE: This file contains the script for initialize & listener Template.
 */
/*----------------------------------------------
Index Of Script
------------------------------------------------
------- Plugin Init --------
:: Tooltip
:: Popover
:: Progress Bar
:: NoUiSlider
:: CopyToClipboard
:: Vanila Datepicker
:: SliderTab
:: Data Tables
:: Active Class for Pricing Table
------ Functions --------
:: Loader Init
:: Resize Plugins
:: Sidebar Toggle
:: Back To Top
------- Listners ---------
:: DOMContentLoaded
:: Window Resize
------------------------------------------------
Index Of Script
----------------------------------------------*/


(function () {
  'use strict';

  /*------------LoaderInit----------------*/
  var loaderInit = function loaderInit() {
    var loader = document.querySelector('.loader');
    if (loader !== null) {
      loader.classList.add('animate__animated', 'animate__fadeOut');
      setTimeout(function () {
        loader.classList.add('d-none');
      }, 200);
    }
  };

  /*----------Sticky-Nav-----------*/
  window.addEventListener('scroll', function () {
    var yOffset = document.documentElement.scrollTop;
    var navbar = document.querySelector('.navs-sticky');
    if (navbar !== null) {
      if (yOffset >= 100) {
        navbar.classList.add('menu-sticky');
      } else {
        navbar.classList.remove('menu-sticky');
      }
    }
  });
  /*------------Popover--------------*/
  var initPopovers = function initPopovers() {
    if ((typeof bootstrap === "undefined" ? "undefined" : _typeof(bootstrap)) !== ( true ? "undefined" : 0) && bootstrap.Popover) {
      var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
      popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
      });
    }
  };
  /*-------------Tooltip--------------------*/
  // Initialize tooltip function
  window.tooltipInit = function () {
    if ((typeof bootstrap === "undefined" ? "undefined" : _typeof(bootstrap)) !== ( true ? "undefined" : 0) && bootstrap.Tooltip) {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
      var sidebarTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-sidebar-toggle="tooltip"]'));
      sidebarTooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    } else {
      console.warn('Bootstrap is not available, tooltips cannot be initialized');
    }
  };

  // Initialize tooltips and popovers when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initPopovers();
      window.tooltipInit();
    });
  } else {
    initPopovers();
    window.tooltipInit();
  }
  /*-------------Progress Bar------------------*/
  var progressBarInit = function progressBarInit(elem) {
    var currentValue = elem.getAttribute('aria-valuenow');
    elem.style.width = '0%';
    elem.style.transition = 'width 2s';
    if ((typeof Waypoint === "undefined" ? "undefined" : _typeof(Waypoint)) !== ( true ? "undefined" : 0)) {
      new Waypoint({
        element: elem,
        handler: function handler() {
          setTimeout(function () {
            elem.style.width = currentValue + '%';
          }, 100);
        },
        offset: 'bottom-in-view'
      });
    }
  };
  var customProgressBar = document.querySelectorAll('[data-toggle="progress-bar"]');
  Array.from(customProgressBar, function (elem) {
    progressBarInit(elem);
  });
  /*---------------noUiSlider-------------------*/
  function createSlider(elem) {
    return noUiSlider.create(elem, {
      start: [50, 2000],
      connect: true,
      range: {
        min: 0,
        '10%': [50, 50],
        max: 2000
      }
    });
  }
  var rangeSlider = document.querySelectorAll('.range-slider');
  Array.from(rangeSlider, function (elem) {
    if ((typeof noUiSlider === "undefined" ? "undefined" : _typeof(noUiSlider)) !== ( true ? "undefined" : 0)) {
      if (elem.getAttribute('id') !== '' && elem.getAttribute('id') !== null) {
        window[elem.getAttribute('id')] = createSlider(elem);
      } else {
        createSlider(elem);
      }
    }
  });
  var slider = document.querySelectorAll('.slider');
  Array.from(slider, function (elem) {
    if ((typeof noUiSlider === "undefined" ? "undefined" : _typeof(noUiSlider)) !== ( true ? "undefined" : 0)) {
      noUiSlider.create(elem, {
        start: 50,
        connect: [true, false],
        range: {
          min: 0,
          max: 100
        }
      });
    }
  });
  /*------------Copy To Clipboard---------------*/
  var copy = document.querySelectorAll('[data-toggle="copy"]');
  if (_typeof(copy) !== ( true ? "undefined" : 0)) {
    Array.from(copy, function (elem) {
      elem.addEventListener('click', function (e) {
        var target = elem.getAttribute('data-copy-target');
        var value = elem.getAttribute('data-copy-value');
        var container = document.querySelector(target);
        if (container !== undefined && container !== null) {
          if (container.value !== undefined && container.value !== null) {
            value = container.value;
          } else {
            value = container.innerHTML;
          }
        }
        if (value !== null) {
          var _elem = document.createElement('textarea');
          document.querySelector('body').appendChild(_elem);
          _elem.value = value;
          _elem.select();
          document.execCommand('copy');
          _elem.remove();
        }
        elem.setAttribute('data-bs-original-title', 'Copied!');
        if ((typeof bootstrap === "undefined" ? "undefined" : _typeof(bootstrap)) !== ( true ? "undefined" : 0) && bootstrap.Tooltip) {
          var btn_tooltip = bootstrap.Tooltip.getInstance(elem);
          if (btn_tooltip) {
            btn_tooltip.show();
            // reset the tooltip title
            elem.setAttribute('data-bs-original-title', 'Copy');
            setTimeout(function () {
              btn_tooltip.hide();
            }, 500);
          }
        }
      });
    });
  }
  /*------------Minus-plus--------------*/
  var plusBtns = document.querySelectorAll('.iq-quantity-plus');
  var minusBtns = document.querySelectorAll('.iq-quantity-minus');
  var updateQtyBtn = function updateQtyBtn(elem, value) {
    var oldValue = elem.closest('[data-qty="btn"]').querySelector('[data-qty="input"]').value;
    var newValue = Number(oldValue) + Number(value);
    if (newValue >= 1) {
      elem.closest('[data-qty="btn"]').querySelector('[data-qty="input"]').value = newValue;
    }
  };
  Array.from(plusBtns, function (elem) {
    elem.addEventListener('click', function (e) {
      updateQtyBtn(elem, 1);
    });
  });
  Array.from(minusBtns, function (elem) {
    elem.addEventListener('click', function (e) {
      updateQtyBtn(elem, -1);
    });
  });
  /*------------Flatpickr--------------*/
  var date_flatpickr = document.querySelectorAll('.date_flatpicker');
  Array.from(date_flatpickr, function (elem) {
    if ((typeof flatpickr === "undefined" ? "undefined" : _typeof(flatpickr)) !== ( true ? "undefined" : 0)) {
      flatpickr(elem, {
        minDate: 'today',
        dateFormat: 'Y-m-d'
      });
    }
  });
  /*----------Range Flatpickr--------------*/
  var range_flatpicker = document.querySelectorAll('.range_flatpicker');
  Array.from(range_flatpicker, function (elem) {
    if ((typeof flatpickr === "undefined" ? "undefined" : _typeof(flatpickr)) !== ( true ? "undefined" : 0)) {
      flatpickr(elem, {
        mode: 'range',
        minDate: 'today',
        dateFormat: 'Y-m-d'
      });
    }
  });
  /*------------Wrap Flatpickr---------------*/
  var wrap_flatpicker = document.querySelectorAll('.wrap_flatpicker');
  Array.from(wrap_flatpicker, function (elem) {
    if ((typeof flatpickr === "undefined" ? "undefined" : _typeof(flatpickr)) !== ( true ? "undefined" : 0)) {
      flatpickr(elem, {
        wrap: true,
        minDate: 'today',
        dateFormat: 'Y-m-d'
      });
    }
  });
  /*-------------Time Flatpickr---------------*/
  var time_flatpickr = document.querySelectorAll('.time_flatpicker');
  Array.from(time_flatpickr, function (elem) {
    if ((typeof flatpickr === "undefined" ? "undefined" : _typeof(flatpickr)) !== ( true ? "undefined" : 0)) {
      flatpickr(elem, {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i'
      });
    }
  });
  /*-------------Inline Flatpickr-----------------*/
  var inline_flatpickr = document.querySelectorAll('.inline_flatpickr');
  Array.from(inline_flatpickr, function (elem) {
    if ((typeof flatpickr === "undefined" ? "undefined" : _typeof(flatpickr)) !== ( true ? "undefined" : 0)) {
      flatpickr(elem, {
        inline: true,
        minDate: 'today',
        dateFormat: 'Y-m-d'
      });
    }
  });

  /*-------------CounterUp 2--------------*/
  if (window.counterUp !== undefined) {
    var counterUp = window.counterUp['default'];
    var counterUp2 = document.querySelectorAll('.counter');
    Array.from(counterUp2, function (el) {
      if ((typeof Waypoint === "undefined" ? "undefined" : _typeof(Waypoint)) !== ( true ? "undefined" : 0)) {
        var waypoint = new Waypoint({
          element: el,
          handler: function handler() {
            counterUp(el, {
              duration: 1000,
              delay: 10
            });
            this.destroy();
          },
          offset: 'bottom-in-view'
        });
      }
    });
  }

  /*----------------SliderTab------------------*/
  Array.from(document.querySelectorAll('[data-toggle="slider-tab"]'), function (elem) {
    if ((typeof SliderTab === "undefined" ? "undefined" : _typeof(SliderTab)) !== ( true ? "undefined" : 0)) {
      new SliderTab(elem);
    }
  });
  var Scrollbar;
  if (_typeof(Scrollbar) !== _typeof(null)) {
    if (document.querySelectorAll('.data-scrollbar').length) {
      Scrollbar = window.Scrollbar;
      Scrollbar.init(document.querySelector('.data-scrollbar'), {
        continuousScrolling: false,
        alwaysShowTracks: false
      });
    }
  }
  /*-------------Data tables---------------*/
  if ($.fn.DataTable) {
    // Bootstrap DataTable
    if ($('[data-toggle="data-table"]').length) {
      $('[data-toggle="data-table"]').DataTable({
        autoWidth: false,
        dom: '<"row align-items-center"<"col-md-6" l><"col-md-6" f>><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" i><"col-md-6" p>><"clear">'
      });
    }
    // Column hidden datatable
    if ($('[data-toggle="data-table-column-hidden"]').length) {
      var hiddentable = $('[data-toggle="data-table-column-hidden"]').DataTable({});
      $('a.toggle-vis').on('click', function (e) {
        e.preventDefault();
        var column = hiddentable.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });
    }
    // Column filter datatable
    if ($('[data-toggle="data-table-column-filter"]').length) {
      $('[data-toggle="data-table-column-filter"] tfoot th').each(function () {
        var title = $(this).attr('title');
        $(this).html("<td><input type=\"text\" class=\"form-control form-control-sm\" placeholder=\"".concat(title, "\" /></td>"));
      });
      $('[data-toggle="data-table-column-filter"]').DataTable({
        initComplete: function initComplete() {
          this.api().columns().every(function () {
            var that = this;
            $('input', this.footer()).on('keyup change clear', function () {
              if (that.search() !== this.value) {
                that.search(this.value).draw();
              }
            });
          });
        }
      });
    }
    // Multilanguage datatable
    if ($('[data-toggle="data-table-multi-language"]').length) {
      var languageSelect = function languageSelect() {
        return Array.from(document.querySelector('#langSelector').options).filter(function (option) {
          return option.selected;
        }).map(function (option) {
          return option.getAttribute('data-path');
        });
      };
      var dataTableInit = function dataTableInit() {
        $('[data-toggle="data-table-multi-language"]').DataTable({
          language: {
            url: languageSelect()
          }
        });
      };
      dataTableInit();
      document.querySelector('#langSelector').addEventListener('change', function (e) {
        $('[data-toggle="data-table-multi-language"]').dataTable().fnDestroy();
        dataTableInit();
      });
    }
  }

  /*--------------Active Class for Pricing Table------------------------*/
  var tableTh = document.querySelectorAll('#my-table tr th');
  var tableTd = document.querySelectorAll('#my-table td');
  if (tableTh !== null) {
    Array.from(tableTh, function (elem) {
      elem.addEventListener('click', function (e) {
        Array.from(tableTh, function (th) {
          if (th.children.length) {
            th.children[0].classList.remove('active');
          }
        });
        elem.children[0].classList.add('active');
        Array.from(tableTd, function (td) {
          return td.classList.remove('active');
        });
        var col = Array.prototype.indexOf.call(document.querySelector('#my-table tr').children, elem);
        var tdIcons = document.querySelectorAll('#my-table tr td:nth-child(' + parseInt(col + 1) + ')');
        Array.from(tdIcons, function (td) {
          return td.classList.add('active');
        });
      });
    });
  }
  /*------------Resize Plugins--------------*/
  var resizePlugins = function resizePlugins() {
    // For sidebar-mini & responsive
    var tabs = document.querySelectorAll('.nav');
    var sidebarResponsive = document.querySelector('[data-sidebar="responsive"]');
    if (window.innerWidth < 1025) {
      Array.from(tabs, function (elem) {
        if (!elem.classList.contains('flex-column') && elem.classList.contains('nav-tabs') && elem.classList.contains('nav-pills')) {
          elem.classList.add('flex-column', 'on-resize');
        }
      });
      if (sidebarResponsive !== null) {
        if (!sidebarResponsive.classList.contains('sidebar-mini')) {
          sidebarResponsive.classList.add('sidebar-mini', 'on-resize');
        }
      }
    } else {
      Array.from(tabs, function (elem) {
        if (elem.classList.contains('on-resize')) {
          elem.classList.remove('flex-column', 'on-resize');
        }
      });
      if (sidebarResponsive !== null) {
        if (sidebarResponsive.classList.contains('sidebar-mini') && sidebarResponsive.classList.contains('on-resize')) {
          sidebarResponsive.classList.remove('sidebar-mini', 'on-resize');
        }
      }
    }
  };
  /*-------------Sidebar Toggle-----------------*/
  function updateSidebarType() {
    if ((typeof IQSetting === "undefined" ? "undefined" : _typeof(IQSetting)) !== ( true ? "undefined" : 0)) {
      var sidebarType = IQSetting.options.setting.sidebar_type.value;
      var newTypes = sidebarType;
      if (sidebarType.includes('sidebar-mini')) {
        var indexOf = newTypes.findIndex(function (x) {
          return x == 'sidebar-mini';
        });
        newTypes.splice(indexOf, 1);
      } else {
        newTypes.push('sidebar-mini');
      }
      IQSetting.sidebar_type(newTypes);
    }
  }
  var sidebarToggle = function sidebarToggle(elem) {
    elem.addEventListener('click', function (e) {
      var sidebar = document.querySelector('.sidebar');
      if (sidebar.classList.contains('sidebar-mini')) {
        sidebar.classList.remove('sidebar-mini');
        updateSidebarType();
      } else {
        sidebar.classList.add('sidebar-mini');
        updateSidebarType();
      }
    });
  };
  var sidebarToggleBtn = document.querySelectorAll('[data-toggle="sidebar"]');
  Array.from(sidebarToggleBtn, function (sidebarBtn) {
    sidebarToggle(sidebarBtn);
  });

  /*----------------Back To Top--------------------*/
  var backToTop = document.getElementById('back-to-top');
  if (backToTop !== null && backToTop !== undefined) {
    document.getElementById('back-to-top').classList.add('animate__animated', 'animate__fadeOut');
    window.addEventListener('scroll', function (e) {
      if (document.documentElement.scrollTop > 250) {
        document.getElementById('back-to-top').classList.remove('animate__fadeOut');
        document.getElementById('back-to-top').classList.add('animate__fadeIn');
      } else {
        document.getElementById('back-to-top').classList.remove('animate__fadeIn');
        document.getElementById('back-to-top').classList.add('animate__fadeOut');
      }
    });
    // scroll body to 0px on click
    document.querySelector('#top').addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
  /*------------DOMContentLoaded--------------*/
  document.addEventListener('DOMContentLoaded', function (event) {
    resizePlugins();
    loaderInit();
  });
  /*------------Window Resize------------------*/
  window.addEventListener('resize', function (event) {
    resizePlugins();
  });
  /*--------DropDown--------*/

  function darken_screen(yesno) {
    if (yesno == true) {
      if (document.querySelector('.screen-darken') !== null) {
        document.querySelector('.screen-darken').classList.add('active');
      }
    } else if (yesno == false) {
      if (document.querySelector('.screen-darken') !== null) {
        document.querySelector('.screen-darken').classList.remove('active');
      }
    }
  }
  function close_offcanvas() {
    darken_screen(false);
    if (document.querySelector('.mobile-offcanvas.show') !== null) {
      document.querySelector('.mobile-offcanvas.show').classList.remove('show');
      document.body.classList.remove('offcanvas-active');
    }
  }
  function show_offcanvas(offcanvas_id) {
    darken_screen(true);
    if (document.getElementById(offcanvas_id) !== null) {
      document.getElementById(offcanvas_id).classList.add('show');
      document.body.classList.add('offcanvas-active');
    }
  }
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-trigger]').forEach(function (everyelement) {
      var offcanvas_id = everyelement.getAttribute('data-trigger');
      everyelement.addEventListener('click', function (e) {
        e.preventDefault();
        show_offcanvas(offcanvas_id);
      });
    });
    if (document.querySelectorAll('.btn-close')) {
      document.querySelectorAll('.btn-close').forEach(function (everybutton) {
        everybutton.addEventListener('click', function (e) {
          close_offcanvas();
        });
      });
    }
    if (document.querySelector('.screen-darken')) {
      document.querySelector('.screen-darken').addEventListener('click', function (event) {
        close_offcanvas();
      });
    }
  });
  if (document.querySelector('#navbarSideCollapse')) {
    document.querySelector('#navbarSideCollapse').addEventListener('click', function () {
      document.querySelector('.offcanvas-collapse').classList.toggle('open');
    });
  }
  var toggleelem = document.getElementById('navbarSupportedContent');
  var offcanvasheader = document.getElementById('offcanvasBottom');
  if (offcanvasheader !== null && offcanvasheader !== undefined && (typeof bootstrap === "undefined" ? "undefined" : _typeof(bootstrap)) !== ( true ? "undefined" : 0) && bootstrap.Offcanvas) {
    var bsOffcanvas = new bootstrap.Offcanvas(offcanvasheader);
    if (toggleelem) {
      toggleelem.addEventListener('show.bs.collapse', function () {
        bsOffcanvas.show();
        var backdrop = document.querySelector('.offcanvas-backdrop');
        if (backdrop) {
          backdrop.addEventListener('click', function () {
            if (bootstrap.Collapse) {
              var toggleInstace = bootstrap.Collapse.getInstance(toggleelem);
              if (toggleInstace) {
                toggleInstace.hide();
              }
            }
          });
        }
      });
      toggleelem.addEventListener('hide.bs.collapse', function () {
        bsOffcanvas.hide();
      });
    }
  }
  /*---------------Form Validation--------------------*/
  // Example starter JavaScript for disabling form submissions if there are invalid fields
  window.addEventListener('load', function () {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function (form) {
      form.addEventListener('submit', function (event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
  $(document).on('click', '.btn', function (e) {
    $(this).trigger('blur');
  });
  // Snackbar Message
  var snackbarMessage = function snackbarMessage() {
    var PRIMARY_COLOR = window.getComputedStyle(document.querySelector('html')).getPropertyValue('--bs-success').trim();
    var DANGER_COLOR = window.getComputedStyle(document.querySelector('html')).getPropertyValue('--bs-danger').trim();
    var successSnackbar = function successSnackbar(message) {
      node_snackbar__WEBPACK_IMPORTED_MODULE_0___default().show({
        text: message,
        pos: 'bottom-left',
        actionTextColor: PRIMARY_COLOR,
        duration: 2500
      });
    };
    window.successSnackbar = successSnackbar;
    var errorSnackbar = function errorSnackbar(message) {
      node_snackbar__WEBPACK_IMPORTED_MODULE_0___default().show({
        text: message,
        pos: 'bottom-left',
        actionTextColor: '#FFFFFF',
        backgroundColor: DANGER_COLOR,
        duration: 2500
      });
    };
    window.errorSnackbar = errorSnackbar;
  };
  snackbarMessage();

  /*
    Exemples :
    <a href="posts/2" data-method="delete" data-token="{{csrf_token()}}">
    - Or, request confirmation in the process -
    <a href="posts/2" data-method="delete" data-token="{{csrf_token()}}" data-confirm="Are you sure?">
    */

  window.laravel = {
    initialize: function initialize() {
      this.methodLinks = $('[data-method]');
      this.token = $('[data-token]');
      this.registerEvents();
      window.tooltipInit();
      if ($('#quick-action-type').val() == '') {
        $('#quick-action-apply').attr('disabled', true);
      }
    },
    registerEvents: function registerEvents() {
      // Use event delegation to handle dynamically loaded content
      // Unbind previous handlers to avoid duplicate bindings
      $(document).off('click', '[data-method]', this.handleMethod);
      $(document).on('click', '[data-method]', this.handleMethod);
    },
    ajaxSubmitForm: function ajaxSubmitForm(e) {
      var URL = $(this).attr('action');
      var DATA = $(this).serialize();
      var __this = $(this);
      e.preventDefault();
      $.ajax({
        type: 'POST',
        url: URL,
        data: DATA,
        dataType: 'json',
        success: function success(res) {
          if (res.status) {
            // window.successSnackbar(res.message)
            Swal.fire({
              title: 'Deleted',
              text: res.message,
              icon: 'success',
              showClass: {
                popup: 'animate__animated animate__zoomIn'
              },
              hideClass: {
                popup: 'animate__animated animate__zoomOut'
              }
            });
            if (window.renderedDataTable) {
              window.renderedDataTable.ajax.reload(null, false);
            } else if (typeof renderedDataTable !== 'undefined') {
              renderedDataTable.ajax.reload(null, false);
            }
            __this.remove();
          } else {
            if (res.message) {
              Swal.fire({
                title: 'Error',
                text: res.message,
                icon: 'error',
                showClass: {
                  popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                  popup: 'animate__animated animate__zoomOut'
                }
              });
              __this.remove();
            }
          }
        },
        error: function error(err) {
          var wrapper = document.createElement('div');
          wrapper.innerHTML = err.responseText;
          Swal.fire({
            title: err.statusText,
            text: wrapper.innerHTML,
            icon: 'error',
            showClass: {
              popup: 'animate__animated animate__zoomIn'
            },
            hideClass: {
              popup: 'animate__animated animate__zoomOut'
            }
          });
          __this.remove();
        }
      });
    },
    acceptSubmitForm: function acceptSubmitForm(e) {
      var URL = $(this).attr('action');
      var DATA = $(this).serialize();
      var __this = $(this);
      e.preventDefault();
      $.ajax({
        type: 'POST',
        url: URL,
        data: DATA,
        dataType: 'json',
        success: function success(res) {
          if (res.status) {
            // window.successSnackbar(res.message)
            Swal.fire({
              title: 'Done',
              text: res.message,
              icon: 'success'
            });
            renderedDataTable.ajax.reload(null, false);
            __this.remove();
          } else {
            if (res.message) {
              Swal.fire({
                title: 'Error',
                text: res.message,
                icon: 'error'
              });
              __this.remove();
            }
          }
        },
        error: function error(err) {
          var wrapper = document.createElement('div');
          wrapper.innerHTML = err.responseText;
          Swal.fire({
            title: err.statusText,
            text: wrapper.innerHTML,
            icon: 'error'
          });
          __this.remove();
        }
      });
    },
    handleMethod: function handleMethod(e) {
      e.preventDefault();
      var link = $(this);
      var httpMethod = link.data('method').toUpperCase();
      var form;

      // If the data-method attribute is not PUT, PATCH or DELETE,
      // Then we don't know what to do. Just ignore.
      if ($.inArray(httpMethod, ['PUT', 'DELETE', 'PATCH', 'GET']) === -1) {
        return;
      }

      // Allow user to optionally provide data-confirm="Are you sure?"
      if (link.data('confirm')) {
        if (httpMethod == 'GET') {
          window.laravel.verifyConfirmdata(link).then(function (res) {
            if (res.isConfirmed) {
              var formID = 'form-' + link.attr('id');
              form = window.laravel.createForm(link, formID);
              if (link.data('type') == 'ajax') {
                $('#' + formID).on('submit', window.laravel.acceptSubmitForm);
              }
              form.submit();
            } else {
              return false;
            }
          });
        } else {
          window.laravel.verifyConfirm(link).then(function (res) {
            if (res.isConfirmed) {
              var formID = 'form-' + link.attr('id');
              form = window.laravel.createForm(link, formID);
              if (link.data('type') == 'ajax') {
                $('#' + formID).on('submit', window.laravel.ajaxSubmitForm);
              }
              form.submit();
            } else {
              return false;
            }
          });
        }
      }
    },
    verifyConfirm: function () {
      var _verifyConfirm = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(link) {
        var _window$localMessages, _window$localMessages2;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return Swal.fire({
                title: link.data('confirm'),
                icon: 'question',
                // iconColor:'primary',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#858482',
                confirmButtonText: ((_window$localMessages = window.localMessagesUpdate) === null || _window$localMessages === void 0 || (_window$localMessages = _window$localMessages.messages) === null || _window$localMessages === void 0 ? void 0 : _window$localMessages.yes) || 'Yes',
                cancelButtonText: ((_window$localMessages2 = window.localMessagesUpdate) === null || _window$localMessages2 === void 0 || (_window$localMessages2 = _window$localMessages2.messages) === null || _window$localMessages2 === void 0 ? void 0 : _window$localMessages2.cancel) || 'Cancel',
                showClass: {
                  popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                  popup: 'animate__animated animate__zoomOut'
                }
              }).then(function (result) {
                return result;
              });
            case 2:
              return _context.abrupt("return", _context.sent);
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }));
      function verifyConfirm(_x) {
        return _verifyConfirm.apply(this, arguments);
      }
      return verifyConfirm;
    }(),
    verifyConfirmdata: function () {
      var _verifyConfirmdata = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(link) {
        var _window$localMessages3, _window$localMessages4;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.next = 2;
              return Swal.fire({
                title: link.data('confirm'),
                icon: 'question',
                // iconColor:'primary',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#858482',
                confirmButtonText: ((_window$localMessages3 = window.localMessagesUpdate) === null || _window$localMessages3 === void 0 || (_window$localMessages3 = _window$localMessages3.messages) === null || _window$localMessages3 === void 0 ? void 0 : _window$localMessages3.yes) || 'Yes',
                cancelButtonText: ((_window$localMessages4 = window.localMessagesUpdate) === null || _window$localMessages4 === void 0 || (_window$localMessages4 = _window$localMessages4.messages) === null || _window$localMessages4 === void 0 ? void 0 : _window$localMessages4.cancel) || 'Cancel'
              }).then(function (result) {
                return result;
              });
            case 2:
              return _context2.abrupt("return", _context2.sent);
            case 3:
            case "end":
              return _context2.stop();
          }
        }, _callee2);
      }));
      function verifyConfirmdata(_x2) {
        return _verifyConfirmdata.apply(this, arguments);
      }
      return verifyConfirmdata;
    }(),
    createForm: function createForm(link, formID) {
      var form = $('<form>', {
        method: 'POST',
        id: formID,
        action: link.attr('href')
      });
      var token = $('<input>', {
        type: 'hidden',
        name: '_token',
        value: link.data('token')
      });
      var hiddenInput = $('<input>', {
        name: '_method',
        type: 'hidden',
        value: link.data('method')
      });
      return form.append(token, hiddenInput).appendTo('body');
    }
  };
})();
})();

/******/ })()
;