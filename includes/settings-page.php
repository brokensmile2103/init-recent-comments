<?php
/**
 * Trang Settings của plugin.
 *
 * @package InitRecentComments
 */

defined( 'ABSPATH' ) || exit;

// ===== REGISTER SETTINGS ===== //
add_action( 'admin_init', 'init_plugin_suite_recent_comments_register_settings' );

/**
 * Đăng ký setting, section và field.
 *
 * @return void
 */
function init_plugin_suite_recent_comments_register_settings() {
	register_setting(
		INIT_PLUGIN_SUITE_IRC_SLUG . '_settings_group',
		INIT_PLUGIN_SUITE_IRC_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'init_plugin_suite_recent_comments_sanitize_settings',
			'default'           => array(),
		)
	);

	add_settings_section(
		INIT_PLUGIN_SUITE_IRC_SLUG . '_main_section',
		__( 'General Settings', 'init-recent-comments' ),
		'__return_false',
		INIT_PLUGIN_SUITE_IRC_SLUG
	);

	add_settings_field(
		'disable_css',
		__( 'Disable built-in CSS', 'init-recent-comments' ),
		'init_plugin_suite_recent_comments_disable_css_field',
		INIT_PLUGIN_SUITE_IRC_SLUG,
		INIT_PLUGIN_SUITE_IRC_SLUG . '_main_section'
	);
}

// ===== SANITIZE SETTINGS ===== //

/**
 * Sanitize settings.
 *
 * @param mixed $input Dữ liệu gửi lên từ form.
 * @return array
 */
function init_plugin_suite_recent_comments_sanitize_settings( $input ) {
	return array(
		'disable_css' => ( is_array( $input ) && ! empty( $input['disable_css'] ) ) ? 1 : 0,
	);
}

// ===== FIELD RENDER FUNCTION ===== //

/**
 * Render checkbox "Disable built-in CSS".
 *
 * @return void
 */
function init_plugin_suite_recent_comments_disable_css_field() {
	$options = get_option( INIT_PLUGIN_SUITE_IRC_OPTION );
	$checked = is_array( $options ) && ! empty( $options['disable_css'] );
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( INIT_PLUGIN_SUITE_IRC_OPTION ); ?>[disable_css]" value="1" <?php checked( $checked ); ?> />
		<?php esc_html_e( 'Use your own theme styling instead of the plugin’s CSS.', 'init-recent-comments' ); ?>
	</label>
	<?php
}

// ===== ADD SETTINGS PAGE TO MENU ===== //
add_action( 'admin_menu', 'init_plugin_suite_recent_comments_add_settings_page' );

/**
 * Thêm trang Settings vào menu.
 *
 * @return void
 */
function init_plugin_suite_recent_comments_add_settings_page() {
	add_options_page(
		__( 'Init Recent Comments Settings', 'init-recent-comments' ),
		__( 'Init Recent Comments', 'init-recent-comments' ),
		'manage_options',
		INIT_PLUGIN_SUITE_IRC_SLUG,
		'init_plugin_suite_recent_comments_render_settings_page'
	);
}

// ===== RENDER PAGE HTML ===== //

/**
 * Render trang Settings.
 *
 * @return void
 */
function init_plugin_suite_recent_comments_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Init Recent Comments Settings', 'init-recent-comments' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( INIT_PLUGIN_SUITE_IRC_SLUG . '_settings_group' );
			do_settings_sections( INIT_PLUGIN_SUITE_IRC_SLUG );
			submit_button();
			?>
		</form>

		<h2><?php esc_html_e( 'Shortcode Builder', 'init-recent-comments' ); ?></h2>
		<div id="shortcode-builder-target" data-plugin="init-recent-comments"></div>
	</div>
	<?php
}
