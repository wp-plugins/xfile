//>>built
/*

 Copyright (c) 2010, 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors: 
  Felipe Heidrich (IBM Corporation) - initial API and implementation
  Silenio Quarti (IBM Corporation) - initial API and implementation
*/
define("orion/editor/keyBinding",["orion/editor/util"],function(f){function g(a,b,c,d,e){this.keyCode="string"===typeof a?a.toUpperCase().charCodeAt(0):a;this.mod1=void 0!==b&&null!==b?b:!1;this.mod2=void 0!==c&&null!==c?c:!1;this.mod3=void 0!==d&&null!==d?d:!1;this.mod4=void 0!==e&&null!==e?e:!1}g.prototype={match:function(a){return this.keyCode===a.keyCode?this.mod1!==(f.isMac?a.metaKey:a.ctrlKey)||this.mod2!==a.shiftKey||this.mod3!==a.altKey||f.isMac&&this.mod4!==a.ctrlKey?!1:!0:!1},equals:function(a){return!a||
this.keyCode!==a.keyCode||this.mod1!==a.mod1||this.mod2!==a.mod2||this.mod3!==a.mod3||this.mod4!==a.mod4?!1:!0}};return{KeyBinding:g}});
//# sourceMappingURL=keyBinding.js.map