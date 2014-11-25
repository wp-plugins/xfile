//>>built
/*

 Copyright (c) 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors: IBM Corporation - initial API and implementation
*/
(function(f,e){"function"===typeof define&&define.amd?define("orion/Deferred",e):"object"===typeof exports?module.exports=e():(f.orion=f.orion||{},f.orion.Deferred=e())})(this,function(){function f(){for(var a;a=r.shift()||s.shift();)a();n=!1}function e(a,c){(c?s:r).push(a);n||(n=!0,c?setTimeout(f,0):f())}function t(a){return function(){a.apply(null,arguments)}}function u(){}function v(){var a=Error("Cancel");a.name="Cancel";return a}function l(){function a(){for(var a;a=m.shift();){var b=a.deferred,
k="resolved"===g?"resolve":"reject";if("function"===typeof a[k])try{var h=a[k](c);h&&"function"===typeof h.then?(b.cancel=h.cancel||u,h.then(t(b.resolve),t(b.reject),b.progress)):b.resolve(h)}catch(q){b.reject(q)}else b[k](c)}}var c,g,m=[],d=this;this.reject=function(p,b){g||(g="rejected",c=p,m.length&&e(a));return d.promise};this.resolve=function(p,b){g||(g="resolved",c=p,m.length&&e(a));return d.promise};this.progress=function(a,b){g||m.forEach(function(b){b.progress&&b.progress(a)});return d.promise};
this.cancel=function(){g||d.reject(v())};this.then=function(c,b,d){c={resolve:c,reject:b,progress:d,deferred:new l};var h=c.deferred,q=this.cancel.bind(this),f=function(){e(function(){(h.cancel===f?q:h.cancel)()},!0)};h.cancel=f;b=h.promise;b.cancel=function(){h.cancel()};m.push(c);g&&e(a,!0);return b};this.promise={then:this.then,cancel:this.cancel}}var r=[],s=[],n=!1;l.all=function(a,c){function g(a,c){b||(e[a]=c,0===--d&&k.resolve(e))}function f(a,d){if(!b){if(c)try{g(a,c(d));return}catch(e){d=
e}k.reject(d)}}var d=a.length,e=[],b=!1,k=new l;k.then(null,function(){b=!0;a.forEach(function(a){a.cancel&&a.cancel()})});0===d?k.resolve(e):a.forEach(function(a,b){a.then(g.bind(null,b),f.bind(null,b))});return k.promise};l.when=function(a,c,e,f){var d;a&&"function"===typeof a.then||(d=new l,d.resolve(a),a=d.promise);return a.then(c,e,f)};return l});
//# sourceMappingURL=Deferred.js.map