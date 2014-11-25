//>>built
/*

 Copyright (c) 2011 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors:
     IBM Corporation - initial API and implementation
*/
define("orion/editor/regex",[],function(){return{escape:function(a){return a.replace(/([\\$\^*\/+?\.\(\)|{}\[\]])/g,"\\$\x26")},parse:function(a){return(a=/^\s*\/(.+)\/([gim]{0,3})\s*$/.exec(a))?{pattern:a[1],flags:a[2]}:null}}});
//# sourceMappingURL=regex.js.map