this.BX=this.BX||{},this.BX.YandexPay=this.BX.YandexPay||{},this.BX.YandexPay.Solution=this.BX.YandexPay.Solution||{},this.BX.YandexPay.Solution.Aspro=this.BX.YandexPay.Solution.Aspro||{},function(t){"use strict";class e{static make(t={}){return new e(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,e){this.matchEvent("bx")&&this.onBxEvent(t,e),this.matchEvent("jquery")&&this.onJQueryEvent(t,e),this.matchEvent("plain")&&this.onPlainEvent(t,e)}off(t,e){this.matchEvent("bx")&&this.offBxEvent(t,e),this.matchEvent("jquery")&&this.offJQueryEvent(t,e),this.matchEvent("plain")&&this.offPlainEvent(t,e)}fire(t,e={}){this.matchEvent("bx")&&this.fireBxEvent(t,e),this.matchEvent("jquery")&&this.fireJQueryEvent(t,e),this.matchEvent("plain")&&this.firePlainEvent(t,e)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.addCustomEvent&&BX.addCustomEvent(t,e)}offBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.removeCustomEvent&&BX.removeCustomEvent(t,e)}fireBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.onCustomEvent&&BX.onCustomEvent(t,[e])}onJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");if(this.canProxyCallback(n)){const t=e;e=(...e)=>{t(...e)},this.pushCallbackVariation("jquery",t,e)}jQuery(document).on(t,e)}offJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("jquery",e))||jQuery(document).off(t,e)}fireJQueryEvent(t,e){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:e})))}onPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");if(this.canProxyCallback(n)){const t=e;e=e=>{t(e.detail)},this.pushCallbackVariation("plain",t,e)}document.addEventListener(t,e)}offPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("plain",e))||document.removeEventListener(t,e)}firePlainEvent(t,e){document.dispatchEvent(new CustomEvent(t,{detail:e}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,e,n){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(e,n)}popCallbackVariation(t,e){if(null==this.callbackMap[t])return null;const n=this.callbackMap[t],a=n.get(e);return null!=a&&n.delete(e),a}}class n{constructor(){this.eventProxies={}}bootFactory(t){}bootWidget(t){}destroyWidget(t){}bootCart(t){}destroyCart(t){}onEvent(t,n,a={}){null==this.eventProxies[t]&&(this.eventProxies[t]=e.make(a)),this.eventProxies[t].on(t,n)}offEvent(t,e,n={}){null!=this.eventProxies[t]&&this.eventProxies[t].off(t,e)}}class a extends n{constructor(...t){super(...t),this.eventConfig={jquery:!0,strict:!0},this.onAjaxSuccess=(t,e,n,a)=>{const s=n.url;if("string"==typeof s&&0===s.indexOf("/ajax/amount.php")){let t,e,n=a;if("object"!=typeof n)return;if(!n.success)return;for(t in null==n?void 0:n.amount)isNaN(parseInt(t,10))||(e=parseInt(t,10));if(null==e)return;this.cart.delayChangeOffer(e)}}}bootCart(t){this.cart=t,this.onStarterOffer(),this.handleCommonOffer(!0)}destroyCart(t){this.cart=null,this.handleCommonOffer(!1)}onStarterOffer(){var t;const e=document.querySelector(".sku-props.sku-props--detail"),n=parseInt(null==e||null==(t=e.dataset)?void 0:t.offerId,10);isNaN(n)||this.cart.changeOffer(n)}handleCommonOffer(t){this[t?"onEvent":"offEvent"]("ajaxSuccess",this.onAjaxSuccess,this.eventConfig)}}class s extends n{constructor(...t){super(...t),this.eventConfig={bx:!0,strict:!0},this.onBasketChange=()=>{this.cart.delayChangeBasket()}}bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:'[data-entity="basket-total-block"]',delay:null}}})}bootCart(t){this.cart=t,this.handleBasketChange(!0)}destroyCart(t){this.cart=null,this.handleBasketChange(!1)}handleBasketChange(t){this[t?"onEvent":"offEvent"]("OnBasketChange",this.onBasketChange,this.eventConfig),this[t?"onEvent":"offEvent"]("OnCouponApply",this.onBasketChange,this.eventConfig)}}class i extends s{}class o extends n{bootFactory(t){t.extendDefaults({preserve:{mutation:{anchor:"#bx-soa-total-mobile, #bx-soa-total, .bx-soa-cart-total",delay:null}}})}}class r extends o{}const l=new class{constructor(t){this.classMap={},this.classMap=t}create(t){const e=this.classMap[t];return null==e?null:new e}}({element:a,elementFast:class extends a{bootFactory(t){t.setOptions({event:"bxYapayFastViewInit",eventConfig:{strict:!0,plain:!0},preserve:{mutation:{anchor:".catalog-detail__cart",delay:100}}});let e=setInterval((function(){2==$(".fast_view_frame.popup .jqmClose.top-close").css("z-index")&&(clearInterval(e),setTimeout((function(){document.dispatchEvent(new CustomEvent("bxYapayFastViewInit"))}),250))}),100)}onStarterOffer(){var t;const e=document.querySelector("#fast_view_item .sku-props.sku-props--detail"),n=parseInt(null==e||null==(t=e.dataset)?void 0:t.offerId,10);isNaN(n)||this.cart.changeOffer(n)}},basket:class extends i{},order:class extends r{}});t.factory=l}(this.BX.YandexPay.Solution.Aspro.Lite=this.BX.YandexPay.Solution.Aspro.Lite||{});
//# sourceMappingURL=solution.js.map
