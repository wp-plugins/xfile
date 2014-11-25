//>>built
define("davinci/html/HTMLModel",["dojo/_base/declare","davinci/model/Model"],function(b,c){davinci.html||(davinci.html={});davinci.html._noFormatElements={span:!0,b:!0,it:!0};davinci.html.escapeXml=function(a){return!a?a:a.replace(/&/g,"\x26amp;").replace(/</g,"\x26lt;").replace(/>/g,"\x26gt;").replace(/"/g,"\x26quot;")};davinci.html.unEscapeXml=function(a){return!a||"string"!==typeof a?a:a.replace(/&quot;/g,'"').replace(/&gt;/g,"\x3e").replace(/&lt;/g,"\x3c").replace(/&amp;/g,"\x26")};return b("davinci.html.HTMLModel",
c,{})});
//# sourceMappingURL=HTMLModel.js.map