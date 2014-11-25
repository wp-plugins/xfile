//>>built
/*

 Copyright (c) 2011, 2012 IBM Corporation and others.
 Copyright (c) 2012 VMware, Inc.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors:
     IBM Corporation - initial API and implementation
     Andrew Eisenberg - rename to jsTemplateContentAssist.js
*/
define("orion/editor/jsTemplateContentAssist",[],function(){function k(e,a,c){var b=c-e.length,g=[],d="";for(c-=1;0<c;){var f=a.charAt(c--);if("\n"===f||"\r"===f)break;d=/\s/.test(f)?f.concat(d):""}a=d;0==="if".indexOf(e)&&(d="if - if statement",c=[{offset:b+4,length:9}],f=b+a.length+18,g.push({proposal:("if (condition) {\n"+a+"\t\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}),d="if - if else statement",c=[{offset:b+4,length:9}],f=b+a.length+18,g.push({proposal:("if (condition) {\n"+
a+"\t\n"+a+"} else {\n"+a+"\t\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}));0==="for".indexOf(e)&&(d="for - iterate over array",c=[{offset:b+9,length:1},{offset:b+20,length:5}],f=b+a.length+42,g.push({proposal:("for (var i \x3d 0; i \x3c array.length; i++) {\n"+a+"\t\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}),d="for..in - iterate over properties of an object",c=[{offset:b+9,length:8},{offset:b+21,length:6}],f=b+2*a.length+73,g.push({proposal:("for (var property in object) {\n"+
a+"\tif (object.hasOwnProperty(property)) {\n"+a+"\t\t\n"+a+"\t}\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}));0==="while".indexOf(e)&&(d="while - while loop with condition",c=[{offset:b+7,length:9}],f=b+a.length+21,g.push({proposal:("while (condition) {\n"+a+"\t\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}));0==="do".indexOf(e)&&(d="do - do while loop with condition",c=[{offset:b+16,length:9}],f=b+a.length+6,g.push({proposal:("do {\n"+a+
"\t\n"+a+"} while (condition);").substring(e.length),description:d,positions:c,escapePosition:f}));0==="switch".indexOf(e)&&(d="switch - switch case statement",c=[{offset:b+8,length:10},{offset:b+28,length:6}],f=b+2*a.length+38,g.push({proposal:("switch (expression) {\n"+a+"\tcase value1:\n"+a+"\t\t\n"+a+"\t\tbreak;\n"+a+"\tdefault:\n"+a+"}").substring(e.length),description:d,positions:c,escapePosition:f}));0==="try".indexOf(e)&&(d="try - try..catch statement",f=b+a.length+7,g.push({proposal:("try {\n"+
a+"\t\n"+a+"} catch (err) {\n"+a+"}").substring(e.length),description:d,escapePosition:f}),d="try - try..catch statement with finally block",f=b+a.length+7,g.push({proposal:("try {\n"+a+"\t\n"+a+"} catch (err) {\n"+a+"} finally {\n"+a+"}").substring(e.length),description:d,escapePosition:f}));return g}function l(e,a,c){a="break case catch continue debugger default delete do else finally for function if in instanceof new return switch this throw try typeof var void while with".split(" ");c=[];for(var b=
0;b<a.length;b++)0===a[b].indexOf(e)&&c.push({proposal:a[b].substring(e.length),description:a[b]});return c}function h(){}var m={":":":","!":"!","@":"@","#":"#",$:"$","^":"^","\x26":"\x26","*":"*",".":".","?":"?","\x3c":"\x3c","\x3e":"\x3e"};h.prototype={computeProposals:function(e,a,c){c=c.prefix;for(var b=[],g=a-c.length-1,d="";0<=g&&!(d=e[g],"\n"===d||"\r"===d);)if(/\s/.test(d))g--;else break;if(m[d])return b;b=b.concat(k(c,e,a));return b=b.concat(l(c,e,a))}};return{JSTemplateContentAssistProvider:h}});
//# sourceMappingURL=jsTemplateContentAssist.js.map