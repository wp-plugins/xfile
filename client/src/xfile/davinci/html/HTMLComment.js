//>>built
define("davinci/html/HTMLComment",["dojo/_base/declare","davinci/html/HTMLItem"],function(b,c){return b("davinci.html.HTMLComment",c,{constructor:function(a){this.elementType="HTMLComment";this.value=a||""},getText:function(a){a=this.isProcessingInstruction?"":"--";return"\x3c!"+a+this.value+a+"\x3e"}})});
//# sourceMappingURL=HTMLComment.js.map