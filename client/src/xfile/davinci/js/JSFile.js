//>>built
define("davinci/js/JSFile",["dojo/_base/declare","davinci/js/JSElement"],function(c,d){return c("davinci.js.JSFile",d,{constructor:function(a){this.elementType="JSFile";this.nosemicolon=!0;this._textContent="";a&&(this.origin=a)},getText:function(a){return this._textContent},setText:function(a){this._textContent=a},getLabel:function(){return this.fileName},getID:function(){return this.fileName},visit:function(a){if(!a.visit(this))for(var b=0;b<this.children.length;b++)this.children[b].visit(a);a.endVisit&&
a.endVisit(this)}})});
//# sourceMappingURL=JSFile.js.map