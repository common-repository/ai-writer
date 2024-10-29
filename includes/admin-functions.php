<?php
/**
 * Frontend data structure
 *
 * @package WordPress
 * @subpackage AI Writer.
 * @since 1.0
 */

/**
 * Display pre-saved admin notices
 */
function aiwriter_settings_display_saved_notices() {
	global $aiwriter__;

	// Display notices.
	foreach ( $aiwriter__['notice'] as $notice ) {
		echo wp_kses_post( $notice );
	}
}

/**
 * Admin settings template functions.
 *
 * Display settings title
 */
function aiwriter_settings_display_title() {
	echo sprintf( '<h1 class="">%s</h1>', esc_html( get_admin_page_title() ) );
}

/**
 * Display admin settings header description contnet.
 */
function aiwriter_settings_header_desc() {
	global $aiwriter__;

	// change support url based on state of pro plugin availability.
	$support_url = $aiwriter__['plugin']['request_quote'];

	echo sprintf(
		'<a href="%s" target="_blank">%s</a> | <a href="%s" target="_blank">%s</a>',
		esc_url( $aiwriter__['plugin']['docs'] ),
		'DOCUMENTATION',
		esc_url( $support_url ),
		'SUPPORT'
	);
}

/**
 * For displaying icon
 *
 * @param string $tab_name menu name.
 */
function aiwriter_settings_get_menu_icon( $tab_name ) {
	$s = '';

	$menu = sanitize_title( $tab_name );

	if ( 'settings' === $menu ) {
		$s = 'admin-settings';
	} elseif ( 'new-content' === $menu ) {
		$s = 'welcome-write-blog';
	}

	if ( ! empty( $s ) ) {
		echo sprintf( '<span class="dashicons dashicons-%s"></span> ', esc_attr( $s ) );
	}

	echo esc_html( $tab_name );
}

/**
 * Get menu url.
 *
 * @param string $menu tab name.
 */
function aiwriter_settings_menu_url( $menu ) {
	global $aiwriter__;

	$url = '';

	foreach ( $aiwriter__['menu'] as $page => $tabs ) {
		if ( ! in_array( $menu, $tabs, true ) ) {
			continue;
		}

		if( 'New Content' === $menu ){
			$url = admin_url( 'admin.php?page=ai-writer' );
		}else{
			$url = admin_url( 'admin.php?page=aiwriter-' . $page . '&tab=' . sanitize_title( $menu ) . '&nonce=' . wp_create_nonce( 'aiwriter_tab_nonce' ) );
		}

	}

	return $url;
}

/**
 * Display navigation items
 */
function aiwriter_settings_menu() {
	global $aiwriter__;

	$section = $aiwriter__['settings_section'];
	$tab     = $aiwriter__['settings_tab'];

	// url nonce.
	$nonce = wp_create_nonce( 'aiwriter_tab_nonce' );

	foreach ( $aiwriter__['menu'][ $section ] as $menu ) {
		// get static url for the menu.
		$url = '';

		$classes = array();

		$url = aiwriter_settings_menu_url( $menu );

		// check if this menu is active?
		if ( sanitize_title( $menu ) === $tab ) {
			$classes[] = 'nav-tab-active';
		}
		?>
		<a class="nav-tab <?php echo esc_html( implode( ' ', $classes ) ); ?>" data-target="general" href="<?php echo esc_url( $url ); ?>">
			<?php aiwriter_settings_get_menu_icon( $menu ); ?>
		</a>
		<?php
	}
}

/**
 * Display tab-wise content in admin settings page.
 */
function aiwriter_settings_display_section() {
	global $aiwriter__;

	$tab = $aiwriter__['settings_tab'];

    $path = AIWRITER_PATH . 'templates/admin/template-parts/' . $tab . '.php';
    if ( file_exists( $path ) ) {
        include $path;
    }

	// for adding extra admin settings.
	do_action( 'aiwritera_extra_section' );
}

/**
 * Which settings to load
 *
 * @param string $section | settings section.
 * @param string $tab | subsection of the given $section.
 */
function aiwriter_load_settings_template( $section, $tab ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// show error/update messages.
	settings_errors( 'wporg_messages' );

	// if GET parameter given as custom tab, use that.
	if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'aiwriter_tab_nonce' ) ) {
		// if aiwriter_tab exists | nav-tab-active.
		if ( isset( $_GET['tab'] ) && ! empty( sanitize_key( wp_unslash( $_GET['tab'] ) ) ) ) {
			$tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
		}
	}

	global $aiwriter__;

	// set current settings section and tab.
	$aiwriter__['settings_section'] = $section;
	$aiwriter__['settings_tab']     = $tab;

	include AIWRITER_PATH . 'templates/admin/settings.php';
}

/**
 * Display settings saved notice.
 */
function aiwriter_settings_saved_notice() {
	if ( isset( $_POST['aiwriter_settings_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['aiwriter_settings_nonce'] ), 'aiwriter_settings' ) ) {
		/**
		 * Check if the user have submitted the settings
		 * WordPress will add the "settings-updated" $_GET parameter to the url
		 */
		if ( isset( $_GET['settings-updated'] ) && ! empty( sanitize_key( wp_unslash( $_GET['settings-updated'] ) ) ) ) {
			// add settings saved message with the class of "updated".
			add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'wporg' ), 'updated' );
		}
	}
}

/**
 * Top level menu callback function
 */
function aiwriter_content_generator() {
	aiwriter_settings_saved_notice();
	aiwriter_load_settings_template( 'ai-writer', 'new-content' );
}

/**
 * Ai writer admin settings page
 */
function aiwriter_settings_page() {
	aiwriter_load_settings_template( 'settings', 'settings' );
}

/**
 * OpenAI API Key
 */
// delete_option( 'ai_writer_api_key' ); // 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
function aiwriter_api_key(){
	// Retrieve the API key from the options table.
	$api_key = get_option( 'ai_writer_api_key' );
	return $api_key;
}

/**
 * Generate content based on given title and OpenAI API key
 *
 * @param string $api_key OpenAI API key.
 * @param string $title given title on which content will be generated later.
 *
 * @return string $generated_content html string content.
 */
function aiwriter_ai_content( $api_key, $title ) {

	$url = 'https://api.openai.com/v1/engines/text-davinci-003/completions';

	$data = array(
		'prompt'      => $title,
		'max_tokens'  => 500, // 1000 token = 750 words, limit : (send 4000) (receive 2000)
		'n'           => 1,
		'stop'        => null,
		'temperature' => 0.7,
	);

	$headers = array(
		'Content-Type'  => 'application/json',
		'Authorization' => 'Bearer ' . $api_key,
	);

	$response = wp_remote_post(
		$url,
		array(
			'timeout'     => 90, // how long connection should stay open - in seconds.
			'method'      => 'POST',
			'headers'     => $headers,
			'body'        => wp_json_encode( $data ),
			'data_format' => 'body',
		)
	);

	if ( is_wp_error( $response ) ) {
		$error_msg = $response->get_error_message();
		return 'Error: ' . wp_kses_post( $error_msg );
	}

	$response_data = json_decode( wp_remote_retrieve_body( $response ), true );

	$choices           = $response_data['choices'];
	$content = $choices[0]['text'];

	return $content;
}

/**
 * Ajax AI Content Generator
 */
function aiwriter_get_content() {
	if ( ! check_ajax_referer( 'ajax-nonce', 'aiwriter_nonce', false ) ) {
		wp_send_json_error(
			array(
				'error' => true,
				'msg'    => 'Invalid security token sent.',
			)
		);
	}

	$api_key = aiwriter_api_key();
	if( empty( $api_key ) ){
		wp_send_json_error(
			array(
				'error' => true,
				'msg'    => 'Please set your OpenAI API key in the AI Writer settings page.',
			)
		);
	}

	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

	$starter = isset( $_POST['starter'] ) ? sanitize_text_field( wp_unslash( $_POST['starter'] ) ) : '';

	$content = aiwriter_ai_content( $api_key, $title );

	$r = array(
		'error'   => false,
		'req'     => $_POST
	);

	if( empty( $content ) ){
		wp_send_json(
			array(
				'error' => true,
				'msg'    => 'Something is wrong. <a href="' . esc_url( admin_url( 'admin.php?page=aiwriter-settings' ) ) . '">Please double check your API key</a>.',
			)
		);
	}

	// Extract title and content from given content.
	if( 'true' === $starter ){
		$s = strpos( $content, '</h1>' ) + 5;
		$r['title'] = strip_tags( substr( $content, 0, $s ) );

		$content = substr( $content, $s );

		preg_match_all('#<h2.*?>.*?</h2>#', $content, $h2s);
		$headings = array();
		foreach( $h2s[0] as $i => $h ){
			$headings[] = strip_tags( $h );
		}
		$r['headings'] = $headings;
	}else{
		$r['content'] = $content;
	}

	wp_send_json( $r );
}
add_action( 'wp_ajax_aiwriter_get_content', 'aiwriter_get_content' );

/**
 * Create a new post or page
 */
function aiwriter_new_post() {
	if ( ! check_ajax_referer( 'ajax-nonce', 'aiwriter_nonce', false ) ) {
		wp_send_json_error(
			array(
				'error'		=> true,
				'html'		=> 'Invalid security token sent.',
			)
		);
	}

	$title			= isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
	$post_content	= isset( $_POST['post_content'] ) ? wp_kses_post( $_POST['post_content'] ) : '';
	$post_type		= isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
	$post_status	= isset( $_POST['post_status'] ) ? sanitize_key( $_POST['post_status'] ) : '';

	// Create a new post or page.
	$post_data = array(
		'post_title'   => $title,
		'post_content' => $post_content,
		'post_status'  => $post_status,
		'post_author'  => get_current_user_id(),
		'post_type'    => $post_type,
	);

	$post_id = 0;
	$r = array(
		'error' => false
	);

	if ( ! empty( $title ) && ! empty( $post_content ) ) {
		$post_id = wp_insert_post( $post_data );
	}

	if( 0 !== $post_id ){
		$r['post_id'] = $post_id;
		$r['view'] = get_permalink( $post_id );
		$r['edit'] = get_edit_post_link( $post_id );
		$r['trash'] = get_delete_post_link( $post_id );
	}else{
		$r['error'] = true;
		$r['msg'] = 'Error creating post';
	}

	wp_send_json( $r );
}
add_action( 'wp_ajax_aiwriter_new_post', 'aiwriter_new_post' );

/**
 * New content submit button
 */
function aiwriter_content_submit_button(){
	// check if api key saved.
	$api_key = aiwriter_api_key();

	$url = admin_url( 'admin.php?page=aiwriter-settings&tab=settings&nonce=' . wp_create_nonce( 'aiwriter_tab_nonce' ) );

	?>
	<p class="submit aiwriter-write">
		<?php if( empty( $api_key ) ) : ?>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Generate Content" disabled>
			<p><a href="<?php echo esc_url( $url ); ?>">Please add OpenAI API key first</a></p>
		<?php else: ?>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Generate Content">
		<?php endif; ?>
	</p>
	<?php
}

/**
 * AI Writer API key settings input field
 */
function aiwriter_api_field(){
	$api_key = aiwriter_api_key();
	?>
	<input name="api_key" type="text" id="api_key" value="<?php echo esc_html( $api_key ); ?>" class="regular-text" required>
	<p><a href="https://platform.openai.com/account/api-keys"><?php echo empty( $api_key ) ? 'Get' : ''; ?> OpenAI API Key</a></p>
	<?php
}