/**
 * Frontend JavaScript
 *
 * @package Wordpress
 * @subpackage AI Writer
 * @since 1.0
 */

(function($) {
	// on ESC key pressed, hide popup box.
	$( document ).on(
		'keyup',
		function( e ) {
			if ( e.keyCode == 27 ) {
				// $( '#aiwriterpop' ).hide();
			}
		}
	);
})( jQuery );
