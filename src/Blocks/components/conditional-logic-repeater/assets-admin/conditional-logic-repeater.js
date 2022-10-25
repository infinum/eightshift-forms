export class ConditionalTags {
	constructor(options) {
		this.fieldSelector = options.fieldSelector
		this.idPrefix = options.idPrefix

		this.data = {};
	}

	// getData = () => {
	// 	return this.data;
	// }

	// setData = (name, data) => {
	// 	console.log(name, data, this.data);
		
	// 	return 
	// 		...this.data,
	// 		[name] => data,
	// 	]
	// }

	init = () => {
		const elements = document.querySelectorAll(this.fieldSelector);

		// Loop all forms on the page.
		[...elements].forEach((element) => {

			const id = element.getAttribute('data-id');
			const input = document.querySelector(`#${id}`)

			element.addEventListener('es-conditional-logic-repeater-update', (event) => {
				const {
					behavior,
					logic,
					conditions
				} = event.detail;

				const data = [
					behavior,
					logic,
					conditions.map((item) => {
						return [
							item.field,
							item.comparison,
							item.value,
						]
					})
				];

				input.value = JSON.stringify(data);
			});

			// if (element.enabled) {
			// 	console.log({
			// 		enabled: element.enabled,
			// 		behavior: element.behavior,
			// 		logic: element.logic,
			// 		conditions: element.conditions,
			// 	});
			// } else {
			// 	console.log('Repeater is disabled');
			// }
		});
	}
}

export function conditionalLogicRepeaterComponent() {
	(function(){const e=document.createElement("link").relList;if(e&&e.supports&&e.supports("modulepreload"))return;for(const r of document.querySelectorAll('link[rel="modulepreload"]'))i(r);new MutationObserver(r=>{for(const l of r)if(l.type==="childList")for(const o of l.addedNodes)o.tagName==="LINK"&&o.rel==="modulepreload"&&i(o)}).observe(document,{childList:!0,subtree:!0});function n(r){const l={};return r.integrity&&(l.integrity=r.integrity),r.referrerpolicy&&(l.referrerPolicy=r.referrerpolicy),r.crossorigin==="use-credentials"?l.credentials="include":r.crossorigin==="anonymous"?l.credentials="omit":l.credentials="same-origin",l}function i(r){if(r.ep)return;r.ep=!0;const l=n(r);fetch(r.href,l)}})();function I(){}function X(t){return t()}function $(){return Object.create(null)}function P(t){t.forEach(X)}function ae(t){return typeof t=="function"}function pe(t,e){return t!=t?e==e:t!==e||t&&typeof t=="object"||typeof t=="function"}function ge(t){return Object.keys(t).length===0}function _(t,e){t.appendChild(e)}function O(t,e,n){t.insertBefore(e,n||null)}function T(t){t.parentNode.removeChild(t)}function m(t){return document.createElement(t)}function V(t){return document.createTextNode(t)}function R(){return V(" ")}function ue(){return V("")}function S(t,e,n,i){return t.addEventListener(e,n,i),()=>t.removeEventListener(e,n,i)}function E(t,e,n){n==null?t.removeAttribute(e):t.getAttribute(e)!==n&&t.setAttribute(e,n)}function me(t){return Array.from(t.childNodes)}function ee(t,e){t.value=e==null?"":e}function A(t,e){for(let n=0;n<t.options.length;n+=1){const i=t.options[n];if(i.__value===e){i.selected=!0;return}}t.selectedIndex=-1}function W(t){const e=t.querySelector(":checked")||t.options[0];return e&&e.__value}function be(t){const e={};for(const n of t)e[n.name]=n.value;return e}let Y;function U(t){Y=t}const F=[],G=[],K=[],te=[],ve=Promise.resolve();let Q=!1;function ke(){Q||(Q=!0,ve.then(j))}function B(t){K.push(t)}const D=new Set;let J=0;function j(){const t=Y;do{for(;J<F.length;){const e=F[J];J++,U(e),ye(e.$$)}for(U(null),F.length=0,J=0;G.length;)G.pop()();for(let e=0;e<K.length;e+=1){const n=K[e];D.has(n)||(D.add(n),n())}K.length=0}while(F.length);for(;te.length;)te.pop()();Q=!1,D.clear(),U(t)}function ye(t){if(t.fragment!==null){t.update(),P(t.before_update);const e=t.dirty;t.dirty=[-1],t.fragment&&t.fragment.p(t.ctx,e),t.after_update.forEach(B)}}const we=new Set;function fe(t,e){t&&t.i&&(we.delete(t),t.i(e))}function de(t,e){t.d(1),e.delete(t.key)}function he(t,e,n,i,r,l,o,c,s,f,w,d){let g=t.length,v=l.length,h=g;const H={};for(;h--;)H[t[h].key]=h;const M=[],N=new Map,u=new Map;for(h=v;h--;){const b=d(r,l,h),a=n(b);let C=o.get(a);C?i&&C.p(b,e):(C=f(a,b),C.c()),N.set(a,M[h]=C),a in H&&u.set(a,Math.abs(h-H[a]))}const p=new Set,L=new Set;function y(b){fe(b,1),b.m(c,w),o.set(b.key,b),w=b.first,v--}for(;g&&v;){const b=M[v-1],a=t[g-1],C=b.key,k=a.key;b===a?(w=b.first,g--,v--):N.has(k)?!o.has(C)||p.has(C)?y(b):L.has(k)?g--:u.get(C)>u.get(k)?(L.add(C),y(b)):(p.add(k),g--):(s(a,o),g--)}for(;g--;){const b=t[g];N.has(b.key)||s(b,o)}for(;v;)y(M[v-1]);return M}function Ce(t,e,n,i){const{fragment:r,on_mount:l,on_destroy:o,after_update:c}=t.$$;r&&r.m(e,n),i||B(()=>{const s=l.map(X).filter(ae);o?o.push(...s):P(s),t.$$.on_mount=[]}),c.forEach(B)}function Ee(t,e){const n=t.$$;n.fragment!==null&&(P(n.on_destroy),n.fragment&&n.fragment.d(e),n.on_destroy=n.fragment=null,n.ctx=[])}function Me(t,e){t.$$.dirty[0]===-1&&(F.push(t),ke(),t.$$.dirty.fill(0)),t.$$.dirty[e/31|0]|=1<<e%31}function Le(t,e,n,i,r,l,o,c=[-1]){const s=Y;U(t);const f=t.$$={fragment:null,ctx:null,props:l,update:I,not_equal:r,bound:$(),on_mount:[],on_destroy:[],on_disconnect:[],before_update:[],after_update:[],context:new Map(e.context||(s?s.$$.context:[])),callbacks:$(),dirty:c,skip_bound:!1,root:e.target||s.$$.root};o&&o(f.root);let w=!1;if(f.ctx=n?n(t,e.props||{},(d,g,...v)=>{const h=v.length?v[0]:g;return f.ctx&&r(f.ctx[d],f.ctx[d]=h)&&(!f.skip_bound&&f.bound[d]&&f.bound[d](h),w&&Me(t,d)),g}):[],f.update(),w=!0,P(f.before_update),f.fragment=i?i(f.ctx):!1,e.target){if(e.hydrate){const d=me(e.target);f.fragment&&f.fragment.l(d),d.forEach(T)}else f.fragment&&f.fragment.c();e.intro&&fe(t.$$.fragment),Ce(t,e.target,e.anchor,e.customElement),j()}U(s)}let _e;typeof HTMLElement=="function"&&(_e=class extends HTMLElement{constructor(){super(),this.attachShadow({mode:"open"})}connectedCallback(){const{on_mount:t}=this.$$;this.$$.on_disconnect=t.map(X).filter(ae);for(const e in this.$$.slotted)this.appendChild(this.$$.slotted[e])}attributeChangedCallback(t,e,n){this[t]=n}disconnectedCallback(){P(this.$$.on_disconnect)}$destroy(){Ee(this,1),this.$destroy=I}$on(t,e){const n=this.$$.callbacks[t]||(this.$$.callbacks[t]=[]);return n.push(e),()=>{const i=n.indexOf(e);i!==-1&&n.splice(i,1)}}$set(t){this.$$set&&!ge(t)&&(this.$$.skip_bound=!0,this.$$set(t),this.$$.skip_bound=!1)}});function ne(t,e,n){const i=t.slice();return i[20]=e[n],i[21]=e,i[22]=n,i}function oe(t,e,n){const i=t.slice();return i[23]=e[n],i[25]=n,i}function Se(t){let e,n,i,r,l,o;return{c(){e=m("label"),n=m("input"),r=V(`
		Use conditional logic`),E(n,"type","checkbox"),E(n,"part",i="use-toggle-checkbox "+(t[0]?"use-toggle-checkbox-enabled":"")),E(e,"part","use-toggle-label")},m(c,s){O(c,e,s),_(e,n),n.checked=t[0],_(e,r),l||(o=S(n,"change",t[12]),l=!0)},p(c,s){s&1&&i!==(i="use-toggle-checkbox "+(c[0]?"use-toggle-checkbox-enabled":""))&&E(n,"part",i),s&1&&(n.checked=c[0])},d(c){c&&T(e),l=!1,o()}}}function ie(t){let e,n,i,r,l,o,c,s,f,w,d=[],g=new Map,v,h,H,M=t[3];const N=u=>u[22];for(let u=0;u<M.length;u+=1){let p=ne(t,M,u),L=N(p);g.set(L,d[u]=se(L,p))}return{c(){e=m("div"),n=m("select"),i=m("option"),i.textContent="Show",r=m("option"),r.textContent="Hide",l=V(`
		this field if
		`),o=m("select"),c=m("option"),c.textContent="all",s=m("option"),s.textContent="any",f=V(`
		of the following match:`),w=R();for(let u=0;u<d.length;u+=1)d[u].c();v=ue(),i.__value="show",i.value=i.__value,r.__value="hide",r.value=r.__value,E(n,"part","header-behavior-select"),t[1]===void 0&&B(()=>t[13].call(n)),c.__value="and",c.value=c.__value,s.__value="or",s.value=s.__value,E(o,"part","header-logic-select"),t[2]===void 0&&B(()=>t[14].call(o)),E(e,"class","conditional-logic-repeater__item"),E(e,"part","header")},m(u,p){O(u,e,p),_(e,n),_(n,i),_(n,r),A(n,t[1]),_(e,l),_(e,o),_(o,c),_(o,s),A(o,t[2]),_(e,f),O(u,w,p);for(let L=0;L<d.length;L+=1)d[L].m(u,p);O(u,v,p),h||(H=[S(n,"change",t[13]),S(n,"change",t[10]),S(o,"change",t[14]),S(o,"change",t[10])],h=!0)},p(u,p){p&2&&A(n,u[1]),p&4&&A(o,u[2]),p&1944&&(M=u[3],d=he(d,p,N,1,u,M,g,v.parentNode,de,se,v,ne))},d(u){u&&T(e),u&&T(w);for(let p=0;p<d.length;p+=1)d[p].d(u);u&&T(v),h=!1,P(H)}}}function le(t){let e=[],n=new Map,i,r=t[7];const l=o=>o[25];for(let o=0;o<r.length;o+=1){let c=oe(t,r,o),s=l(c);n.set(s,e[o]=re(s,c))}return{c(){for(let o=0;o<e.length;o+=1)e[o].c();i=ue()},m(o,c){for(let s=0;s<e.length;s+=1)e[s].m(o,c);O(o,i,c)},p(o,c){c&128&&(r=o[7],e=he(e,c,l,1,o,r,n,i.parentNode,de,re,i,oe))},d(o){for(let c=0;c<e.length;c+=1)e[c].d(o);o&&T(i)}}}function re(t,e){let n,i=e[23].label+"",r;return{key:t,first:null,c(){n=m("option"),r=V(i),n.__value=e[23].value,n.value=n.__value,this.first=n},m(l,o){O(l,n,o),_(n,r)},p(l,o){e=l},d(l){l&&T(n)}}}function ce(t){let e,n,i;function r(){return t[18](t[22])}return{c(){e=m("button"),e.innerHTML='<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>',E(e,"part","remove-condition-button")},m(l,o){O(l,e,o),n||(i=S(e,"click",r),n=!0)},p(l,o){t=l},d(l){l&&T(e),n=!1,i()}}}function se(t,e){var Z;let n,i,r,l,o,c,s,f,w,d,g,v,h,H,M,N,u,p,L,y=((Z=e[4])==null?void 0:Z.length)>0&&le(e);function b(){e[15].call(i,e[21],e[22])}function a(){e[16].call(l,e[21],e[22])}function C(){e[17].call(h,e[21],e[22])}let k=e[22]>0&&ce(e);return{key:t,first:null,c(){n=m("div"),i=m("select"),y&&y.c(),r=R(),l=m("select"),o=m("option"),o.textContent="is",c=m("option"),c.textContent="is not",s=m("option"),s.textContent="greater than",f=m("option"),f.textContent="less than",w=m("option"),w.textContent="contains",d=m("option"),d.textContent="starts with",g=m("option"),g.textContent="ends with",v=R(),h=m("input"),H=R(),M=m("button"),M.innerHTML='<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10H10M19 10H10M10 10V1M10 10V19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>',N=R(),k&&k.c(),u=R(),E(i,"part","item-field-select"),e[20].field===void 0&&B(b),o.__value="is",o.value=o.__value,c.__value="isnot",c.value=c.__value,s.__value="gt",s.value=s.__value,f.__value="lt",f.value=f.__value,w.__value="contains",w.value=w.__value,d.__value="startsWith",d.value=d.__value,g.__value="endsWith",g.value=g.__value,E(l,"part","item-comparison-select"),e[20].comparison===void 0&&B(a),E(h,"type","text"),E(h,"part","item-value-input"),E(M,"part","add-condition-button"),E(n,"class","conditional-logic-repeater__item"),E(n,"part","item"),this.first=n},m(q,z){O(q,n,z),_(n,i),y&&y.m(i,null),A(i,e[20].field),_(n,r),_(n,l),_(l,o),_(l,c),_(l,s),_(l,f),_(l,w),_(l,d),_(l,g),A(l,e[20].comparison),_(n,v),_(n,h),ee(h,e[20].value),_(n,H),_(n,M),_(n,N),k&&k.m(n,null),_(n,u),p||(L=[S(i,"change",b),S(i,"change",e[10]),S(l,"change",a),S(l,"change",e[10]),S(h,"input",C),S(h,"change",e[10]),S(M,"click",e[8])],p=!0)},p(q,z){var x;e=q,((x=e[4])==null?void 0:x.length)>0?y?y.p(e,z):(y=le(e),y.c(),y.m(i,null)):y&&(y.d(1),y=null),z&136&&A(i,e[20].field),z&136&&A(l,e[20].comparison),z&136&&h.value!==e[20].value&&ee(h,e[20].value),e[22]>0?k?k.p(e,z):(k=ce(e),k.c(),k.m(n,u)):k&&(k.d(1),k=null)},d(q){q&&T(n),y&&y.d(),k&&k.d(),p=!1,P(L)}}}function He(t){let e,n,i=t[6]&&Se(t),r=(t[0]||!t[6])&&ie(t);return{c(){e=m("div"),i&&i.c(),n=R(),r&&r.c(),this.c=I,E(e,"class","conditional-logic-repeater"),E(e,"part","container")},m(l,o){O(l,e,o),i&&i.m(e,null),_(e,n),r&&r.m(e,null),t[19](e)},p(l,[o]){l[6]&&i.p(l,o),l[0]||!l[6]?r?r.p(l,o):(r=ie(l),r.c(),r.m(e,null)):r&&(r.d(1),r=null)},i:I,o:I,d(l){l&&T(e),i&&i.d(),r&&r.d(),t[19](null)}}}function Ne(t,e,n){let{enabled:i=!0}=e,{behavior:r="show"}=e,{logic:l="and"}=e,{conditions:o=[{field:"",comparison:"is",value:""}]}=e,{fields:c}=e,{toggleable:s}=e,f;const w=typeof s<"u",d=(c==null?void 0:c.length)>0?JSON.parse(c):"",g=()=>{n(3,o=[...o,{field:"",comparison:"is",value:""}])},v=a=>{n(3,o=o.filter((C,k)=>k!==a)),h()},h=()=>{f.dispatchEvent(new CustomEvent("es-conditional-logic-repeater-update",{detail:{enabled:i,behavior:r,logic:l,conditions:o},composed:!0}))};function H(){i=this.checked,n(0,i)}function M(){r=W(this),n(1,r)}function N(){l=W(this),n(2,l)}function u(a,C){a[C].field=W(this),n(3,o),n(7,d)}function p(a,C){a[C].comparison=W(this),n(3,o),n(7,d)}function L(a,C){a[C].value=this.value,n(3,o),n(7,d)}const y=a=>v(a);function b(a){G[a?"unshift":"push"](()=>{f=a,n(5,f)})}return t.$$set=a=>{"enabled"in a&&n(0,i=a.enabled),"behavior"in a&&n(1,r=a.behavior),"logic"in a&&n(2,l=a.logic),"conditions"in a&&n(3,o=a.conditions),"fields"in a&&n(4,c=a.fields),"toggleable"in a&&n(11,s=a.toggleable)},[i,r,l,o,c,f,w,d,g,v,h,s,H,M,N,u,p,L,y,b]}class Oe extends _e{constructor(e){super(),this.shadowRoot.innerHTML=`<style>.conditional-logic-repeater{--rptr-font-family:system-ui, sans-serif;--rptr-accent-color:var(
		--es-conditional-logic-repeater-accent,
		#29a3a3
	);--rptr-input-border-color:var(
		--es-conditional-logic-repeater-input-border,
		#8f9c9c
	);--rptr-font-size:var(--es-conditional-logic-repeater-font-size, 0.9em);font-family:system-ui, sans-serif;font-size:var(--rptr-font-size);display:flex;flex-direction:column;gap:1rem}.conditional-logic-repeater,.conditional-logic-repeater *{box-sizing:border-box}button{padding:0.25rem;border:0;margin:0;width:1.75rem;height:1.75rem;background-color:transparent;color:var(--rptr-accent-color);border:1px solid var(--rptr-accent-color);border-radius:100rem;display:flex;align-items:center;justify-content:center;cursor:pointer}button svg{width:1rem;height:1rem}input[type="checkbox"]{width:1.25rem;height:1.25rem;accent-color:var(--rptr-accent-color)}input[type="text"],select{height:2.5rem;border-radius:0.25rem;border:1px solid var(--rptr-input-border-color);padding:0.5rem;outline-color:var(--es-conditional-logic-repeater-accent);accent-color:var(--es-conditional-logic-repeater-accent)}label,.conditional-logic-repeater__item{display:flex;align-items:center;line-height:1}label{gap:0.25rem}.conditional-logic-repeater__item{gap:0.75rem}</style>`,Le(this,{target:this.shadowRoot,props:be(this.attributes),customElement:!0},Ne,He,pe,{enabled:0,behavior:1,logic:2,conditions:3,fields:4,toggleable:11},null),e&&(e.target&&O(e.target,this,e.anchor),e.props&&(this.$set(e.props),j()))}static get observedAttributes(){return["enabled","behavior","logic","conditions","fields","toggleable"]}get enabled(){return this.$$.ctx[0]}set enabled(e){this.$$set({enabled:e}),j()}get behavior(){return this.$$.ctx[1]}set behavior(e){this.$$set({behavior:e}),j()}get logic(){return this.$$.ctx[2]}set logic(e){this.$$set({logic:e}),j()}get conditions(){return this.$$.ctx[3]}set conditions(e){this.$$set({conditions:e}),j()}get fields(){return this.$$.ctx[4]}set fields(e){this.$$set({fields:e}),j()}get toggleable(){return this.$$.ctx[11]}set toggleable(e){this.$$set({toggleable:e}),j()}}customElements.define("conditional-logic-repeater",Oe);
}
