//>>built
define("davinci/js/JSFieldReference",["dojo/_base/declare","davinci/js/JSExpression"],function(c,d){return c("davinci.js.JSFieldReference",d,{constructor:function(){this.elementType="JSFieldReference";this.name="";this.receiver=null},getText:function(a){var b="";this.comment&&(b+=this.printNewLine(a)+this.comment.getText(a));this.label&&(b+=this.printNewLine(a)+this.label.getText(a));return b+this.receiver.getText(a)+"."+this.name},visit:function(a){a.visit(this)||this.receiver.visit(a);a.endVisit&&
a.endVisit(this)}})});
//# sourceMappingURL=JSFieldReference.js.map