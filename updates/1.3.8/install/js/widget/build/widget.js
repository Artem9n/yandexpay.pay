this.BX=this.BX||{},function(t){"use strict";class e{static getFactory(t){var e,n,i,s,o;if(null==t)return null;const r=null==(e=window)||null==(n=e.BX)||null==(i=n.YandexPay)||null==(s=i.Solution)||null==(o=s[t])?void 0:o.factory;if(null!=r)return r;var a;null==(a=console)||a.warn(`cant find solution ${t}`)}static getPage(t,e){if(null==t||null==e)return null;const n=t+":"+e;return null==this.pages[n]&&(this.pages[n]=this.createPage(t,e)),this.pages[n]}static createPage(t,e){const n=this.getFactory(t);return null==n?null:n.create(e)}}e.pages={};class n{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e)}destroy(){}}n.defaults={check:null};class i extends n{constructor(t,e={}){super(t,e),this.loop=()=>{this.options.check()&&this.loopTimeout()},this.loopTimeout()}destroy(){this.loopCancel()}loopTimeout(){clearTimeout(this._loopTimeout),this._loopTimeout=setTimeout(this.loop,this.options.timeout)}loopCancel(){clearTimeout(this._loopTimeout)}}i.defaults=Object.assign({},n.defaults,{timeout:1e3});class s extends n{constructor(t,e={}){super(t,e),this.listener=t=>{for(const e of t)if(null!=e.removedNodes)for(const t of e.removedNodes)if(t instanceof HTMLElement&&(t===this.el||t.contains(this.el)))return void this.runCheck()},this.observe()}destroy(){this.disconnect()}observe(){const t=this.getAnchor();var e;null!=t?(this.observer=new window.MutationObserver(this.listener),this.observer.observe(t,{childList:!0,subtree:!0})):null==(e=console)||e.warn("cant find anchor for node preserver")}disconnect(){null!=this.observer&&(this.observer.disconnect(),this.observer=null)}runCheck(){const t=this.options.delay;null==t?this.options.check():(clearTimeout(this._checkTimeout),this._checkTimeout=setTimeout((()=>{this.options.check()}),t))}getAnchor(){if(null==this.options.anchor)return document.body;let t=null;for(let e of this.options.anchor.split(","))if(e=e.trim(),""!==e&&(t=this.el.closest(e),null!=t))break;return t}}s.defaults=Object.assign({},n.defaults,{anchor:null,delay:0});class o{static make(t={}){return new o(t)}constructor(t={}){this.config=t,this.callbackMap={}}on(t,e){this.matchEvent("bx")&&this.onBxEvent(t,e),this.matchEvent("jquery")&&this.onJQueryEvent(t,e),this.matchEvent("plain")&&this.onPlainEvent(t,e)}off(t,e){this.matchEvent("bx")&&this.offBxEvent(t,e),this.matchEvent("jquery")&&this.offJQueryEvent(t,e),this.matchEvent("plain")&&this.offPlainEvent(t,e)}fire(t,e={}){this.matchEvent("bx")&&this.fireBxEvent(t,e),this.matchEvent("jquery")&&this.fireJQueryEvent(t,e),this.matchEvent("plain")&&this.firePlainEvent(t,e)}matchEvent(t){return null!=this.config[t]?!!this.config[t]:!this.config.strict}onBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.addCustomEvent&&BX.addCustomEvent(t,e)}offBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.removeCustomEvent&&BX.removeCustomEvent(t,e)}fireBxEvent(t,e){"undefined"!=typeof BX&&null!=BX.onCustomEvent&&BX.onCustomEvent(t,[e])}onJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");if(this.canProxyCallback(n)){const t=e;e=(e,n)=>{var i;const s=null!=n?n:null==e||null==(i=e.originalEvent)?void 0:i.detail;t(s)},this.pushCallbackVariation("jquery",t,e)}jQuery(document).on(t,e)}offJQueryEvent(t,e){if("undefined"==typeof jQuery)return;const n=this.typeConfig("jquery");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("jquery",e))||jQuery(document).off(t,e)}fireJQueryEvent(t,e){"undefined"!=typeof jQuery&&(this.hasPlainAndJQueryCollision()||jQuery(document).triggerHandler(new CustomEvent(t,{detail:e})))}onPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");if(this.canProxyCallback(n)){const t=e;e=e=>{t(e.detail)},this.pushCallbackVariation("plain",t,e)}document.addEventListener(t,e)}offPlainEvent(t,e){if(this.hasPlainAndJQueryCollision())return;const n=this.typeConfig("plain");this.canProxyCallback(n)&&null==(e=this.popCallbackVariation("plain",e))||document.removeEventListener(t,e)}firePlainEvent(t,e){document.dispatchEvent(new CustomEvent(t,{detail:e}))}hasPlainAndJQueryCollision(){if(!this.matchEvent("plain")||!this.matchEvent("jquery"))return!1;return!0!==this.typeConfig("plain").force&&"undefined"!=typeof jQuery}canProxyCallback(t){return!1!==t.proxy}typeConfig(t){return"object"==typeof this.config[t]&&null!=this.config[t]?this.config:{}}pushCallbackVariation(t,e,n){null==this.callbackMap[t]&&(this.callbackMap[t]=new WeakMap),this.callbackMap[t].set(e,n)}popCallbackVariation(t,e){if(null==this.callbackMap[t])return null;const n=this.callbackMap[t],i=n.get(e);return null!=i&&n.delete(e),i}}class r{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e),this.eventProxy=new o(this.options.config),this.bind()}destroy(){this.unbind(),this.eventProxy=null,this.options={},this.el=null}bind(){this.bindOn(),this.bindEvent()}unbind(){this.unbindOff(),this.unbindEvent()}bindOn(){null!=this.options.on&&this.options.on(this.options.check)}unbindOff(){null!=this.options.off&&this.options.off(this.options.check)}bindEvent(){const t=this.options.event;if(null!=t)if("string"==typeof t)this.eventProxy.on(t,this.options.check);else if(Array.isArray(t))t.forEach((t=>{this.eventProxy.on(t,this.options.check)}));else{var e;null==(e=console)||e.warn("unknown event type "+typeof t)}}unbindEvent(){const t=this.options.event;if(null!=t)if("string"==typeof t)this.eventProxy.off(t,this.options.check);else if(Array.isArray(t))t.forEach((t=>{this.eventProxy.off(t,this.options.check)}));else{var e;null==(e=console)||e.warn("unknown event type "+typeof t)}}}r.defaults={check:null,event:null,config:{},on:null,off:null};class a{constructor(t,e={}){this.onFrameReady=()=>{this.options.check()},this.el=t,this.options=Object.assign({},this.constructor.defaults,e),this.boot()}destroy(){var t;"undefined"==typeof BX&&(null==(t=BX)||t.removeCustomEvent("onFrameDataReceived",this.onFrameReady))}boot(){var t;const e=this.anchor();"undefined"!=typeof BX&&null==e||null==(t=BX)||t.addCustomEvent("onFrameDataReceived",this.onFrameReady)}anchor(){let t=this.el.parentElement;for(;t;){let e=!1,n=[];for(const i of t.children)if(null==i.id!=null&&/^bxdynamic_.*_start$/.test(i.id))e=!0;else if(null==i.id!=null&&/^bxdynamic_.*_end$/.test(i.id)){e=!1;for(const t of n)if(t===this.el||t.contains(this.el))return t;n=[]}else e&&n.push(i);t=t.parentElement}return null}}a.defaults={check:null};class l{constructor(t,e={}){this.check=()=>{const t=document.body.contains(this.el);return t||this.options.restore(),t},this.el=t,this.options=Object.assign({},this.constructor.defaults,e),this.install()}destroy(){this.uninstall(),this.options={},this.el=null}install(){this.installMutation(),this.installSubscriber(),this.installComposite()}uninstall(){this.uninstallMutation(),this.uninstallSubscriber(),this.uninstallComposite()}installMutation(){this.isEnabled("mutation")&&(this.mutation=class{static make(t,e){return"function"==typeof window.MutationObserver?new s(t,e):new i(t,e)}}.make(this.el,this.driverOptions("mutation")))}uninstallMutation(){null!=this.mutation&&this.mutation.destroy()}installSubscriber(){this.isEnabled("subscriber")&&(this.subscriber=new r(this.el,this.driverOptions("subscriber")))}uninstallSubscriber(){null!=this.subscriber&&(this.subscriber.destroy(),this.subscriber=null)}installComposite(){this.isEnabled("composite")&&(this.composite=new a(this.el,this.driverOptions("composite")))}uninstallComposite(){null!=this.composite&&this.composite.destroy()}isEnabled(t){return!!this.options[t]}driverOptions(t){const e="object"==typeof this.options[t]?this.options[t]:{},n={check:this.check};return Object.assign({},e,n)}}l.defaults={restore:null,subscriber:null,mutation:!0,composite:!0};class h{static compile(t,e){let n,i,s,o=t;for(n in e)if(e.hasOwnProperty(n)){i="#"+n.toUpperCase()+"#",s=e[n];do{o=o.replace(i,s)}while(-1!==o.indexOf(i))}return o}static toElement(t){const e=document.createElement("div");return e.innerHTML=t,e.firstElementChild}static toElements(t){const e=document.createElement("div");return e.innerHTML=t,[...e.children]}}class c{static getInstance(){return null==this.instance&&(this.instance=new c),this.instance}load(){return null!=this._loadPromise||(this._loadPromise=new Promise(((t,e)=>{if(this.testGlobal())return void t();const n=document.createElement("script"),i=document.getElementsByTagName("script")[0]||document.body;n.type="text/javascript",n.async=!0,n.src="https://pay.yandex.ru/sdk/v1/pay.js",n.onload=()=>{this.loopGlobal((()=>{this._loadPromise=null,t()}))},n.onerror=()=>{this._loadPromise=null,n.remove(),e(new Error("cant load yandex pay sdk library"))},i.parentNode.insertBefore(n,i)}))),this._loadPromise}loopGlobal(t){this.testGlobal()?t():setTimeout((()=>{this.loopGlobal(t)}),100)}testGlobal(){var t;return null!=(null==(t=window.YaPay)?void 0:t.createPayment)}}c.instance=null;class u{constructor(t,e={}){this.el=t,this.options=Object.assign({},this.constructor.defaults,e)}wait(){return new Promise((t=>{"undefined"!=typeof IntersectionObserver?(this.observer=new IntersectionObserver(((e,n)=>{e.forEach((e=>{if(e.isIntersecting){const i=e.target;n.unobserve(i),t(i)}}))})),this.observer.observe(this.el)):t(this.el)}))}restore(t){var e,n;null==(e=this.observer)||e.unobserve(this.el),this.el=t,null==(n=this.observer)||n.observe(this.el)}}u.defaults={};class d{constructor(t,e){this.factory=t,this.options=Object.assign({},this.constructor.defaults,e)}style(){}mount(t,e,n){}unmount(t,e){}getOption(t){return this.options[t]}}d.defaults={};class p extends d{style(){const t=this.collectHeight(),e=this.collectBorder(),n=this.collectWidth();return h.compile(this.getOption("style"),{style:t+e+n})}mount(t,e,n){const i=this.getOption("VARIANT_BUTTON")||YaPay.ButtonTheme.Black,s=this.getOption("WIDTH_BUTTON")||YaPay.ButtonWidth.Max;e.mountButton(t,{type:n,theme:i,width:"OWN"!==s?s:YaPay.ButtonWidth.Max})}unmount(t,e){e.unmountButton(t)}collectHeight(){let t="";return null==(this.getOption("HEIGHT_TYPE_BUTTON")||null)||(t=h.compile(this.getOption("styleHeight"),{id:this.factory.getOption("containerId"),height:this.getOption("HEIGHT_VALUE_BUTTON")||"54"})),t}collectBorder(){var t;let e="";return null==(this.getOption("BORDER_RADIUS_TYPE_BUTTON")||null)||(e=h.compile(this.getOption("styleBorder"),{id:this.factory.getOption("containerId"),border:null!=(t=this.getOption("BORDER_RADIUS_VALUE_BUTTON"))?t:"8"})),e}collectWidth(){let t="";return"OWN"!==this.getOption("WIDTH_BUTTON")||(t=h.compile(this.getOption("styleWidth"),{id:this.factory.getOption("containerId"),width:this.getOption("WIDTH_VALUE_BUTTON")||"282"})),t}width(){let t=this.getOption("WIDTH_BUTTON")||"MAX";return"OWN"===t&&(t="MAX"),t}}p.defaults={style:"<style>#STYLE#</style>",styleHeight:"##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-loading {height:#HEIGHT#px;}",styleBorder:"##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-loading {border-radius:#BORDER#px;}",styleWidth:"##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-loading, ##ID# .bx-yapay-divider{width: #WIDTH#px;}"};class m extends d{style(){const t=this.collectBorder(),e=this.collectWidth();return h.compile(this.getOption("style"),{style:t+e})}mount(t,e,n){e.mountWidget(t,{widgetType:this.getOption("TYPE_WIDGET")||YaPay.WidgetType.Compact,widgetTheme:this.getOption("THEME_WIDGET")||YaPay.WidgetTheme.Dark,buttonTheme:this.getOption("BUTTON_THEME_WIDGET")||YaPay.ButtonTheme.Black,borderRadius:this.getOption("BORDER_RADIUS_VALUE_WIDGET")||"8",bnplSelected:!!Number(this.getOption("SPLIT_SELECT_WIDGET")||!1)})}unmount(t,e){e.unmountWidget(t)}collectBorder(){var t;let e="";return null==(this.getOption("BORDER_RADIUS_TYPE_WIDGET")||null)||(e=h.compile(this.getOption("styleBorder"),{id:this.factory.getOption("containerId"),border:null!=(t=this.getOption("BORDER_RADIUS_VALUE_WIDGET"))?t:"8"})),e}collectWidth(){let t="";return null==(this.getOption("WIDTH_TYPE_WIDGET")||null)||(t=h.compile(this.getOption("styleWidth"),{id:this.factory.getOption("containerId"),width:this.getOption("WIDTH_VALUE_WIDGET")||"282"})),t}width(){return"MAX"}}m.defaults={style:"<style>#STYLE#</style>",styleBorder:"##ID# .bx-yapay-skeleton-loading {border-radius:#BORDER#px !important;}",styleWidth:"##ID# .ya-pay-widget, ##ID# .bx-yapay-skeleton-loading, ##ID# .bx-yapay-divider{width: #WIDTH#px !important;}"};class y{static make(t,e,n){if("Button"===t)return new p(e,n);if("Widget"===t)return new m(e,n);throw new Error("unknown display "+t)}}class g{constructor(t={}){this.waitCount=0,this.defaults=Object.assign({},this.constructor.defaults),this.options={},this.setOptions(t),this.bootSolution(),this.bootLocal()}inject(t,e){let n;return Promise.resolve().then((()=>{n=this.filterMedia(t)})).then((()=>this.waitElement(n))).then((t=>this.checkElement(t))).then((t=>this.renderElement(t,e))).then((t=>this.install(t))).then((t=>this.insertLoader(t))).then((t=>{const i=new u(t.el);return this.getOption("preserve")&&this.preserve(n,e,t,i),i.wait().then((()=>t))})).then((t=>c.getInstance().load().then((()=>t))))}filterMedia(t){const e=[];for(const n of t.split(",")){const t=n.trim();if(""===t)continue;const[i,s]=this.testSelectorMedia(t);s&&e.push(i)}if(0===e.length)throw new Error("widget not matched any media of "+t);return e.join(",")}checkElement(t){const e=this.containerSelector();if(!!t.querySelector(e)||this.containsSiblingElement(t,e))throw new Error("the element already has a container");return t}containerSelector(){return"#"+this.getOption("containerId")+" "+this.getOption("containerSelector")}containsSiblingElement(t,e){var n;let i=!1,s=null==(n=t.parentElement)?void 0:n.firstElementChild;for(;s;){if(s.matches(e)||s.querySelector(e)){i=!0;break}s=s.nextElementSibling}return i}preserve(t,e,n,i){const s=new l(n.el,Object.assign({},this.preserveOptions(),{restore:()=>{s.destroy(),this.restore(t,e,n,i)}}))}preserveOptions(){const t=this.getOption("preserve");return"object"==typeof t?t:{}}restore(t,e,n,i){return Promise.resolve().then((()=>this.waitElement(t))).then((s=>{const o=this.renderElement(s,e);return n.restore(o),null==i||i.restore(o),this.getOption("preserve")&&this.preserve(t,e,n,i),n}))}install(t){return new BX.YandexPay.Widget(t)}insertLoader(t){return t.bootLoader(),t}waitElement(t){return new Promise(((e,n)=>{this.waitCount=0,this.waitElementLoop(t,e,n)}))}waitElementLoop(t,e,n){const i=this.findElement(t);i?e(i):(++this.waitCount,this.waitCount>=this.getOption("waitLimit")?n("cant find element by selector "+t):setTimeout(this.waitElementLoop.bind(this,t,e,n),this.getOption("waitTimeout")))}findElement(t){var e,n;let i,s,o=t.trim();if(""===o)throw new Error("widget selector is empty");return i=null!=(e=null!=(n=this.searchBySelector(o))?n:this.searchById(t))?e:this.searchByClassName(t),null==i?null:(i.length>1&&(s=this.reduceVisible(i)),null==s&&(s=i[0]),s)}searchBySelector(t){try{const e=[];for(const n of t.split(",")){const t=n.trim();if(""===t||!this.isCssSelector(t))continue;const i=document.querySelectorAll(t);for(const t of i)e.push(t)}return e.length>0?e:null}catch(t){return null}}searchById(t){try{const e=document.getElementById(t);return null!=e?[e]:null}catch(t){return null}}searchByClassName(t){try{const e=document.getElementsByClassName(t);return e.length>0?e:null}catch(t){return null}}reduceVisible(t){let e=null;for(const n of t)if(this.testVisible(n)){e=n;break}return e}testVisible(t){return t.offsetWidth||t.offsetHeight||t.getClientRects().length}isCssSelector(t){return/^[.#]/.test(t)}testSelectorMedia(t){const e=/^(.*):media(\(.*\))$/.exec(t);return null==e?[t,!0]:[e[1],window.matchMedia(e[2]).matches]}renderElement(t,e){var n,i;const s=this.containerSelector(),o=this.getDivider(),r=this.getDisplay(),a=h.compile(this.getOption("template"),{divider:o,style:null!=r?r.style():"",width:null!=r?r.width().toLowerCase():"auto",id:this.getOption("containerId"),mode:this.getOption("mode")||"payment",display:(null==(n=this.getOption("displayType"))?void 0:n.toLowerCase())||"button",solution:null==(i=this.getOption("solution"))?void 0:i.toLowerCase()});let l=h.toElements(a),c=null;0===e.indexOf("after")&&(l=l.reverse());for(const n of l)t.insertAdjacentElement(e,n),null==c&&(c=n.matches(s)?n:n.querySelector(s));if(null==c)throw new Error(`cant find template container by selector ${s}`);return c}bootSolution(){const t=this.getOption("solution"),n=this.getOption("mode"),i=e.getPage(t,n);null!=i&&i.bootFactory(this)}getDisplay(){const t=this.getOption("displayType"),e=this.getOption("displayParameters");return null==t?null:y.make(t,this,e)}getDivider(){return this.getOption("useDivider")?h.compile(this.getOption("divider"),{label:this.getOption("label")}):""}bootLocal(){o.make().fire("bxYapayFactoryInit",{factory:this})}extendDefaults(t){this.defaults=Object.assign(this.defaults,t)}setOptions(t){this.options=Object.assign(this.options,t)}getOption(t){var e;return null!=(e=this.options[t])?e:this.defaults[t]}}g.defaults={solution:null,template:'<div id="#ID#" class="bx-yapay-drawer-container yapay-behavior--#MODE# yapay-display--#DISPLAY# yapay-width--#WIDTH# yapay-solution--#SOLUTION#">#STYLE##DIVIDER#<div class="bx-yapay-drawer"></div></div>',divider:'<div class="bx-yapay-divider"> <span class="bx-yapay-divider__corner"></span> <span class="bx-yapay-divider__text">#LABEL#</span> <span class="bx-yapay-divider__corner at--right"></span> </div>',useDivider:!1,containerSelector:".bx-yapay-drawer",loaderSelector:".bx-yapay-skeleton-loading",preserve:{composite:!0},waitLimit:30,waitTimeout:1e3};class f{constructor(t,e={}){this.delayTimeouts={},this.widget=t,this.defaults=Object.assign({},this.constructor.defaults),this.options=Object.assign({},e)}getOption(t){var e,n;return null!=(e=null!=(n=this.options[t])?n:this.widget.getOption(t))?e:this.defaults[t]}render(t,e={}){t.innerHTML=this.compile(e)}compile(t){return h.compile(this.getOption("template"),t)}restore(t){}query(t,e){return fetch(t,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)}).then((t=>t.json()))}isRest(){return this.getOption("isRest")}getElement(t,e,n){let i=this.getElementSelector(t);return this.getNode(i,e,n||"querySelector")}getElementSelector(t){let e=t+"Element";return this.options[e]}getNode(t,e,n){return"#"===t.substr(0,1)?e=document:e||(e=this.el),e[n](t)}clearDelay(t){null!=this.delayTimeouts[t]&&(clearTimeout(this.delayTimeouts[t]),this.delayTimeouts[t]=null)}delay(t,e=[],n=300){this.clearDelay(t),this.delayTimeouts[t]=setTimeout(this[t].bind(this,...e),n)}showError(t,e,n=null){let i=t+" - "+e;n&&(i+=" "+n),alert(i)}}f.defaults={template:""};class b{constructor(t={}){this.options=Object.assign({},this.constructor.defaults,t),this.widget=null}render(t,e={}){t.innerHTML=this.compile(e)}compile(t){return h.compile(this.options.template,t)}setWidget(t){this.widget=t}getOption(t){return t in this.options?this.options[t]:this.widget.options[t]}}b.defaults={template:null};class O extends b{render(t,e){super.render(t,e),this.autosubmit(t)}compile(t){const e=this.options.template,n=Object.assign(t,{inputs:this.makeInputs(t)});return h.compile(e,n)}makeInputs(t){let e,n,i,s=t.params;if(0===Object.keys(s).length)return"";for(e in i=t.termUrl?'<input type="hidden" name="TermUrl" value="'+s.termUrl+'">':"",s)s.hasOwnProperty(e)&&(n=s[e],i+='<input type="hidden" name="'+e+'" value="'+n+'">');return i}makeTermUrl(){let t=this.getOption("notifyUrl"),e=window.location.href;return t+=(-1===t.indexOf("?")?"?":"&")+"backurl="+encodeURIComponent(e)+"&service="+this.getOption("requestSign")+"&paymentId="+this.getOption("externalId"),t}autosubmit(t){t.querySelector("form").submit()}}O.defaults={template:'<form name="form" action="#ACTION#" method="#METHOD#">#INPUTS#</form>'};class w extends b{render(t,e){this.insertIframe(t,e)}insertIframe(t,e){t.innerHTML=h.compile(this.options.template,e),this.compile(t,e)}compile(t,e){let n=t.querySelector("iframe"),i=n.contentWindow||n.contentDocument.document||n.contentDocument,s=e.params;i.document.open(),i.document.write(s),i.document.close()}}w.defaults={template:'<iframe style="display: none;"></iframe>'};class v extends b{render(t,e){this.insertIframe(t,e)}insertIframe(t,e){t.innerHTML=h.compile(this.options.template,e),this.compile(t,e)}compile(t,e){Promise.resolve().then((()=>this.renderIframe(t,e))).then((()=>this.query(e)))}query(t){fetch(this.getOption("notifyUrl"),{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({service:this.getOption("requestSign"),accept:"json",secure:t.params.secure,externalId:t.params.notify.externalId,paySystemId:t.params.notify.paySystemId})}).then((t=>t.json())).then((t=>{!0===t.success?this.widget.go(t.state,t):this.widget.go("error",t)})).catch((t=>console.log(t)))}renderIframe(t,e){let n=t.querySelector("iframe"),i=n.contentWindow||n.contentDocument.document||n.contentDocument,s=`<form name="form" action="${e.action}" method="POST">`;i.document.open(),i.document.write(s),i.document.close(),i.document.querySelector("form").submit()}}v.defaults={template:'<iframe style="display: none;"></iframe>'};class P extends f{render(t,e){const n=this.makeView(e);n.setWidget(this.widget),n.render(t,e)}makeView(t){let e=t.view;if("form"===e)return new O;if("iframe"===e)return new w;if("iframerbs"===e)return new v;throw new Error("view secure3d missing")}}class E extends f{}E.defaults={template:'<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'};class T extends f{}T.defaults={template:'<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'};class k extends f{render(t,e={}){const n=this.compile(e),i=h.toElement(n);t.insertAdjacentElement("beforeend",i),this.restoreElement=i}restore(t){null!=this.restoreElement&&t.insertAdjacentElement("beforeend",this.restoreElement)}remove(t){const e=t.querySelector(this.getOption("loaderSelector"));null==e||e.remove()}}k.defaults={template:'<div class="bx-yapay-skeleton-loading"></div>',loaderSelector:".bx-yapay-skeleton-loading"};class I{constructor(t){this.cart=t,this.widget=t.widget,this.paymentData=this.getPaymentData()}getOption(t){return this.cart.getOption(t)}bootstrap(){}createPayment(t,e){}getPaymentData(){}restore(t){}mount(t,e){}}class C extends I{bootstrap(){this.getButtonData().then((t=>{if("fail"===t.status)throw new Error(t.reason);this.combineOrderWithData(t.data),this.createPayment(this.cart.element,this.paymentData)})).catch((t=>{this.widget.removeLoader()}))}getButtonData(){let t={productId:this.getOption("productId"),mode:this.getOption("mode"),currencyCode:this.getOption("currencyCode"),setupId:this.getOption("setupId")};return this.cart.query(this.getOption("restUrl")+"button/data",t)}getPaymentData(){return{env:this.getOption("env"),version:3,merchantId:this.getOption("merchantId"),cart:{externalId:"checkout-b2b-test-order-id"},currencyCode:this.getOption("currencyCode")}}onPaymentSuccess(t){this.authorize(t).then((t=>{"success"===t.status?setTimeout((function(){window.location.href=t.data.redirect}),1e3):this.cart.showError("authorize",t.reasonCode,t.reason)})),o.make().fire("bxYapayPaymentSuccess",t)}onPaymentAbort(t){o.make().fire("bxYapayPaymentAbort",t)}onPaymentError(t){o.make().fire("bxYapayPaymentError",t),this.cart.showError("yapayPayment","payment not created",t.reason)}createPayment(t,e){null==this._mounted&&(this._mounted=!1,YaPay.createSession(e,{onSuccess:this.onPaymentSuccess.bind(this),onAbort:this.onPaymentAbort.bind(this),onError:this.onPaymentError.bind(this),agent:{name:"CMS-Bitrix",version:"1.0"}}).then((t=>{this._mounted=!0,this.widget.removeLoader(),this.mount(t)})).catch((e=>{this._mounted=null,t.remove(),this.cart.showError("yapayPayment","payment not created",e)})))}authorize(t){let e={orderId:t.orderId,hash:t.metadata,successUrl:this.getOption("successUrl")};return this.cart.query(this.getOption("restUrl")+"authorize",e)}bindDebug(t){for(const e in YaPay.CheckoutEventType)YaPay.CheckoutEventType.hasOwnProperty(e)&&t.on(YaPay.CheckoutEventType[e],(function(){console.log(arguments)}))}mount(t){this.cart.initialContent=null,this.payment=t,this.cart.display.mount(this.cart.element,t,YaPay.ButtonType.Checkout),o.make().fire("bxYapayMountButton")}restore(t){null!=this.payment&&(this.cart.display.mount(t,this.payment,YaPay.ButtonType.Checkout),o.make().fire("bxYapayRestoreButton"))}combineOrderWithData(t){const{cart:e}=this.paymentData;let n={cart:{...e,items:t.items,total:{amount:t.total.amount}},metadata:t.metadata};Object.assign(this.paymentData,n)}changeOffer(t){this.getOption("productId")!==t&&(this.widget.setOptions({productId:t}),null==this._mounted?this.bootstrap():this.update())}changeBasket(){this.update()}update(){this.payment.update((async()=>{const t=await this.getButtonData().then((t=>t)).catch((t=>{this.cart.showError("getButtonData","get not button data",t)}));if("fail"===t.status)throw this._mounted=null,this.cart.display.unmount(this.cart.element,this.payment),new Error(t.reason);return{cart:{items:t.data.items},total:{amount:t.data.total.amount},metadata:t.data.metadata}}))}}class B extends I{bootstrap(){this.reflow()}getPaymentData(){let t={env:this.getOption("env"),version:2,countryCode:YaPay.CountryCode.Ru,currencyCode:YaPay.CurrencyCode.Rub,merchant:{id:this.getOption("merchantId"),name:this.getOption("merchantName"),url:this.getOption("siteUrl")},order:{id:"0"},paymentMethods:[{type:YaPay.PaymentMethodType.Card,gateway:this.getOption("gateway"),gatewayMerchantId:this.getOption("gatewayMerchantId"),allowedAuthMethods:[YaPay.AllowedAuthMethod.PanOnly],allowedCardNetworks:[YaPay.AllowedCardNetwork.UnionPay,YaPay.AllowedCardNetwork.Uzcard,YaPay.AllowedCardNetwork.Discover,YaPay.AllowedCardNetwork.AmericanExpress,YaPay.AllowedCardNetwork.Visa,YaPay.AllowedCardNetwork.Mastercard,YaPay.AllowedCardNetwork.Mir,YaPay.AllowedCardNetwork.Maestro,YaPay.AllowedCardNetwork.VisaElectron]}],requiredFields:{billingContact:{email:this.getOption("useEmail")||!1},shippingContact:{name:this.getOption("useName")||!1,email:this.getOption("useEmail")||!1,phone:this.getOption("usePhone")||!1},shippingTypes:{direct:!0,pickup:!0}}};return null!=this.getOption("paymentCash")&&t.paymentMethods.push({type:YaPay.PaymentMethodType.Cash}),t}getProducts(){let t={yapayAction:"getProducts",productId:this.getOption("productId"),mode:this.getOption("mode"),setupId:this.getOption("setupId")};return this.cart.query(this.getOption("purchaseUrl"),t)}getShippingOptions(t){let e={address:t,yapayAction:"deliveryOptions",items:this.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}getPickupOptions(t){let e={bounds:t,yapayAction:"pickupOptions",items:this.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}createPayment(t,e){null==this._mounted&&(this._mounted=!1,YaPay.createPayment(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{this._mounted=!0,this.widget.removeLoader(),this.mountButton(t,e),e.on(YaPay.PaymentEventType.Process,(t=>{this.orderAccept(t).then((n=>{if(n.error)throw new Error(n.error.message,n.error.code);this.isPaymentTypeCash(t)?(e.complete(YaPay.CompleteReason.Success),null!=n.redirect&&(window.location.href=n.redirect)):this.notify(n,t).then((t=>{!0===t.success?(this.widget.go(t.state,t),e.complete(YaPay.CompleteReason.Success)):(this.widget.go("error",t),e.complete(YaPay.CompleteReason.Error))}))})).catch((t=>{this.cart.showError("yapayProcess","",t),e.complete(YaPay.CompleteReason.Error)}))})),e.on(YaPay.PaymentEventType.Error,(t=>{this.cart.showError("yapayError","service temporary unavailable"),e.complete(YaPay.CompleteReason.Error)})),e.on(YaPay.PaymentEventType.Change,(t=>{t.shippingAddress&&this.getShippingOptions(t.shippingAddress).then((t=>{e.update({shippingOptions:t})})),t.shippingOption&&e.update({order:this.combineOrderWithDirectShipping(t.shippingOption)}),t.pickupBounds&&this.getPickupOptions(t.pickupBounds).then((t=>{e.update({pickupPoints:t})})),t.pickupInfo&&this.getPickupDetail(t.pickupInfo.pickupPointId).then((t=>{e.update({pickupPoint:t})})),t.pickupPoint&&e.update({order:this.combineOrderWithPickupShipping(t.pickupPoint)})}))})).catch((t=>{this._mounted=null,this.cart.showError("yapayPayment","payment not created",t)})))}mountButton(t,e){this.cart.initialContent=null;const n=this.cart.display.getOption("VARIANT_BUTTON")||YaPay.ButtonTheme.Black,i=this.cart.display.getOption("WIDTH_BUTTON")||YaPay.ButtonWidth.Auto;this.paymentButton=e.createButton({type:YaPay.ButtonType.Checkout,theme:n,width:i}),this.paymentButton.mount(this.cart.element),this.paymentButton.on(YaPay.ButtonEventType.Click,(()=>{e.checkout()}))}getPickupDetail(t){let e={pickupId:t,yapayAction:"pickupDetail",items:this.paymentData.order.items,setupId:this.getOption("setupId"),paySystemId:this.getOption("paySystemId")};return this.cart.query(this.getOption("purchaseUrl"),e)}orderAccept(t){let e,n=t.shippingMethodInfo.shippingOption?"delivery":"pickup";e="pickup"===n?{address:t.shippingMethodInfo.pickupPoint.address,pickup:t.shippingMethodInfo.pickupPoint}:{address:t.shippingMethodInfo.shippingAddress,delivery:t.shippingMethodInfo.shippingOption};let i={...{setupId:this.getOption("setupId"),items:this.paymentData.order.items,payment:t.paymentMethodInfo,contact:t.shippingContact,yapayAction:"orderAccept",deliveryType:n,paySystemId:this.isPaymentTypeCash(t)?this.getOption("paymentCash"):this.getOption("paySystemId"),orderAmount:t.orderAmount},...e};return this.cart.query(this.getOption("purchaseUrl"),i)}isPaymentTypeCash(t){return"CASH"===t.paymentMethodInfo.type}notify(t,e){let n={service:this.getOption("requestSign"),accept:"json",yandexData:e,externalId:t.externalId,paySystemId:t.paySystemId};return this.cart.query(this.getOption("notifyUrl"),n)}changeOffer(t){this.getOption("productId")!==t&&(this.widget.setOptions({productId:t}),this.reflow())}changeBasket(){this.reflow()}reflow(){this.getProducts().then((t=>{if(t.error)throw new Error(t.error.message);this.combineOrderWithProducts(t),this.createPayment(this.cart.element,this.paymentData)})).catch((t=>{this.widget.removeLoader()}))}combineOrderWithPickupShipping(t){const{order:e}=this.paymentData;return{...e,items:[...e.items,{type:"SHIPPING",label:t.label,amount:t.amount}],total:{...e.total,amount:this.amountSum(e.total.amount,t.amount)}}}combineOrderWithDirectShipping(t){const{order:e}=this.paymentData;return{...e,items:[...e.items,{type:"SHIPPING",label:t.label,amount:t.amount}],total:{...e.total,amount:this.amountSum(e.total.amount,t.amount)}}}combineOrderWithProducts(t){const{order:e}=this.paymentData;let n={...e,items:t.items,total:{amount:t.amount}};Object.assign(this.paymentData.order,n)}restore(t){null!=this.paymentButton&&this.paymentButton.mount(t)}amountSum(t,e){return(Number(t)+Number(e)).toFixed(2)}}class S extends f{constructor(...t){super(...t),this.isBootstrap=!1}render(t,e){this.element=t,this.display=this.getDisplay(),this.initialContent=this.element.innerHTML,this.bootProxy(),this.bootSolution(),this.bootLocal(),this.delayBootstrap()}bootProxy(){this.proxy=this.isRest()?new C(this):new B(this)}restore(t){null!=this.initialContent&&(t.innerHTML=this.initialContent),this.element=t,this.proxy.restore(t)}bootstrap(){this.isBootstrap=!0,this.proxy.bootstrap()}bootSolution(){const t=this.widget.getSolution();null!=t&&t.bootCart(this)}bootLocal(){o.make().fire("bxYapayCartInit",{cart:this})}delayChangeBasket(){this.delay("changeBasket")}delayChangeOffer(t){this.delay("changeOffer",[t])}delayBootstrap(){var t;t=()=>{this.delay("bootstrap")},"complete"===document.readyState||"interactive"===document.readyState?setTimeout(t,1):document.addEventListener("DOMContentLoaded",t)}changeBasket(){var t;this.isBootstrap&&(null==(t=this.proxy)||t.changeBasket())}changeOffer(t){var e;this.isBootstrap?null==(e=this.proxy)||e.changeOffer(t):this.widget.setOptions({productId:t})}getDisplay(){const t=this.getOption("displayType"),e=this.getOption("displayParameters");return y.make(t,this,e)}}class x{constructor(t){this.payment=t,this.widget=t.widget}getOption(t){return this.payment.getOption(t)}createPayment(t,e){}getPaymentData(t){}}class D extends x{getPaymentData(t){return{env:this.getOption("env"),version:3,currencyCode:YaPay.CurrencyCode.Rub,merchantId:this.getOption("merchantId"),orderId:t.id,cart:{items:t.items,total:{amount:t.total}},metadata:this.getOption("metadata")}}onPaymentSuccess(t){this.payment.element.remove(),this.authorize(t).then((t=>{"success"===t.status?setTimeout((function(){window.location.href=t.data.redirect}),1e3):this.payment.showError("authorize",t.reasonCode,t.reason)}))}onPaymentAbort(t){}onPaymentError(t){setTimeout((()=>{window.location.href=this.getOption("failUrl")}),1e3)}createPayment(t,e){YaPay.createSession(e,{onSuccess:this.onPaymentSuccess.bind(this),onAbort:this.onPaymentAbort.bind(this),onError:this.onPaymentError.bind(this),agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{this.widget.removeLoader(),e.mountButton(t,{type:YaPay.ButtonType.Pay,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto})})).catch((t=>{this.payment.showError("yapayPayment","payment not created",t)}))}authorize(t){let e={orderId:t.orderId,hash:t.metadata,successUrl:this.getOption("successUrl")};return this.payment.query(this.getOption("restUrl")+"authorize",e)}}class A extends x{getPaymentData(t){return{env:this.getOption("env"),version:2,countryCode:YaPay.CountryCode.Ru,currencyCode:YaPay.CurrencyCode.Rub,merchant:{id:this.getOption("merchantId"),name:this.getOption("merchantName")},order:{id:t.id,total:{amount:t.total},items:t.items},paymentMethods:[{type:YaPay.PaymentMethodType.Card,gateway:this.getOption("gateway"),gatewayMerchantId:this.getOption("gatewayMerchantId"),allowedAuthMethods:[YaPay.AllowedAuthMethod.PanOnly],allowedCardNetworks:[YaPay.AllowedCardNetwork.UnionPay,YaPay.AllowedCardNetwork.Uzcard,YaPay.AllowedCardNetwork.Discover,YaPay.AllowedCardNetwork.AmericanExpress,YaPay.AllowedCardNetwork.Visa,YaPay.AllowedCardNetwork.Mastercard,YaPay.AllowedCardNetwork.Mir,YaPay.AllowedCardNetwork.Maestro,YaPay.AllowedCardNetwork.VisaElectron]}]}}createPayment(t,e){YaPay.createPayment(e,{agent:{name:"CMS-Bitrix",version:"1.0"}}).then((e=>{let n=e.createButton({type:YaPay.ButtonType.Pay,theme:this.getOption("buttonTheme")||YaPay.ButtonTheme.Black,width:this.getOption("buttonWidth")||YaPay.ButtonWidth.Auto});this.widget.removeLoader(),n.mount(t),n.on(YaPay.ButtonEventType.Click,(function(){e.checkout()})),e.on(YaPay.PaymentEventType.Process,(t=>{this.notify(e,t).then((t=>{})),e.complete(YaPay.CompleteReason.Success)})),e.on(YaPay.PaymentEventType.Error,(function(t){console.log({errors:t}),e.complete(YaPay.CompleteReason.Error)})),e.on(YaPay.PaymentEventType.Abort,(function(t){}))})).catch((function(t){console.log({"payment not create":t})}))}notify(t,e){return fetch(this.getOption("notifyUrl"),{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({service:this.getOption("requestSign"),accept:"json",yandexData:e,externalId:this.getOption("externalId"),paySystemId:this.getOption("paySystemId")})}).then((t=>t.json())).then((t=>{!0===t.success?this.widget.go(t.state,t):this.widget.go("error",t)})).catch((t=>console.log(t)))}}class Y extends f{render(t,e){this.element=t,this.bootProxy();const n=this.getPaymentData(e);this.createPayment(t,n)}bootProxy(){this.proxy=this.isRest()?new D(this):new A(this)}getPaymentData(t){return this.proxy.getPaymentData(t)}createPayment(t,e){this.proxy.createPayment(t,e)}}class M{constructor(t,e={}){this.defaults=Object.assign({},this.constructor.defaults),this.options={},this.el=t,this.setOptions(e),this.bootSolution()}payment(t){this.go("payment",t)}cart(t){this.go("cart",t)}restore(t){var e;this.el=t,null==(e=this.step)||e.restore(t)}go(t,e){this.step=this.makeStep(t),this.step.render(this.el,e)}bootLoader(){this.loader=new k(this),this.loader.render(this.el)}removeLoader(){null!=this.loader&&this.loader.remove(this.el)}makeStep(t){const e=this.getOption(t)||{};return class{static make(t,e,n={}){if("3ds"===t)return new P(e,n);if("finished"===t)return new E(e,n);if("error"===t)return new T(e,n);if("payment"===t)return new Y(e,n);if("cart"===t)return new S(e,n);if("loader"===t)return new k(e,n);throw new Error("unknown step "+t)}}.make(t,this,e)}getSolution(){const t=this.getOption("solution"),n=this.getOption("mode");return e.getPage(t,n)}bootSolution(){const t=this.getSolution();null!=t&&t.bootWidget(this)}extendDefaults(t){this.defaults=Object.assign(this.defaults,t)}setOptions(t){this.options=Object.assign(this.options,t)}getOption(t){var e;return null!=(e=this.options[t])?e:this.defaults[t]}}M.defaults={},t.Factory=g,t.Widget=M}(this.BX.YandexPay=this.BX.YandexPay||{});
//# sourceMappingURL=widget.js.map
