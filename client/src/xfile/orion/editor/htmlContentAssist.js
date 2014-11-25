//>>built
/*

 Copyright (c) 2011, 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors:
     IBM Corporation - initial API and implementation
*/
define("orion/editor/htmlContentAssist",[],function(){function m(){}m.prototype={leadingWhitespace:function(b,e){var l="";for(e-=1;0<e;){var f=b.charAt(e--);if("\n"===f||"\r"===f)break;l=/\s/.test(f)?f.concat(l):""}return l},computeProposals:function(b,e,l){function f(a){return a.substring(l.prefix.length)}var g=[];if(0===b.length)return g.push({proposal:'\x3c!DOCTYPE html\x3e\n\x3chtml lang\x3d"en"\x3e\n\t\x3chead\x3e\n\t\t\x3cmeta charset\x3dutf-8\x3e\n\t\t\x3ctitle\x3eMy Document\x3c/title\x3e\n\t\x3c/head\x3e\n\t\x3cbody\x3e\n\t\t\x3ch1\x3eA basic HTML document\x3c/h1\x3e\n\t\t\x3cp\x3e\n\t\t\t\n\t\t\x3c/p\x3e\n\t\x3c/body\x3e\n\x3c/html\x3e',
description:"Simple HTML document",escapePosition:e+152}),g;var d=l.prefix;if("\x3c"!==b.charAt(e-d.length-1))return g;for(var a,c,k="abbr b button canvas cite command dd del dfn dt em embed font h1 h2 h3 h4 h5 h6 i ins kbd label li mark meter object option output progress q rp rt samp small strong sub sup td time title tt u var".split(" "),h=0;h<k.length;h++)a=k[h],0===a.indexOf(d)&&(c=a+"\x3e\x3c/"+a+"\x3e",a=e+a.length-d.length+1,g.push({proposal:f(c),description:"\x3c"+c,escapePosition:a}));k=
"address article aside audio bdo blockquote body caption code colgroup datalist details div fieldset figure footer form head header hgroup iframe legend map menu nav noframes noscript optgroup p pre ruby script section select span style tbody textarea tfoot th thead tr video".split(" ");b=this.leadingWhitespace(b,e);for(h=0;h<k.length;h++)a=k[h],0===a.indexOf(d)&&(c=a+"\x3e\n"+b+"\t\n"+b+"\x3c/"+a+"\x3e",a=e+a.length-d.length+b.length+3,g.push({proposal:f(c),description:"\x3c"+c,escapePosition:a}));
k="area base br col hr input link meta param keygen source".split(" ");for(h=0;h<k.length;h++)a=k[h],0===a.indexOf(d)&&(c=a+"/\x3e",a=e+a.length-d.length+2,g.push({proposal:f(c),description:"\x3c"+c,escapePosition:a}));0==="img".indexOf(d)&&(c='img src\x3d"" alt\x3d"Image"/\x3e',g.push({proposal:f(c),description:"\x3c"+c,escapePosition:e+9-d.length}));"a"===d&&g.push({proposal:f('a href\x3d""\x3e\x3c/a\x3e'),description:"\x3ca\x3e\x3c/a\x3e - HTML anchor element",escapePosition:e+7});0==="ul".indexOf(d)&&
(c="\x3cul\x3e - unordered list",a=e-d.length+b.length+9,g.push({proposal:f("ul\x3e\n"+b+"\t\x3cli\x3e\x3c/li\x3e\n"+b+"\x3c/ul\x3e"),description:c,escapePosition:a}));0==="ol".indexOf(d)&&(c="\x3col\x3e - ordered list",a=e-d.length+b.length+9,g.push({proposal:f("ol\x3e\n"+b+"\t\x3cli\x3e\x3c/li\x3e\n"+b+"\x3c/ol\x3e"),description:c,escapePosition:a}));0==="dl".indexOf(d)&&(c="\x3cdl\x3e - definition list",a=e-d.length+b.length+9,g.push({proposal:f("dl\x3e\n"+b+"\t\x3cdt\x3e\x3c/dt\x3e\n"+b+"\t\x3cdd\x3e\x3c/dd\x3e\n"+
b+"\x3c/dl\x3e"),description:c,escapePosition:a}));0==="table".indexOf(d)&&(c="\x3ctable\x3e - basic HTML table",a=e-d.length+2*b.length+19,g.push({proposal:f("table\x3e\n"+b+"\t\x3ctr\x3e\n"+b+"\t\t\x3ctd\x3e\x3c/td\x3e\n"+b+"\t\x3c/tr\x3e\n"+b+"\x3c/table\x3e"),description:c,escapePosition:a}));return g}};return{HTMLContentAssistProvider:m}});
//# sourceMappingURL=htmlContentAssist.js.map