//>>built
/*

 Copyright (c) 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors: IBM Corporation - initial API and implementation
*/
define("orion/editor/util",function(){var a=navigator.userAgent,f=parseFloat(a.split("MSIE")[1])||void 0,g=parseFloat(a.split("Firefox/")[1]||a.split("Minefield/")[1])||void 0,h=-1!==a.indexOf("Opera"),c=parseFloat(a.split("Chrome/")[1])||void 0,k=-1!==a.indexOf("Safari")&&!c,l=parseFloat(a.split("WebKit/")[1])||void 0,m=-1!==a.indexOf("Android"),d=-1!==a.indexOf("iPad"),a=-1!==a.indexOf("iPhone"),n=d||a,p=-1!==navigator.platform.indexOf("Mac"),e=-1!==navigator.platform.indexOf("Win"),q=-1!==navigator.platform.indexOf("Linux");
return{formatMessage:function(a){var b=arguments;return a.replace(/\$\{([^\}]+)\}/g,function(a,r){return b[(r<<0)+1]})},createElement:function(a,b){return a.createElementNS?a.createElementNS("http://www.w3.org/1999/xhtml",b):a.createElement(b)},isIE:f,isFirefox:g,isOpera:h,isChrome:c,isSafari:k,isWebkit:l,isAndroid:m,isIPad:d,isIPhone:a,isIOS:n,isMac:p,isWindows:e,isLinux:q,platformDelimiter:e?"\r\n":"\n"}});
//# sourceMappingURL=util.js.map