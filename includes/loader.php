<?php
/**
 * Plugin loading functions.
 *
 * @package WordPress
 * @subpackage AI Writer.
 * @since 1.0
 */

global $aiwriter__;

add_action( 'admin_head', 'aiwriter_handle_admin_notice' );

// admin menu icon style.
add_action( 'admin_head', 'aiwriter_menu_icon_style' );

// Initialize the process. Everything starts from here!
add_action( 'init', 'aiwriter_activation_process_handler' );

// Activate and commence plugin.
register_activation_hook( AIWRITER, 'aiwriter_activation' );

// Register deactivation process.
register_deactivation_hook( AIWRITER, 'aiwriter_deactivation' );


/**
 * EXTRA SUPPORT section for ADMIN CORE SUPOPRT
 *
 * Display what you want to show in the notice
 */
function aiwriter_client_feedback_notice() {
	global $aiwriter__;

	// get current page.
	$page = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	// dynamic extra parameter adding beore adding new url parameters.
	$page .= strpos( $page, '?' ) !== false ? '&' : '?';
	?>
	<div class="notice notice-info is-dismissible">
		<h3><?php echo esc_html( $aiwriter__['plugin']['name'] ); ?></h3>
		<p>
			Excellent! You've been using <strong><a href="<?php echo esc_url( $aiwriter__['plugin']['review_link'] ); ?>"><?php echo esc_html( $aiwriter__['plugin']['name'] ); ?></a></strong> for a while. We'd appreciate if you kindly rate us on <strong><a href="<?php echo esc_url( $aiwriter__['plugin']['review_link'] ); ?>">WordPress.org</a></strong>
		</p>
		<p>
			<a href="<?php echo esc_url( $aiwriter__['plugin']['review_link'] ); ?>" class="button-primary">Rate it</a> <a href="<?php echo esc_url( $page ); ?>aiwriter_rate_us=done&aiwriter_raten=<?php echo esc_attr( wp_create_nonce( 'aiwriter_rateing_nonce' ) ); ?>" class="button">Already Did</a> <a href="<?php echo esc_url( $page ); ?>aiwriter_rate_us=cancel&aiwriter_raten=<?php echo esc_attr( wp_create_nonce( 'aiwriter_rateing_nonce' ) ); ?>" class="button">Cancel</a>
		</p>
	</div>
	<?php
}

/**
 * Calculate date difference and some other accessories
 *
 * @param string $key | option meta key.
 * @param int    $notice_interval | Alarm after this day's difference.
 * @param string $skip_ | skip this value.
 */
function aiwriter_date_diff( $key, $notice_interval, $skip_ = '' ) {
	$value = get_option( $key );

	if ( empty( $value ) || '' === $value ) {

		// if skip value is meta value - return false.
		if ( '' !== $skip_ && $skip_ === $value ) {
			return false;
		} else {

			$c   = date_create( gmdate( 'Y-m-d' ) );
			$d   = date_create( $value );
			$dif = date_diff( $c, $d );
			$b   = (int) $dif->format( '%d' );

			// if days difference meets minimum given interval days - return true.
			if ( $b >= $notice_interval ) {
				return true;
			}
		}
	} else {
		add_option( $key, gmdate( 'Y-m-d' ) );
	}

	return false;
}

/**
 * SUPPORT section of CORE ADMIN
 *
 * Save all admin notices for displaying later
 */
function aiwriter_handle_admin_notice() {
	global $aiwriter__;

	// only apply to admin AIWRITER setting page.
	$screen = get_current_screen();
	if ( ! in_array( $screen->id, $aiwriter__['plugin']['screen'], true ) ) {
		return;
	}

	// Buffer only the notices.
	ob_start();

	do_action( 'admin_notices' );

	$content = ob_get_contents();
	ob_get_clean();

	// Keep the notices in global $aiwriter__.
	array_push( $aiwriter__['notice'], $content );

	// Remove all admin notices as we don't need to display in it's place.
	remove_all_actions( 'admin_notices' );
}

/**
 * Client feedback - rating
 */
function aiwriter_client_feedback() {
	global $aiwriter__;

	if ( isset( $_GET['aiwriter_raten'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['aiwriter_raten'] ) ), 'aiwriter_rating_nonce' ) ) {

		if ( isset( $_GET['aiwriter_rate_us'] ) ) {
			$task = sanitize_key( wp_unslash( $_GET['aiwriter_rate_us'] ) );

			if ( 'done' === $task ) {
				// never show this notice again.
				update_option( 'aiwriter_rate_us', 'done' );
			} elseif ( 'cancel' === $task ) {
				// show this notice in a week again.
				update_option( 'aiwriter_rate_us', gmdate( 'Y-m-d' ) );
			}
		}
	} else {
		if ( aiwriter_date_diff( 'aiwriter_rate_us', $aiwriter__['plugin']['notice_interval'], 'done' ) ) {
			// show notice to rate us after 15 days interval.
			add_action( 'admin_notices', 'aiwriter_client_feedback_notice' );

		}
	}
}

/**
 * Check conditions before actiavation of the plugin
 */
function aiwriter_pre_activation() {
	aiwriter_client_feedback();
	return true;
}

/**
 * Add Settings to WooCommerce > Settings > Products > WC Multiple Cart
 *
 * @param array $links plugin extra links.
 */
function aiwriter_add_extra_plugin_links( $links ) {
	global $aiwriter__;

	$action_links = array();

	$action_links['settings'] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=aiwriter-settings' ) ), 'Settings' );

	return array_merge( $action_links, $links );
}

/**
 * Add plugin description meta
 *
 * @param array  $links | plugin description links.
 * @param string $file | plugin base name.
 */
function aiwriter_plugin_desc_meta( $links, $file ) {

	// if it's not aiwriter plugin, return.
	if ( plugin_basename( AIWRITER ) !== $file ) {
		return $links;
	}

	global $aiwriter__;
	$row_meta = array();

	$row_meta['docs']    = sprintf( '<a href="%s">Docs</a>', esc_url( $aiwriter__['plugin']['docs'] ) );
	$row_meta['apidocs'] = sprintf( '<a href="%s">Support</a>', esc_url( $aiwriter__['plugin']['request_quote'] ) );

	return array_merge( $links, $row_meta );
}

/**
 * Frontend script and style enqueuing
 */
function aiwriter_load_scripts() {
	global $aiwriter__;

	// enqueue style.
	wp_enqueue_style( 'aiwriter-frontend', plugin_dir_url( AIWRITER ) . 'assets/frontend.css', array(), $aiwriter__['plugin']['version'], 'all' );

	// register script.
	wp_register_script( 'aiwriter-frontend', plugin_dir_url( AIWRITER ) . 'assets/frontend.js', array( 'jquery' ), $aiwriter__['plugin']['version'], true );

	wp_enqueue_script( 'aiwriter-frontend', plugin_dir_url( AIWRITER ) . 'assets/frontend.js', array( 'jquery' ), $aiwriter__['plugin']['version'], false );

	// handle localized variables.
	$redirect_url = get_option( 'wmc_redirect' );
	if ( '' === $redirect_url ) {
		$redirect_url = 'cart';
	}

	// add localized variables.
	$localaized_values = array(
		'ajaxurl'        => admin_url( 'admin-ajax.php' ),
		'nonce'          => wp_create_nonce( 'ajax-nonce' )
	);
	
	// apply filter.
	$localaized_values = apply_filters( 'aiwriter_front_local_vars', $localaized_values );

	// localize script.
	wp_localize_script( 'aiwriter-frontend', 'aiwriter', $localaized_values );
}

/**
 * Register and enqueue a custom stylesheet in the WordPress admin.
 */
function aiwriter_admin_enqueue_scripts() {
	global $aiwriter__;

	$screen = get_current_screen();
	if ( ! in_array( $screen->id, $aiwriter__['plugin']['screen'], true ) ) {
		return;
	}

	// enqueue style.
	wp_register_style( 'aiwriter_admin_style', plugin_dir_url( AIWRITER ) . 'assets/admin/admin.css', false, $aiwriter__['plugin']['version'] );
	wp_enqueue_style( 'aiwriter_admin_style' );

	// colorpicker style.
	wp_enqueue_style( 'wp-color-picker' );

	// colorpicker script.
	wp_enqueue_script( 'wp-color-picker' );

	wp_register_script( 'aiwriter_admin_script', plugin_dir_url( AIWRITER ) . 'assets/admin/admin.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-sortable' ), $aiwriter__['plugin']['version'], true );
	wp_enqueue_script( 'aiwriter_admin_script' );

	$var = array(
		'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'ajax-nonce' )
	);

	if ( empty( $var['image_size'] ) || '' === $var['image_size'] || 'NaN' === $var['image_size'] ) {
		$var['image_size'] = 55;
	}

	// apply hook for editing localized variables in admin script.
	$var = apply_filters( 'aiwriter_local_var', $var );
	wp_localize_script( 'aiwriter_admin_script', 'aiwriter', $var );
}

/**
 * Add menu and submenu pages
 */
function aiwriter_add_admin_menu() {
	global $aiwriter__;

	// Main menu.
	add_menu_page(
		'AI Writer',
		'AI Writer',
		'manage_options',
		'ai-writer',
		'aiwriter_content_generator',
		plugin_dir_url( AIWRITER ) . 'assets/images/admin-icon.svg',
		56
	);

	// main menu label change.
	add_submenu_page(
		'ai-writer',
		'AI Writer Content Generator',
		'Generate Content',
		'manage_options',
		'ai-writer'
	);

	add_submenu_page(
		'ai-writer',
		'AI Writer Settings',
		'Settings',
		'manage_options',
		'aiwriter-settings',
		'aiwriter_settings_page'
	);
}

/**
 * Save aiwriter admin settings
 *
 * @since 6.2
 */
function aiwriter_save_settings() {
	if ( ! isset( $_POST['settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['settings_nonce'] ), 'update_settings' ) ) {
		return;
	}
	
	if( ! isset( $_POST['api_key'] ) ){
		return;
	}

	global $aiwriter__;

	$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );

	if ( ! empty( $api_key ) ) {
		update_option( 'ai_writer_api_key', $api_key );
	}

	array_push( $aiwriter__['notice'], '<div id="message" class="updated inline"><p><strong>Your settings have been saved</strong></p></div>' );
}

/**
 * Render sidebar content of admin settings page.
 *
 * @param string $default_path sidebar default path.
 */
function aiwriter_sidebar_( $default_path ) {
	include $default_path;
}

/**
 * Assign default values to admin fields if it's empty
 * Only works wither at plugin activation or update
 *
 * @param array $fields add default data to fields.
 */
function aiwriter_populate_fields( $fields ) {

	foreach ( $fields as $key => $def ) {

		if ( get_option( $key ) ) {
			update_option( $key, $def );
		} else {
			add_option( $key, $def );
		}
	}
}


/**
 * CORE ADMIN section
 *
 * Change admin menu icon.
 */
function aiwriter_menu_icon_style() {
	global $aiwriter__;
	?>
	<style>
		#toplevel_page_ai-writer img {
			width: 20px;
			opacity:1!important;
		}
		.notice h3{
			margin-top:.5em;
			margin-bottom:0;
		}
	</style>
	<?php
}

/**
 * Start the plugin
 */
function aiwriter_activation_process_handler() {

	// check prerequisits.
	if ( ! aiwriter_pre_activation() ) {
		return;
	}

	// add extra links right under plug.
	add_filter( 'plugin_action_links_' . plugin_basename( AIWRITER ), 'aiwriter_add_extra_plugin_links' );
	add_filter( 'plugin_row_meta', 'aiwriter_plugin_desc_meta', 10, 2 );

	// needs to be off the hook in the next version.
	// include AIWRITER_PATH . 'includes/functions.php';

	// Enqueue frontend scripts and styles.
	// add_action( 'wp_enqueue_scripts', 'aiwriter_load_scripts' );

	// Enqueue admin script and style.
	add_action( 'admin_enqueue_scripts', 'aiwriter_admin_enqueue_scripts' );

	// Add admin menu page.
	add_action( 'admin_menu', 'aiwriter_add_admin_menu' );

	// Save admin settings.
	aiwriter_save_settings();

	add_action( 'aiwriter_sidebar', 'aiwriter_sidebar_', 10, 1 );
}

/**
 * Things to do for activating the plugin.
 */
function aiwriter_activation() {
	// main plugin activatio process handler.
	aiwriter_activation_process_handler();

	flush_rewrite_rules();
}

/**
 * Plugin deactivation handler
 */
function aiwriter_deactivation() {
	flush_rewrite_rules();
}
