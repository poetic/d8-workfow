/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.plugins.add( 'brandname', {
	requires: 'dialog',
	icons: 'brandname', // %REMOVE_LINE_CORE%
	hidpi: true, // %REMOVE_LINE_CORE%
	init: function( editor ) {
		// editor.config.smiley_path = editor.config.smiley_path || ( this.path + 'images/' );
		editor.addCommand( 'brandname', {
        exec: function( editor ) {
          editor.insertHtml('[brand:name]');
        }
    });
		editor.ui.addButton( 'brandname', {
			label: 'Insert brandname',
			command: 'brandname',
			toolbar: 'insert,50'
		} );
		CKEDITOR.dialog.add( 'brandname', this.path + 'dialogs/brandname.js' );
	}
} );


