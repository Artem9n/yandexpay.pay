this.BX=this.BX||{},this.BX.YandexPay=this.BX.YandexPay||{},this.BX.YandexPay.Solution=this.BX.YandexPay.Solution||{},this.BX.YandexPay.Solution.Aspro=this.BX.YandexPay.Solution.Aspro||{},function(t){"use strict";class e{static make(t={}){return new e(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,e){this.matchEvent("bx")&&this.onBxEvent(t,e),this.matchEvent("jquery")&&this.onJQueryEvent(t,e),this.matchEvent("plain")&&this.onPlainEvent(t,e)}off(t,e){this.matchEvent("bx")&&this.offBxEvent(t,e),this.matchEvent("jquery")&&this.offJQueryEvent(t,e),this.matchEvent("plain")&&this.offPlainEvent(t,e)}fire(t,e={}){this.matchEvent("bx")&&this.fireBxEvent(t,e),this.matchEvent("jquery")&&this.fireJQueryEvent(t,e),this.matchEvent("plain")&&this.firePlainEvent(t,e)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.addCustomEvent&&BX.addCustomEvent(t,e)}offBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.removeCustomEvent&&BX.removeCustomEvent(t,e)}fireBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.onCustomEvent&&BX.onCustomEvent(t,[e])}onJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");if(this.canProxyCallback(n)){const t=e;e=(...e)=>{t(...e)},this.pushCallbackVariation("jquery",t,e)}jQuery(document).on(t,e)}offJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("jquery",e))||jQuery(document).off(t,e)}fireJQueryEvent(t,e){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:e})))}onPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");if(this.canProxyCallback(n)){const t=e;e=e=>{t(e.detail)},this.pushCallbackVariation("plain",t,e)}document.addEventListener(t,e)}offPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("plain",e))||document.removeEventListener(t,e)}firePlainEvent(t,e){document.dispatchEvent(new CustomEvent(t,{detail:e}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,e,n){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(e,n)}popCallbackVariation(t,e){if(null==this.callbackMap[t])return null;const n=this.callbackMap[t],i=n.get(e);return null!=i&&n.delete(e),i}}class n{constructor(){this.eventProxies={}}bootFactory(t){}bootWidget(t){}destroyWidget(t){}bootCart(t){}destroyCart(t){}onEvent(t,n,i={}){null==this.eventProxies[t]&&(this.eventProxies[t]=e.make(i)),this.eventProxies[t].on(t,n)}offEvent(t,e,n={}){null!=this.eventProxies[t]&&this.eventProxies[t].off(t,e)}}class i extends n{constructor(...t){super(...t),this.eventConfig={bx:!0,strict:!0},this.onStarterOffer=t=>{let e=this.eventProductId(t);null!=e&&(this.initialProduct=e)},this.onCommonOffer=t=>{let e=this.eventProductId(t);null!=e&&this.cart.delayChangeOffer(e)}}bootFactory(t){this.handleStarterOffer(!0)}bootCart(t){this.cart=t,this.handleStarterOffer(!1),this.bootInitialProduct(),this.handleCommonOffer(!0)}destroyCart(t){this.cart=null,this.handleStarterOffer(!1),this.handleCommonOffer(!1)}handleStarterOffer(t){this[t?"onEvent":"offEvent"](this.eventName(),this.onStarterOffer,this.eventConfig)}handleCommonOffer(t){this[t?"onEvent":"offEvent"](this.eventName(),this.onCommonOffer,this.eventConfig)}eventName(){throw new Error("not implemented")}eventProductId(t){let e=parseInt(null==t?void 0:t.newId,10);return isNaN(e)?null:e}bootInitialProduct(){null!=this.initialProduct&&(this.cart.changeOffer(this.initialProduct),this.initialProduct=null)}}class a extends n{constructor(...t){super(...t),this.eventConfig={bx:!0,strict:!0},this.onBasketChange=()=>{this.cart.delayChangeBasket()}}bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:'[data-entity="basket-total-block"]',delay:null}}})}bootCart(t){this.cart=t,this.handleBasketChange(!0)}destroyCart(t){this.cart=null,this.handleBasketChange(!1)}handleBasketChange(t){this[t?"onEvent":"offEvent"]("OnBasketChange",this.onBasketChange,this.eventConfig)}}class o extends n{bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:"#bx-soa-total-mobile, #bx-soa-total, .bx-soa-cart-total",delay:null}}})}}const s=new class{constructor(t){this.classMap={},this.classMap=t}create(t){const e=this.classMap[t];return null==e?null:new e}}({element:class extends i{constructor(...t){super(...t),this.onStarterOffer=()=>{var t;const e=document.querySelector(".to-cart:not(.read_more)"),n=parseInt(null==e||null==(t=e.dataset)?void 0:t.item,10);isNaN(n)||this.cart.changeOffer(n)}}bootFactory(t){}bootCart(t){this.cart=t,this.onStarterOffer(),this.handleCommonOffer(!0)}destroyCart(t){this.cart=null,this.handleCommonOffer(!1)}eventName(){return"onAsproSkuSetPrice"}eventProductId(t){var e;const n=parseInt(null==t||null==(e=t.offer)?void 0:e.ID,10);return isNaN(n)?null:n}},basket:class extends a{},order:class extends o{}});t.factory=s}(this.BX.YandexPay.Solution.Aspro.Base=this.BX.YandexPay.Solution.Aspro.Base||{});
//# sourceMappingURL=solution.js.map
