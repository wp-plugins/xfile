//>>built
define("davinci/js/JSUnaryExpression",["dojo/_base/declare","davinci/js/JSExpression"],function(c,d){return c("davinci.js.JSUnaryExpression",d,{constructor:function(){this.elementType="JSUnaryExpression";this.expr=this.operator=null},getText:function(a){var b="";this.comment&&(b+=this.printNewLine(a)+this.comment.getText(a));this.label&&(b+=this.printNewLine(a)+this.label.getText(a));return b+=this.operator+" "+this.expr.getText(a)},visit:function(a){a.visit(this)||this.expr.visit(a);a.endVisit&&
a.endVisit(this)}})});
//# sourceMappingURL=JSUnaryExpression.js.map