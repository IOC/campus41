YUI.add("moodle-mod_oublog-tagselector",function(s,t){M.mod_oublog=M.mod_oublog||{},M.mod_oublog.tagselector={init:function(t,e){var r,o,a=s.one("form #"+t);if(e&&"object"==typeof e){for(o in r=[],e)r.push(e[o]);e=r}a&&(a.plug(s.Plugin.AutoComplete,{minQueryLength:0,queryDelay:100,queryDelimiter:",",allowTrailingDelimiter:!0,source:e,width:"auto",scrollIntoView:!0,circular:!1,resultTextLocator:"tag",resultHighlighter:"startsWith",resultFilters:["startsWith",function(t,e){var r="",o=a.get("value").lastIndexOf(",");return 0<o&&(r=a.get("value").substring(0,o).split(/\s*,\s*/)),r=s.Array.hash(r),s.Array.filter(e,function(t){return!r.hasOwnProperty(t.text)}).sort(function(t,e){return t.raw.tag<e.raw.tag?-1:t.raw.tag>e.raw.tag?1:0})}],resultFormatter:function(t,e){return s.Array.map(e,function(t){var e='<div class="tagselector_result"><span class="tagselector_result_title">'+t.highlighted+"</span>";return t.raw.label&&(e+=' <span class="tagselector_result_info tagselector_result_info_label">'+t.raw.label+"</span>"),e+=' <span class="tagselector_result_info">'+M.util.get_string("numposts","oublog",t.raw.count)+"</span></div>"})}}),a.on("focus",function(){a.ac.sendRequest("")}),a.ac.after("select",function(t){s.UA.chrome&&window.scrollBy(0,parseInt(a.getStyle("height"))+200),setTimeout(function(){a.ac.sendRequest(""),a.ac.show()},1)}))}}},"@VERSION@",{requires:["base","node","autocomplete","autocomplete-filters","autocomplete-highlighters"]});