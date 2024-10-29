<?php
/**
 * Plugin Name: AI Writer
 * Description: A simple plugin that generates content using the OpenAI API.
 * Version: 1.1.3
 * Author: WebFix Lab
 * Author URI: https://webfixlab.com
 * License: GPL2
 * Text Domain: ai-writer
 *
 * @package WordPress
 * @subpackage AI Writer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

// plugin path
define( 'AIWRITER', __FILE__ );
define( 'AIWRITER_PATH', plugin_dir_path( AIWRITER ) );

include( AIWRITER_PATH . 'includes/core-data.php' );
include( AIWRITER_PATH . 'includes/admin-functions.php' );

include( AIWRITER_PATH . 'includes/loader.php' );
