//>>built
/*

 Copyright (c) 2010, 2012 IBM Corporation and others.
 All rights reserved. This program and the accompanying materials are made 
 available under the terms of the Eclipse Public License v1.0 
 (http://www.eclipse.org/legal/epl-v10.html), and the Eclipse Distribution 
 License v1.0 (http://www.eclipse.org/org/documents/edl-v10.html). 

 Contributors: 
  Felipe Heidrich (IBM Corporation) - initial API and implementation
  Silenio Quarti (IBM Corporation) - initial API and implementation
*/
define("orion/editor/textDND",[],function(){function e(a,b){this._view=a;this._undoStack=b;this._dragSelection=null;this._dropOffset=-1;this._dropText=null;var c=this;this._listener={onDragStart:function(a){c._onDragStart(a)},onDragEnd:function(a){c._onDragEnd(a)},onDragEnter:function(a){c._onDragEnter(a)},onDragOver:function(a){c._onDragOver(a)},onDrop:function(a){c._onDrop(a)},onDestroy:function(a){c._onDestroy(a)}};a.addEventListener("DragStart",this._listener.onDragStart);a.addEventListener("DragEnd",
this._listener.onDragEnd);a.addEventListener("DragEnter",this._listener.onDragEnter);a.addEventListener("DragOver",this._listener.onDragOver);a.addEventListener("Drop",this._listener.onDrop);a.addEventListener("Destroy",this._listener.onDestroy)}e.prototype={destroy:function(){var a=this._view;a&&(a.removeEventListener("DragStart",this._listener.onDragStart),a.removeEventListener("DragEnd",this._listener.onDragEnd),a.removeEventListener("DragEnter",this._listener.onDragEnter),a.removeEventListener("DragOver",
this._listener.onDragOver),a.removeEventListener("Drop",this._listener.onDrop),a.removeEventListener("Destroy",this._listener.onDestroy),this._view=null)},_onDestroy:function(a){this.destroy()},_onDragStart:function(a){var b=this._view,c=b.getSelection(),b=b.getModel();b.getBaseModel&&(c.start=b.mapOffset(c.start),c.end=b.mapOffset(c.end),b=b.getBaseModel());if(b=b.getText(c.start,c.end))this._dragSelection=c,a.event.dataTransfer.effectAllowed="copyMove",a.event.dataTransfer.setData("Text",b)},_onDragEnd:function(a){var b=
this._view;if(this._dragSelection){this._undoStack&&this._undoStack.startCompoundChange();(a="move"===a.event.dataTransfer.dropEffect)&&b.setText("",this._dragSelection.start,this._dragSelection.end);if(this._dropText){var c=this._dropText,d=this._dropOffset;a&&(d>=this._dragSelection.end?d-=this._dragSelection.end-this._dragSelection.start:d>=this._dragSelection.start&&(d=this._dragSelection.start));b.setText(c,d,d);b.setSelection(d,d+c.length);this._dropText=null;this._dropOffset=-1}this._undoStack&&
this._undoStack.endCompoundChange()}this._dragSelection=null},_onDragEnter:function(a){this._onDragOver(a)},_onDragOver:function(a){var b=a.event.dataTransfer.types;if(b){var c=!this._view.getOptions("readonly");c&&(c=b.contains?b.contains("text/plain"):-1!==b.indexOf("text/plain"));c||(a.event.dataTransfer.dropEffect="none")}},_onDrop:function(a){var b=this._view,c=a.event.dataTransfer.getData("Text");c&&(a=b.getOffsetAtLocation(a.x,a.y),this._dragSelection?(this._dropOffset=a,this._dropText=c):
(b.setText(c,a,a),b.setSelection(a,a+c.length)))}};return{TextDND:e}});
//# sourceMappingURL=textDND.js.map