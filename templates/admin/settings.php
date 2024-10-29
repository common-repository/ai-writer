<?php
/**
 * Admin settings templte.
 *
 * @package WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since 1.0
 */

?>
<div class="aiwriter-admin-wrap aiwriter-settings">
	<div class="aiwriter-heading">
		<?php aiwriter_settings_display_title(); ?>
		<div class="aiwriter-description">
			<p><?php aiwriter_settings_header_desc(); ?></p>
		</div>
	</div>
	<div class="aiwriter-notice">
		<?php aiwriter_settings_display_saved_notices(); ?>
	</div>
	<div class="aiwriter-settings">
		<div class="aiwriter-col-12">
			<div class="aiwriter-col-9" id="aiwriter-main">
				<div class="aiwriter-row">
					<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
						<?php aiwriter_settings_menu(); ?>
					</nav>
				</div>
				<div class="aiwriter-row sections">
					<?php aiwriter_settings_display_section(); ?>
				</div>
			</div>
			<div class="aiwriter-col-3 aiwriter-sidebar-wrap">
				<div class="site-intro">
					<h2>Want to add a feature?</h2>
					<a href="https://webfixlab.com/wordpress-offer/" target="_blank">Add it for $99 only</a>
				</div>
				<?php do_action( 'aiwriter_sidebar', AIWRITER_PATH . 'templates/admin/sidebar.php' ); ?>
			</div>
			<?php require AIWRITER_PATH . 'templates/admin/popup.php'; ?>
		</div>
	</div>
</div>
