"use strict";function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function _defineProperties(t,e){for(var o=0;o<e.length;o++){var a=e[o];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(t,a.key,a)}}function _createClass(t,e,o){return e&&_defineProperties(t.prototype,e),o&&_defineProperties(t,o),Object.defineProperty(t,"prototype",{writable:!1}),t}var Public=function(){function t(){_classCallCheck(this,t)}return _createClass(t,[{key:"init",value:function(){if(void 0===window.mtpShortcodeDataObject)return!1;this.baseSelector="makeen-task-shortcode",this.selectors={button:".".concat(this.baseSelector,"-button"),formContainer:".".concat(this.baseSelector,"-form-container")},this.logShortcodeData(),this.initEventHandlers()}},{key:"logShortcodeData",value:function(){var t=window.mtpShortcodeDataObject.shortcodes;0!==Object.keys(t).length&&Object.keys(t).forEach((function(e,o){var a=t[e];console.log(a.metaData)}))}},{key:"initEventHandlers",value:function(){jQuery(document).on("click",this.selectors.button,this.handleShortcodeButtonClick)}},{key:"handleShortcodeButtonClick",value:function(t){t.preventDefault();var e=jQuery(t.target),o=e.attr("data-shortcode-id"),a=window.mtpShortcodeDataObject.shortcodes[o];if(e.attr("disabled")||!a)return!1;e.attr("disabled","disabled"),jQuery.ajax({type:"POST",url:window.mtpShortcodeDataObject.securityData.ajaxUrl,data:{action:window.mtpShortcodeDataObject.securityData.action,nonce:a.securityData.nonce,formId:a.metaData.frm_id,shortcodeId:a.shortcode.id},success:window.Public.handleShortcodeSuccess,error:window.Public.handleShortcodeError})}},{key:"handleShortcodeSuccess",value:function(t){var e=JSON.parse(t),o=e.data.form_id,a=e.data.shortcode_id,r=jQuery("".concat(window.Public.selectors.button,'[data-shortcode-id="').concat(a,'"][data-form-id="').concat(o,'"]'));if(r.length>0&&r.remove(),!e.success)return e.messages.forEach((function(t,e){alert(t)})),!1;var n=jQuery("".concat(window.Public.selectors.formContainer,'[data-shortcode-id="').concat(a,'"][data-form-id="').concat(o,'"]'));n.length>0&&n.html(e.data.markup)}},{key:"handleShortcodeError",value:function(t){console.error(t),jQuery(window.Public.selectors.button).removeAttr("disabled")}}]),t}();window.Public=new Public,window.addEventListener("load",(function(t){window.Public.init()}));
//# sourceMappingURL=public.js.map