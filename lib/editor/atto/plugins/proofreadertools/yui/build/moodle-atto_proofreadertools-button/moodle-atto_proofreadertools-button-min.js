YUI.add("moodle-atto_proofreadertools-button",function(e,t){e.namespace("M.atto_proofreadertools").Button=e.Base.create("button",e.M.editor_atto.EditorPlugin,[],{initializer:function(){if(!this.get("enablebutton"))return;this.addButton({icon:"correctoricon",iconComponent:"atto_proofreadertools",title:"correctoricon",buttonName:"correctoricon",callback:this._correctorIcon}),this.addButton({icon:"blueicon",iconComponent:"atto_proofreadertools",title:"blueicon",buttonName:"blueicon",callback:this._blueIcon}),this.addButton({icon:"redicon",iconComponent:"atto_proofreadertools",title:"redicon",buttonName:"redicon",callback:this._redIcon}),this.addButton({icon:"doubleslashicon",iconComponent:"atto_proofreadertools",title:"doubleslashicon",buttonName:"doubleslashicon",callback:this._doubleslashIcon})},_correctorIcon:function(e){var t=this.get("host");e.preventDefault();var n=t.getSelection(),r='<span class="blauioc">[<span>'+n+"</span>]</span>",i=t.insertContentAtFocusPoint(r),s=i.getDOMNode().childNodes[1],o=window.rangy.createRange(),u=window.rangy.getSelection();o.setStartBefore(s),o.setEnd(s,s.childNodes.length?1:0),u.removeAllRanges(),u.addRange(o),this.markUpdated()},_blueIcon:function(){this.get("host").toggleInlineSelectionClass(["blauioc"])},_redIcon:function(){this.get("host").toggleInlineSelectionClass(["vermellioc"])},_doubleslashIcon:function(e){var t=this.get("host");e.preventDefault();var n=this.get("host").getSelection();t.setSelection(n),t.insertContentAtFocusPoint("[//]"),this.markUpdated()}},{ATTRS:{enablebutton:{value:!1}}})},"@VERSION@",{requires:["moodle-editor_atto-plugin"]});