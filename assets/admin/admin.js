/**
 * Admin JavaScript
 *
 * @package Wordpress
 * @subpackage AI Writer
 * @since 1.0
 */

(function($){
	// sticky header/menu.
	$( window ).on(
		'scroll',
		function(){
			if ( $( window ).scrollTop() > 60 ) {
				$( '.aiwriter-admin-wrap' ).addClass( 'aiwriter-stick-settings' );
			} else {
				if ( $( '.aiwriter-admin-wrap' ).hasClass( 'aiwriter-stick-settings' ) ) {
					$( '.aiwriter-admin-wrap' ).removeClass( 'aiwriter-stick-settings' );
				}
			}
		}
	);
	
	function loading( btn, parent, text, dothis ){
		if( dothis == 'on' ){
			btn.prop( 'disabled', true );

			// start loading animation.
			parent.append( '<div class="aiwriter-loading">'+ text +' <span>.</span></div>' );
			setInterval(() => {
				var crap = parent.find( '.aiwriter-loading span' );
				if( crap.text().length == 5 ){
					crap.text( '' );
				}
				crap.append( '.' );
			}, 350);
		}else{
			btn.prop( 'disabled', false );
			$( 'body' ).find( '.aiwriter-loading' ).remove();
		}
	}

	var numIterations = 2;
	var currentIteration = 0;

	function request_content(){
		var topic		= $( 'textarea[name="title"]' ).val();
		var subtopic    = '';
		var heading_no	= $( 'input[name="heading_no"]' ).val();

		var title = '';
		var starter = false;

		if( currentIteration == 0 ){
			// get title + headings only (excerpt will be available in future version)
			title = 'Based on ' + topic + ' topic, write a SEO optimized title inside a H1 tag and '+ heading_no +' headings in h2 tag without any numbering';
			starter = true;
		}else{
			var h = $( 'textarea[name="title"]' ).data( 'headings' );
			subtopic = h[ currentIteration - 1 ];
			var headings = h.join( ', ' );

			var ai_title = $( 'body' ).find( 'input[name="post_title"]' ).val();
			var content = tinyMCE.get( 'post_content_ifr' ).getContent({ format: 'text' });

			if( currentIteration == 1 ){
				title = (
				    'Main Title: "'+ ai_title +'"\n' +
				    'Sub-headings: "' + headings + '"\n\n' +
				    'Write content for the sub-heading "'+ subtopic +'":'
				);
			}else{
				title = (
				    'Context: "' + content + '"\n' +
				    'Main Title: "'+ ai_title +'"\n' +
				    'Sub-headings: "' + headings + '"\n\n' +
				    'Write content for the sub-heading "'+ subtopic +'":'
				);
			}
		}

		$.ajax({
			method: "POST",
			url: aiwriter.ajaxurl,
			data: {
				'action'			: 'aiwriter_get_content',
				'title'				: title,
				'starter'			: starter,
				'aiwriter_nonce'	: aiwriter.nonce
			},
			async: 'false',
			dataType: 'html',
			success: function( r ){
				var rp = JSON.parse( r );
				if( rp.error == false ){
					$( 'form[name="aiwriter-post"]' ).show();
					$( '.aiwriter-post input' ).prop( 'disabled', false );

					if( currentIteration == 0 ){
						$( 'textarea[name="title"]' ).data( 'headings', rp.headings );
						$( 'input[name="post_title"]' ).val( rp.title );
					}else{
						tinyMCE.get( 'post_content_ifr' ).setContent( tinyMCE.get( 'post_content_ifr' ).getContent() + '<h2>' + subtopic + '</h2>' + rp.content );
					}

					currentIteration++;
					if( currentIteration < numIterations ){
						request_content();
					}else{
						loading( $( '.aiwriter-write input' ), '', '', 'off' );
						$( 'html, body' ).animate({
							scrollTop: $( 'form[name="aiwriter-post"]' ).offset().top - 95
						}, 1000);
					}
				}else{
					loading( $( '.aiwriter-write input' ), '', '', 'off' );
					$( '.aiwriter-post input' ).prop( 'disabled', true );
					$( '.aiwriter-write' ).append( '<h3 class="aiwriter-issue">' + rp.msg + '</h3>' );
				}
			},
			error: function(errorThrown){
				console.log( errorThrown );
			}
		});
	}

	function newpost(){
		var post_title 		= $( 'input[name="post_title"]' ).val();
		var post_content	= tinyMCE.get( 'post_content_ifr' ).getContent();
		var post_type		= $( 'select[name="post_type"]' ).val();
		var post_status		= $( 'select[name="post_status"]' ).val();

		$.ajax({
			method: "POST",
			url: aiwriter.ajaxurl,
			data: {
				'action'			: 'aiwriter_new_post',
				'post_title'		: post_title,
				'post_content'		: post_content,
				'post_type'			: post_type,
				'post_status'		: post_status,
				'aiwriter_nonce'	: aiwriter.nonce
			},
			async: 		'false',
			dataType:	'html',
			success: function( r ){
				loading( $( '.aiwriter-post input' ), '', '', 'off' );
				$( 'body' ).find( '.aiwriter-url' ).remove();
				
				var rp = JSON.parse( r );
				
				if( rp.error == false ){
					$( '.aiwriter-post' ).append( '<div class="aiwriter-url"><h3><a href="'+ rp.edit +'" rel="bookmark" aria-label="View “a”" target="_blank">' + post_title  + '</a> <small> - has been created.</small></h3><div class=""><span class="edit"><a href="'+ rp.edit +'" aria-label="Edit “a”" target="_blank">Edit</a> | </span><span class="trash"><a href="'+ rp.trash +'" class="submitdelete" aria-label="Move “a” to the Delete">Delete</a> | </span><span class="view"><a href="'+ rp.view +'" rel="bookmark" aria-label="View “a”" target="_blank">View</a></span></div></div>' );
				}
			},
			error: function(errorThrown){
				console.log( errorThrown );
			}
		});
	}

	$( document ).ready(
		function(){
			// color picker.
			$( '.aiwriter-colorpicker' ).wpColorPicker();

			$( 'form[name="aiwriter-request"] input[type="submit"]' ).on( 'click', function(e){
				e.preventDefault();

				numIterations = 2;
				currentIteration = 0;

				var v = $( 'textarea[name="title"]' ).val();

				if( v.length > 0 ){
					$( 'body' ).find( '.aiwriter-issue' ).remove();

					$( 'body' ).find( 'input[name="post_title"]' ).val( '' );
					tinyMCE.get( 'post_content_ifr' ).setContent( '' );
					
					numIterations	= parseInt( $( 'input[name="heading_no"]' ).val() ) + 1;
					loading( $( '.aiwriter-write input' ), $( '.aiwriter-write' ), 'Generating', 'on' );
					loading( $( '.aiwriter-ploading span' ), $( '.aiwriter-ploading' ), 'Please wait your content is generating', 'on' );
					request_content();
				}else{
					$( '.aiwriter-write' ).append( '<h3 class="aiwriter-issue">Please add some text in the Prompt box above and try again.</h3>' );
				}
			});
			
			$( 'form[name="aiwriter-post"]' ).on( 'submit', function( e ){
				e.preventDefault();
				loading( $( '.aiwriter-post input' ), $( '.aiwriter-post' ), 'Creating', 'on' );
				newpost();
			});
		}
	);
})( jQuery );
