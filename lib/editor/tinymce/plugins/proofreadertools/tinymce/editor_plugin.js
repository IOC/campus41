// Local mail plugin for Moodle// Copyright © 2012,2013 Institut Obert de Catalunya
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Ths program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

/**
 * TinyMCE Proofreader tools plugin version details.
 *
 * @package   tinymce_prooferadertools
 * @copyright 2013 Institut Obert de Catalunyay
 * @license   http://www.gun.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function() {
    tinymce.create('tinymce.plugins.Proofreadertools', {

        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.contentCSS.push(url + '/proofreadertools.css');

            ed.addCommand('mceCorrectorIcon', function() {
                var n, p;

                if (!ed.selection.getContent()) {

                    ed.selection.setContent('<span class = "blauioc">[');
                    var start = ed.selection.getBookmark();
                    ed.selection.setContent(']</span>');
                    ed.selection.setContent('&nbsp;');

                    ed.selection.moveToBookmark(start);


                } else {
                    var start = ed.selection.getBookmark();
                    var select = ed.selection.getContent();

                    ed.selection.setContent( '<span class = "blauioc">[' + select +']</span>');

                }
            });

            ed.addCommand('mceBlueIcon', function() {
                ed.execCommand("foreColor",false,"#0000ff");
            });

            ed.addCommand('mceRedIcon', function() {
                ed.execCommand("foreColor",false,"#ff0000");
            });

            ed.addCommand('mceDoubleSlash', function() {
                ed.selection.setContent('[//]');
            });

            // Register icons and associated commands (cme's).
            ed.addButton('correctoricon', {
                title : 'proofreadertools.correctoricon',
                cmd : 'mceCorrectorIcon',
                image : url + '/img/correctoricon.gif'
            });
            ed.addButton('blueicon', {
                title : 'proofreadertools.blueicon',
                cmd : 'mceBlueIcon',
                image : url + '/img/blueicon.gif'
            });
            ed.addButton('redicon', {
                title : 'proofreadertools.redicon',
                cmd : 'mceRedIcon',
                image : url + '/img/redicon.gif'
            });
            ed.addButton('doubleslashicon', {
                title : 'proofreadertools.doubleslashicon',
                cmd : 'mceDoubleSlash',
                image : url + '/img/doubleslashicon.gif'
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'IOC Proofreader tools',
                author : 'IOC',
                authorurl : 'http://ioc.xtec.cat',
                infourl : 'http://moodle.org',
                version : "1.0"
            };
        }
    });

    // Register plugin.
    tinymce.PluginManager.add('proofreadertools', tinymce.plugins.Proofreadertools);
})();
