//>>built
/*

 Copyright (c) 2011, 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors: IBM Corporation - initial API and implementation 
*/
define("orion/editor/htmlGrammar",[],function(){return{HtmlGrammar:function(){return{scopeName:"source.html",uuid:"3B5C76FB-EBB5-D930-F40C-047D082CE99B",patterns:[{begin:"\x3c!(doctype|DOCTYPE)",end:"\x3e",contentName:"entity.name.tag.doctype.html",beginCaptures:{"0":{name:"entity.name.tag.doctype.html"}},endCaptures:{"0":{name:"entity.name.tag.doctype.html"}}},{begin:"\x3c!--",end:"--\x3e",beginCaptures:{"0":{name:"punctuation.definition.comment.html"}},endCaptures:{"0":{name:"punctuation.definition.comment.html"}},
patterns:[{match:"--",name:"invalid.illegal.badcomment.html"}],contentName:"comment.block.html"},{match:"\x3c[A-Za-z0-9_\\-:]+(?\x3d ?)",name:"entity.name.tag.html"},{include:"#attrName"},{include:"#qString"},{include:"#qqString"},{include:"#entity"},{match:"\x3c/[A-Za-z0-9_\\-:]+\x3e",name:"entity.name.tag.html"},{match:"\x3e",name:"entity.name.tag.html"}],repository:{attrName:{match:"[A-Za-z\\-:]+(?\x3d\\s*\x3d\\s*['\"])",name:"entity.other.attribute.name.html"},qqString:{match:'(")[^"]+(")',name:"token.string"},
qString:{match:"(')[^']+(')",name:"token.string"},entity:{match:"\x26[A-Za-z0-9]+;",name:"constant.character.entity.html"}}}}}});
//# sourceMappingURL=htmlGrammar.js.map