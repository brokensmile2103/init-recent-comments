<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ===== REGISTER SETTINGS ===== //
add_action( 'admin_init', function () {
	register_setting(
		INIT_PLUGIN_SUITE_IRC_SLUG . '_settings_group',
		INIT_PLUGIN_SUITE_IRC_OPTION,
		[
			'type'              => 'array',
			'sanitize_callback' => 'init_plugin_suite_recent_comments_sanitize_settings',
			'default'           => [],
		]
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
});

// ===== SANITIZE SETTINGS ===== //
function init_plugin_suite_recent_comments_sanitize_settings( $input ) {
	return [
		'disable_css' => isset( $input['disable_css'] ) ? 1 : 0,
	];
}

// ===== FIELD RENDER FUNCTION ===== //
function init_plugin_suite_recent_comments_disable_css_field() {
	$options = get_option( INIT_PLUGIN_SUITE_IRC_OPTION );
	$checked = isset( $options['disable_css'] ) && $options['disable_css'] ? 'checked' : '';
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( INIT_PLUGIN_SUITE_IRC_OPTION ); ?>[disable_css]" value="1" <?php echo esc_attr( $checked ); ?> />
		<?php esc_html_e( 'Use your own theme styling instead of the plugin’s CSS.', 'init-recent-comments' ); ?>
	</label>
	<?php
}

// ===== ADD SETTINGS PAGE TO MENU ===== //
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Init Recent Comments Settings', 'init-recent-comments' ),
		__( 'Init Recent Comments', 'init-recent-comments' ),
		'manage_options',
		INIT_PLUGIN_SUITE_IRC_SLUG,
		'init_plugin_suite_recent_comments_render_settings_page'
	);
});

// ===== RENDER PAGE HTML ===== //
function init_plugin_suite_recent_comments_render_settings_page() {
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

		<h2><?php esc_html_e('Shortcode Builder', 'init-recent-comments'); ?></h2>
        <div id="shortcode-builder-target" data-plugin="init-recent-comments"></div>
	</div>
	<?php
}
