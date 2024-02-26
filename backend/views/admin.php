<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div id="tabs" class="settings-tab">
		<ul>
			<li><a href="#tabs-1"><?php esc_html_e( 'Settings', Y_TEXTDOMAIN ); ?></a></li>
			<li><a href="#tabs-2"><?php esc_html_e( 'Settings 2', Y_TEXTDOMAIN ); ?></a></li>
			<?php
			?>
			<li><a href="#tabs-3"><?php esc_html_e( 'Import/Export', Y_TEXTDOMAIN ); ?></a></li>
			<?php
			?>
		</ul>
		<?php
		require_once plugin_dir_path( __FILE__ ) . 'settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'settings-2.php';
		?>
		<?php
		?>
		<div id="tabs-3" class="metabox-holder">
			<div class="postbox">
				<h3 class="hndle"><span><?php esc_html_e( 'Export Settings', Y_TEXTDOMAIN ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export the plugin\'s settings for this site as a .json file. This will allows you to easily import the configuration to another installation.', Y_TEXTDOMAIN ); ?></p>
					<form method="post">
						<p><input type="hidden" name="y_action" value="export_settings" /></p>
						<p>
							<?php wp_nonce_field( 'y_export_nonce', 'y_export_nonce' ); ?>
							<?php submit_button( __( 'Export', Y_TEXTDOMAIN ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div>
			</div>

			<div class="postbox">
				<h3 class="hndle"><span><?php esc_html_e( 'Import Settings', Y_TEXTDOMAIN ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Import the plugin\'s settings from a .json file. This file can be retrieved by exporting the settings from another installation.', Y_TEXTDOMAIN ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<p>
							<input type="file" name="y_import_file"/>
						</p>
						<p>
							<input type="hidden" name="y_action" value="import_settings" />
							<?php wp_nonce_field( 'y_import_nonce', 'y_import_nonce' ); ?>
							<?php submit_button( __( 'Import', Y_TEXTDOMAIN ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div>
			</div>
		</div>
		<?php
		?>
	</div>

	<div class="right-column-settings-page metabox-holder">
		<div class="postbox">
			<h3 class="hndle"><span><?php esc_html_e( 'Yespo.', Y_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<a href="https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered"><img src="https://raw.githubusercontent.com/WPBP/boilerplate-assets/master/icon-256x256.png" alt=""></a>
			</div>
		</div>
	</div>
</div>
