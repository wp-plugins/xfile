//>>built
define("davinci/js/JSArrayAccess",["dojo/_base/declare","davinci/js/JSExpression"],function(c,d){return c("davinci.js.JSArrayAccess",d,{constructor:function(){this.elementType="JSArrayAccess";this.index=this.array=null},getText:function(a){var b="";this.comment&&(b+=this.printNewLine(a)+this.comment.getText(a));this.label&&(b+=this.printNewLine(a)+this.label.getText(a));return b+=this.array.getText(a)+"["+this.index.getText(a)+"]"},visit:function(a){a.visit(this)||(this.array.visit(a),this.index.visit(a));
a.endVisit&&a.endVisit(this)}})});
//# sourceMappingURL=JSArrayAccess.js.map