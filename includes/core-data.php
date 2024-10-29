<?php
/**
 * Plugin Data Structure
 *
 * @package WordPress.
 * @subpackage AI Writer.
 * @since 1.0
 */

global $aiwriter__;

$aiwriter__ = array(
	'activate_link'   => 'admin.php?page=aiwriter-shortcode-pricing',
	'plugin'          => array(
		'name'			  => 'AI Writer',
		'version'         => '1.1.3',
		'page_limit'      => 10,
		'screen'          => array(
			'toplevel_page_ai-writer',
			'ai-writer_page_aiwriter-settings'
		),
		'review_link'     => 'https://wordpress.org/support/plugin/ai-writer/reviews/?rate=5#new-post',
		'support'         => 'https://webfixlab.com/contact/',
		'request_quote'   => 'https://webfixlab.com/contact/',
		'docs'            => 'https://webfixlab.com/plugins/ai-writer',
		'woo_url'         => 'https://wordpress.org/plugins/woocommerce/',
		'free_aiwriter_url'    => 'https://wordpress.org/plugins/ai-writer/',
		'shortcode_doc'   => 'https://webfixlab.com/plugins/ai-writer',
		'notice_interval' => 15,
	),
	'notice'          => array(),
	'fields_list'     => array()
);

// menu items.
$aiwriter__['menu'] = array(
	'ai-writer'   => array(
		'New Content',
		'Settings',
	),
	'settings' => array(
		'New Content',
		'Settings',
	),
);

// hook to modify global $aiwriter__ data variable.
do_action( 'aiwriter_modify_core_data' );
