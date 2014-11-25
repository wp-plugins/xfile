//>>built
define("davinci/css",["dojo/_base/window","dojo/dom-construct","dojo/dom-attr"],function(b,d){var e=b.doc.getElementsByTagName("head")[0],c={};return{load:function(a,b,f){a=b.toUrl(a);a in c||(d.create("link",{rel:"stylesheet",type:"text/css",href:a},e),c[a]=1);f()}}});
//# sourceMappingURL=css.js.map