YUI.add('moodle-atto_proofreadertools-button', function (Y, NAME) {

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
 * @package    atto_proofreadertools
 * @copyright  2015 Institut Obert de Catalunya
 * @author     Marc Català <mcatala@itteria.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_proofreadertools-button
 */

/**
 * Atto text editor proofreadertools plugin.
 *
 * @namespace M.atto_proofreadertools
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

Y.namespace('M.atto_proofreadertools').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    initializer: function() {
        if (!this.get('enablebutton')) {
            return;
        }
        this.addButton({
            icon: 'correctoricon',
            iconComponent: 'atto_proofreadertools',
            title: 'correctoricon',
            buttonName: 'correctoricon',
            callback: this._correctorIcon
        });

        this.addButton({
            icon: 'blueicon',
            iconComponent: 'atto_proofreadertools',
            title: 'blueicon',
            buttonName: 'blueicon',
            callback: this._blueIcon
        });

        this.addButton({
            icon: 'redicon',
            iconComponent: 'atto_proofreadertools',
            title: 'redicon',
            buttonName: 'redicon',
            callback: this._redIcon
        });

        this.addButton({
            icon: 'doubleslashicon',
            iconComponent: 'atto_proofreadertools',
            title: 'doubleslashicon',
            buttonName: 'doubleslashicon',
            callback: this._doubleslashIcon
        });
    },

    _correctorIcon: function(e) {
        var host = this.get('host');

        e.preventDefault();

        var currentSelection = host.getSelection();

        // Build the tag.
        var html = '<span class="blauioc">[<span>' + currentSelection +'</span>]</span>';

        // Position caret.
        var node = host.insertContentAtFocusPoint(html);
        var domNode = node.getDOMNode().childNodes[1];
        var range = window.rangy.createRange();
        var selection = window.rangy.getSelection();

        range.setStartBefore(domNode);
        range.setEnd(domNode, domNode.childNodes.length ? 1 : 0 );
        selection.removeAllRanges();
        selection.addRange(range);

        this.markUpdated();
    },

    _blueIcon: function() {
        this.get('host').toggleInlineSelectionClass(['blauioc']);
    },

    _redIcon: function() {
        this.get('host').toggleInlineSelectionClass(['vermellioc']);
    },

    _doubleslashIcon: function(e) {
        var host = this.get('host');

        e.preventDefault();
        var currentSelection = this.get('host').getSelection();
        // Focus on the previous selection.
        host.setSelection(currentSelection);

        // And add the characters.
        host.insertContentAtFocusPoint('[//]');

        this.markUpdated();
    }
}, {
    ATTRS: {
        /**
         * Whether the button should be displayed
         *
         * @attribute enablebutton
         * @type Boolean
         */
        enablebutton: {
            value: false
        }
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
