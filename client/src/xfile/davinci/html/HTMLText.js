//>>built
define("davinci/html/HTMLText",["dojo/_base/declare","davinci/html/HTMLItem"],function(c,d){return c("davinci.html.HTMLText",d,{constructor:function(a){this.elementType="HTMLText";this.value=a||""},getText:function(a){return this.value},setText:function(a){if(this.wasParsed||this.parent&&this.parent.wasParsed){var b=a.length-this.value.length;0<b&&this.getHTMLFile().updatePositions(this.startOffset+1,b)}this.value=a},getLabel:function(){return 15>this.value.length?this.value:this.value.substring(0,
15)+"..."}})});
//# sourceMappingURL=HTMLText.js.map