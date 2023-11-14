
YUI.add('moodle-atto_snippet-button', function (Y, NAME) {


    if(Y.Handlebars) {
        // Add helpers to Handlebars
        Y.Handlebars.registerHelper('times', function(n, block) {
            var accum = '';
            for(var i = 0; i < n; ++i) {
                block.data.index = i;
                block.data.first = i === 0;
                block.data.last = i === (n - 1);
                accum += block.fn(this);
            }
            return accum;
        });
        Y.Handlebars.registerHelper('ifCond', function(a, condition, b, opts) {
            var bool;
            if (condition == "==" || condition == "eq"){
                bool = (a==b);
            } else if (condition == "<" || condition == "lt"){
                bool = (a<b);
            } else if (condition == ">" || condition == "gt"){
                bool = (a>b);
            } else if (condition == "<=" || condition == "leq"){
                bool = (a<=b);
            } else if (condition == ">=" || condition == "geq"){
                bool = (a>=b);
            } else {
                bool = (a!=b);
            }
    
            if (bool) {
                return opts.fn(this);
            } else {
                return opts.inverse(this);
            }
        });
    

    Y.Handlebars.registerHelper("math", function(lvalue, operator, rvalue, options) {
        lvalue = parseFloat(lvalue);
        rvalue = parseFloat(rvalue);
            
        return {
            "+": lvalue + rvalue,
            "-": lvalue - rvalue,
            "*": lvalue * rvalue,
            "/": lvalue / rvalue,
            "%": lvalue % rvalue
        }[operator];
    });

    }

//Search the correct position in an array of string to keep it alphabetically ordered
function findAlphPosition(container, value) {
   
    var pos = 0;
    for (var i=0; i<container.length; i++) {
        var valueArr = container[i].get("title");
        if (valueArr.localeCompare(value) < 0) {
            pos += 1;
        }
    }
    return pos;
}

var STORE = {valors:{}};

function loadStore() {
    STORE = JSON.parse(window.localStorage.getItem("iedib-atto-snippets") || '{"valors":{}}');
}
function saveStore() {
    window.localStorage.setItem("iedib-atto-snippets", JSON.stringify(STORE));
}

function getFromStorage(thevariable, defaultValue) {
    console.log(STORE)
    if(thevariable.substring(0,1)=="$" && STORE['valors'][thevariable]!=null) {
       return STORE['valors'][thevariable];
    } else {
        return defaultValue;
    }
}

//Default colors for categories
var CATEGORY_COLORS = ['darkblue', 'orange', 'darkred', 'darkgreen', 'brown', 'crimson', 'purple'];

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_snippet
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_snippet-button
 */

/**
 * Atto text editor snippet plugin.
 *
 * @namespace M.atto_snippet
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_snippet';
var LOGNAME = 'atto_snippet';

var CSS = {
        INPUTSUBMIT: 'atto_media_urlentrysubmit',
        INPUTCANCEL: 'atto_media_urlentrycancel',
        KEYBUTTON: 'atto_snippet_snippetbutton',
        HEADERTEXT: 'atto_snippet_headertext',
        INSTRUCTIONSTEXT: 'atto_snippet_instructionstext',
        TEMPLATEVARIABLE: 'atto_snippet_snippetvariable',
        CATEGORY_LATERAL: 'atto_snippet_lateral',
        CATEGORY_TITLE: 'atto_snippet_title',
    };

var FIELDSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" style="text-align:justify">' +
            '<h4 class="' + CSS.HEADERTEXT + '">{{headertext}} {{snippetname}}</h4>' +
            '<div class="' + CSS.INSTRUCTIONSTEXT + '">{{instructions}}</div>' +
        '</div><hr style="margin:0">';

var CATEGORYTEMPLATE = '' +
        '<div style="border: 2px solid {{color}};margin:2px;width:98%;display:table">' +
            '<div class="' + CSS.CATEGORY_LATERAL + '" style="background:{{color}} !important;">' +
            '<p class="' + CSS.CATEGORY_TITLE + '">{{categorytext}}</p>' +
            '</div>'
        '</div>';
var CATEGORYTEMPLATEINNER = '<div style="display: table-cell;column-count:2;padding:5px"></div>';

var BUTTONSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            //'<h4 class="' + CSS.HEADERTEXT + '">{{headertext}}</h4>' +
        '</div>';
        
var BUTTONTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="atto_snippet_buttons mdl-align" style="width:99%;margin:0;">' +
            '<button style="width:95%" class="atto_snippet_btn ' + CSS.KEYBUTTON + '_{{snippetindex}}">{{#if snippetimage}} <img style="width:30px;" src="{{snippetimage}}"/> {{/if}} {{snippetname}}</button>' +
        '</div>';

var FIELDTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" style="display:table;width:96%;"><span style="font-weight:bold;display:table-cell;">{{snippetvar}}&nbsp;&nbsp;</span>' +
            '<span style="display:table-cell;"><input type="text" style="width:99%;" class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}}" value="{{defaultvalue}}"></input></span>' +
        '</div>';

var CHECKBOXTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" style="display:table;width:96%;"><span style="font-weight:bold; display:table-cell;">{{snippetvar}}&nbsp;&nbsp;</span>' +
            '<span style="display:table-cell;"><input type="checkbox" class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}}" value="{{defaultvalue}}" {{#ifCond defaultvalue \'eq\' \'1\'}}checked{{/ifCond}}></input></span>' +
        '</div>';
        
var SELECTCONTAINERTEMPLATE = '' +
            '<div id="{{elementid}}_{{innerform}}"><span style="font-weight:bold;">{{variable}}&nbsp;&nbsp;</span></div>';
			
var SELECTTEMPLATE = '' +
            '<select class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}} atto_snippet_field"></select>';

var OPTIONTEMPLATE ='' +
		'<option value="{{option}}">{{optionLabel}}</option>';


var SUBMITTEMPLATE = '' +
  '<form class="atto_form">' +
   '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
	'<button class="' + CSS.INPUTSUBMIT +'">{{inserttext}}</button>' +
    '</div>' +
	'</form>';

/*filter thevariables array
 * get rid of variables starting by @ and /
 * get rid of this
 * if variable is #each xxxx --> replace it to xxxx
 * get rid of other #
*/

function filterOutHelpers(thevariablesRaw) {	
	var thevariables = [];
	var j = 0;
	for(var i=0; i<thevariablesRaw.length; i++) {
		var thevariable = thevariablesRaw[i];
		if (thevariable.substring(0,6)==="#each ") {
            var tvar = thevariable.replace("#each ", "").trim();
            if (thevariables.indexOf(tvar) < 0) {
                thevariables[j] = tvar;
                j += 1;
            }
		} else if(thevariable.substring(0,7)==="#times ") {
			var tvar = thevariable.replace("#times ", "").trim();
			if (thevariables.indexOf(tvar) < 0) {
                thevariables[j] = tvar;
                j += 1;
            }
		} else if(thevariable.substring(0,8)==="#ifCond ") {
			var tvar = thevariable.replace("#ifCond ", "").split(" ")[0].trim();
			if (thevariables.indexOf(tvar) < 0) {
                thevariables[j] = tvar;
                j += 1;
            }
		} else if(thevariable.substring(0,3)=="../") {
			var pos = thevariable.lastIndexOf("/");
			thevariable = thevariable.substring(pos+1);
			if (thevariables.indexOf(thevariable) < 0) {
				thevariables[j] = thevariable;
				j += 1;
			}
		} else if( thevariable[0]!="@" && thevariable[0]!="/" &&
                    thevariable[0]!="#" && thevariable!="this" && 
                    thevariable!="else"&& thevariable.substring(0,5)!="math ") {
                    if (thevariables.indexOf(thevariable) < 0) {
                            thevariables[j] = thevariable;
                            j += 1;
                    }
		}
	} 
	return thevariables;
};

// Removes the last part of name_snippet | category_name
function trimCategory(name) {
    name = name || "";
    var indx = name.indexOf("|");
    if(indx>0) {
        name = name.substring(0, indx);
    }
    return name
}


Y.namespace('M.atto_snippet').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    initializer: function() {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')){
            return;
        }

        var theicon = 'iconone';


            // Add the snippet icon/buttons
            this.addButton({
                icon: 'ed/' + theicon,
                iconComponent: 'atto_snippet',
                buttonName: theicon,
                callback: this._displayDialogue,
                callbackArgs: theicon
            });

    },


     /**
     * Display the snippet buttons dialog
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function(e, clickedicon) {
        e.preventDefault();
        var width=550;


        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            focusAfterHide: clickedicon
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if (dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }
        
        //create content container
        var bodycontent =  Y.Node.create('<div style="max-height:420px;overflow:auto;"></div>');
        
        //create and append header
        var template = Y.Handlebars.compile(BUTTONSHEADERTEMPLATE),
            	content = Y.Node.create(template({
                headertext: M.util.get_string('chooseinsert', COMPONENTNAME)
            }));
         bodycontent.append(content);

        //get button nodes
        var buttons = this._getButtonsForSnippets(clickedicon);

        var categories = {'altres': []};
        //get all buttons categories
        Y.Array.each(buttons, function(button) {  
            var realBtn = button.one("button");
            var text = realBtn.get('innerHTML');
            if(text.indexOf("|")>-1) {
                var parts = text.split("|");
                var value = (parts[0] || "").trim();
                var key = (parts[1] || "").trim();
                var container = categories[key];
                button.set("title", value);
                realBtn.set('innerHTML', value);
                if(!container) {
                    container = [];
                    categories[key] = container;
                }
                 //Sort buttons by name title
                var pos = findAlphPosition(container, value);
                container.splice(pos, 0, button);
            } else {
                button.set("title", text);
                var container = categories['altres'];
                var pos = findAlphPosition(container, text);
                container.splice(pos, 0, button);
            }        
        });
        
        //Add a different color for each category
        var catIndex =0;
        var categoryKeys = Y.Object.keys(categories);
        categoryKeys = categoryKeys.sort();
        Y.Array.each(categoryKeys, function(catKey){
            var buttons = categories[catKey];
            var color = CATEGORY_COLORS[catIndex%CATEGORY_COLORS.length];
            catIndex += 1;
            
            //create category container
            var template = Y.Handlebars.compile(CATEGORYTEMPLATE);
            var categoryContainer = Y.Node.create(template({
                color: color,
                categorytext: catKey
            }));
            template = Y.Handlebars.compile(CATEGORYTEMPLATEINNER);
            var categoryContainerInner = Y.Node.create(template({ 
            }));
            categoryContainer.append(categoryContainerInner);
            bodycontent.append(categoryContainer);
            Y.Array.each(buttons, function(button) {  
                //loop start
                    //var realBtn = button.one("button");
                    //realBtn.setStyle("background", color);
                    categoryContainerInner.append(button);
                //loop end
            }, bodycontent);    

        });

        

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

	    /**
     * Display the form for each snippet
     *
     * @method _displayDialogue
     * @private
     */
    _showSnippetForm: function(e,snippetindex) {
        e.preventDefault();
        var width=500;

		
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px'
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }

        //get fields , 1 per variable
        var fields = this._getSnippetFields(snippetindex);
        var instructions = this.get('instructions')[snippetindex];
            instructions = decodeURIComponent(instructions);
	
		//get header node. It will be different if we have no fields
		if(fields && fields.length>0){
			var useheadertext  = M.util.get_string('fieldsheader', COMPONENTNAME);
		}else{
			var useheadertext =  M.util.get_string('nofieldsheader', COMPONENTNAME);
		}
		var template = Y.Handlebars.compile(FIELDSHEADERTEMPLATE),
            	content = Y.Node.create(template({
                snippetname: trimCategory(this.get('snippetnames')[snippetindex]),
                headertext: useheadertext,
                instructions: instructions
            }));
        var header = content;
		
		//set container for our nodes (header, fields, buttons)
        var bodycontent =  Y.Node.create('<div></div>');
        
        //add our header
         bodycontent.append(header);
        
        //add fields
         Y.Array.each(fields, function(field) {  	 
            //loop start
                bodycontent.append(field);
            //loop end
        }, bodycontent);
     
     	//add submit button
     	var submitbuttons = this._getSubmitButtons(snippetindex);
     	bodycontent.append(submitbuttons)

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

  /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getSubmitButtons
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getSubmitButtons: function(snippetindex) {
  
        var template = Y.Handlebars.compile(SUBMITTEMPLATE),
        	
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                inserttext:  M.util.get_string('insert', COMPONENTNAME)
            }));
     
		content.one('.' + CSS.INPUTSUBMIT).on('click', this._doInsert, this, snippetindex);
        return content;
    },


   /**
     * Return a field (yui node) for each variable in the template
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getSnippetFields: function(snippetindex) {
    
        loadStore();

    	var allcontent=[];
    	var thevariablesRaw=this.get('snippetvars')[snippetindex];
    	var thedefaults=this.get('defaults')[snippetindex];
    	
    	//defaults array 
    	var defaultsarray=thedefaults;

        //filter out helpers from handlebars variables
	    var thevariables=filterOutHelpers(thevariablesRaw);
     
        //Variables starting with $ are saved in localStorage
        //Check if they are stored or use defaultValue instead

    	Y.Array.each(thevariables, function(thevariable, currentindex) { 	 
            //loop start
            //a select combo starts
			if((thevariable in defaultsarray) && defaultsarray[thevariable].indexOf('|')>-1){
            
                var content;
                //check if this combo can be rendered as checkbox
                var opts = defaultsarray[thevariable].split('|');
                if(opts.length===2 && opts.indexOf("1")>-1 && opts.indexOf("0")>-1){
                    //A checkbox field starts
                    var template = Y.Handlebars.compile(CHECKBOXTEMPLATE);
                    content = Y.Node.create(template({
                        elementid: this.get('host').get('elementid'),
                        snippetvar: thevariable,
                        defaultvalue: getFromStorage(thevariable, opts[0]),
                        variableindex: currentindex
                    }));
                    //A checkbox ends
                } else {
                    //A combobox starts
                    var containertemplate = Y.Handlebars.compile(SELECTCONTAINERTEMPLATE);
                    content = Y.Node.create(containertemplate({
                        elementid: this.get('host').get('elementid'),
                        variable: thevariable,
                        defaultvalue: getFromStorage(thevariable, defaultsarray[thevariable]),
                        variableindex: currentindex
                    }));
                
                    var selecttemplate = Y.Handlebars.compile(SELECTTEMPLATE);
                    var defaultValue = getFromStorage(thevariable, defaultsarray[thevariable]);
                    var	selectbox = Y.Node.create(selecttemplate({
                        variable: thevariable,
                        defaultvalue: defaultValue,
                        variableindex: currentindex
                    }));
                
                    
                    var htmloptions="";
                    var selectedOpt;
                    var opttemplate = Y.Handlebars.compile(OPTIONTEMPLATE);
                    Y.Array.each(opts, function(opt, optindex) {
                        var optValue = opt;
                        var optLabel = opt;
                        if (opt.indexOf(":")>0) {
                            var parts = opt.split(":");
                            optValue = (parts[0] || "").trim();
                            optLabel = (parts[1] || "").trim();
                        }
                       
                        var optcontent = Y.Node.create(opttemplate({
                                option: optValue,
                                optionLabel: optLabel 
                            }));
                        selectbox.appendChild(optcontent);
                        if (optValue == defaultValue) {
                            selectedOpt = optcontent;
                        }
                    });
                    content.appendChild(selectbox);
                    if(selectedOpt) {
                        selectedOpt.set("selected", "true");
                    }
                 // A select combo ends
                }
                
			} else{
		    // A normal textfield starts
			 	 var defaultvalue = defaultsarray[thevariable];
				 if (defaultvalue === "$RND") {
				    defaultvalue = Math.random().toString(32).substring(2);
				 }
      
				 var template = Y.Handlebars.compile(FIELDTEMPLATE);
				 var content = Y.Node.create(template({
					elementid: this.get('host').get('elementid'),
					snippetvar: thevariable,
					defaultvalue: getFromStorage(thevariable, defaultvalue),
					variableindex: currentindex
                }));
            // A normal textfield ends
            }//end of if | char
            allcontent.push(content);
            //loop end
        }, this);


        return allcontent;
    },


     /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getButtonsForSnippets: function(clickedicon) {
    
    	var allcontent=[];
    	 Y.Array.each(this.get('snippetnames'), function(thesnippetname, currentindex) {
            //loop start
             var template = Y.Handlebars.compile(BUTTONTEMPLATE),
            	content = Y.Node.create(template({
            	elementid: this.get('host').get('elementid'),
                snippetname: thesnippetname,
                snippetindex: currentindex
            }));
            this._form = content;
            content.one('.' + CSS.KEYBUTTON + '_' + currentindex).on('click', this._showSnippetForm, this,currentindex);
            allcontent.push(content);
            //loop end
        }, this);

        return allcontent;
    },

    /**
     * Inserts the users input onto the page
     * @method _getDialogueContent
     * @private
     */
    _doInsert : function(e,snippetindex){
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();
        
        var retcontent = '';
        var retstring = this.get('snippets')[snippetindex];
        var thesnippetname = this.get('snippetnames')[snippetindex];
        var thevariablesRaw=this.get('snippetvars')[snippetindex];

	    //filter out helpers from handlebars variables
	    var thevariables=filterOutHelpers(thevariablesRaw);
 	
        
        //Do the merge (old way)
        /*
         Y.Array.each(thevariables, function(variable, currentindex) {
        	var thefield = Y.one('.' + CSS.TEMPLATEVARIABLE + '_' + currentindex);
        	var thevalue = thefield.get('value');
        	//retstring = retstring.replace('{{' + variable + '}}',thevalue);
             retstring = retstring.replace(new RegExp('{{' + variable + '}}', 'g'),thevalue);
        }, this);
        retcontent = retstring;
        */

        //Do the merge the YUI way        
        var mergevars={};
        Y.Array.each(thevariables, function(variable, currentindex) {
		var thefield = Y.one('.' + CSS.TEMPLATEVARIABLE + '_' + currentindex);
		if (thefield) {
            //possibly 'checked' for checkboxes
            var thevalue2;
            var type = thefield.get('type');
            if(type=='checkbox') {
                thevalue2 = (thefield.get('checked') == 1);
            } else {
                thevalue2 = thefield.get('value');
                thevalue2 = ((thevalue2+"") || "").trim();
                if ( thevalue2.indexOf("[")==0 || thevalue2.lastIndexOf("]")==thevalue2.length-1 ) {
                    try {
                     //Try to convert it into an array
                     thevalue2 = thevalue2.substring(1, thevalue2.length-1).split(";");
                     //Trim it
                     for(var i=0; i < thevalue2.length; i++) {
                         var str = thevalue2[i];
                         if (str.trim) {
                            thevalue2[i] = str.trim();
                         }
                     }
                    } catch(ex){
                     console.log(ex);
                    };	
                 }
            }  
            mergevars[variable] = thevalue2;
            if (variable.substring(0,1)=="$") {
                STORE['valors'][variable] = thevalue2;
            }
		}
        }, this);
    
    // <script> tags are forbidden in retstring since it conflicts with YUI
    retstring = retstring.
                replace(/<script/gi, "<!--<script").
                replace(/<\/script>/gi, "</script>-->");

    console.log("> Template before compilation: ");
    console.log(retstring);
    var template = Y.Handlebars.compile(retstring);
    console.log("> Interpolation variables: ");
    console.log(mergevars);
    var retcontent = template(mergevars);

    // uncomment <script> tags
    retcontent = retcontent.
                replace(/<!--<script/gi, "<script").
                replace(/<\/script>-->/gi, "</script>");

    console.log("> Resulting html content:");
    console.log(retcontent);
    
    /*
	content = Y.Node.create(template(mergevars));
        //fails here because the retcontent is a YUI node and tostring delivers garbage
		//all the data is nested.
		//this only works for text content
        retcontent = content._node.data;
		//this doesn't really work
        var nodelist = content.get('childNodes');
        nodelist.each(function (aNode) {
			retcontent = retcontent + aNode.getHTML();
			});
			*/
	
        this.editor.focus();
        this.get('host').insertContentAtFocusPoint(retcontent);
        this.markUpdated();
        saveStore();
    }
}, { ATTRS: {
    disabled: {
        value: false
    },
    snippets: {
        value: null
    },
    snippetnames: {
        value: null
    },
    snippetvars: {
        value: null
    },
    defaults: {
        value: null
    },
    instructions: {
        value: null
    }
 }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
