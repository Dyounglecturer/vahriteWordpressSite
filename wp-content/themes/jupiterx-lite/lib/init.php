<?php
/**
 * Prepare and initialize the Jupiter framework.
 *
 * @package JupiterX\Framework
 *
 * @since   1.0.0
 */

add_action( 'jupiterx_init', 'jupiterx_define_constants', -1 );
/**
 * Define constants.
 *
 * @since 1.0.0
 * @ignore
 *
 * @return void
 */
function jupiterx_define_constants() {
	$theme_data = get_file_data( get_template_directory() . '/style.css', [ 'Version' ], 'jupiterx' );

	// Define premium.
	if ( ! defined( 'JUPITERX_PREMIUM' ) ) {
		define( 'JUPITERX_PREMIUM', false );
	}

	// Define version.
	define( 'JUPITERX_VERSION', array_shift( $theme_data ) );
	define( 'JUPITERX_INITIAL_FREE_VERSION', '1.3.0' );
	define( 'JUPITERX_NAME', 'Jupiter X' );
	define( 'JUPITERX_SLUG', 'jupiterx' );

	// Define paths.
	if ( ! defined( 'JUPITERX_THEME_PATH' ) ) {
		define( 'JUPITERX_THEME_PATH', wp_normalize_path( trailingslashit( get_template_directory() ) ) );
	}

	define( 'JUPITERX_PATH', JUPITERX_THEME_PATH . 'lib/' );
	define( 'JUPITERX_API_PATH', JUPITERX_PATH . 'api/' );
	define( 'JUPITERX_ASSETS_PATH', JUPITERX_PATH . 'assets/' );
	define( 'JUPITERX_LANGUAGES_PATH', JUPITERX_PATH . 'languages/' );
	define( 'JUPITERX_RENDER_PATH', JUPITERX_PATH . 'render/' );
	define( 'JUPITERX_TEMPLATES_PATH', JUPITERX_PATH . 'templates/' );
	define( 'JUPITERX_STRUCTURE_PATH', JUPITERX_TEMPLATES_PATH . 'structure/' );
	define( 'JUPITERX_FRAGMENTS_PATH', JUPITERX_TEMPLATES_PATH . 'fragments/' );

	// Define urls.
	if ( ! defined( 'JUPITERX_THEME_URL' ) ) {
		define( 'JUPITERX_THEME_URL', trailingslashit( get_template_directory_uri() ) );
	}

	define( 'JUPITERX_URL', JUPITERX_THEME_URL . 'lib/' );
	define( 'JUPITERX_API_URL', JUPITERX_URL . 'api/' );
	define( 'JUPITERX_ASSETS_URL', JUPITERX_URL . 'assets/' );
	define( 'JUPITERX_LESS_URL', JUPITERX_ASSETS_URL . 'less/' );
	define( 'JUPITERX_JS_URL', JUPITERX_ASSETS_URL . 'js/' );
	define( 'JUPITERX_IMAGE_URL', JUPITERX_ASSETS_URL . 'images/' );

	// Define admin paths.
	define( 'JUPITERX_ADMIN_PATH', JUPITERX_PATH . 'admin/' );

	// Define admin url.
	define( 'JUPITERX_ADMIN_URL', JUPITERX_URL . 'admin/' );
	define( 'JUPITERX_ADMIN_ASSETS_URL', JUPITERX_ADMIN_URL . 'assets/' );
	define( 'JUPITERX_ADMIN_JS_URL', JUPITERX_ADMIN_ASSETS_URL . 'js/' );

	// Define helpers.
	define( 'JUPITERX_IMAGE_SIZE_OPTION', JUPITERX_SLUG . '_image_sizes' );
}

add_action( 'jupiterx_init', 'jupiterx_load_dependencies', 5 );
/**
 * Load dependencies.
 *
 * @since 1.0.0
 * @ignore
 *
 * @return void
 */
function jupiterx_load_dependencies() {
	require_once JUPITERX_API_PATH . 'init.php';

	/**
	 * Fires before Jupiter API loads.
	 *
	 * @since 1.0.0
	 */
	do_action( 'jupiterx_before_load_api' );

	$components = [
		'api',
		'compatibility',
		'actions',
		'html',
		'post-meta',
		'image',
		'fonts',
		'customizer',
		'custom-fields',
		'template',
		'layout',
		'header',
		'menu',
		'widget',
		'footer',
	];

	if ( class_exists( 'Elementor\Plugin' ) ) {
		$components[] = 'elementor';
	}

	if ( class_exists( 'woocommerce' ) ) {
		$components[] = 'woocommerce';
	}

	if ( class_exists( 'Rocket_Lazyload_Requirements_Check' ) || class_exists( 'WP_Rocket_Requirements_Check' ) ) {
		$components[] = 'lazy-load';
	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$components[] = 'events-calendar';
	}

	// Load the necessary Jupiter components.
	jupiterx_load_api_components( $components );

	// Add third party styles and scripts compiler support.
	jupiterx_add_api_component_support( 'wp_styles_compiler' );
	jupiterx_add_api_component_support( 'wp_scripts_compiler' );

	/**
	 * Fires after Jupiter API loads.
	 *
	 * @since 1.0.0
	 */
	do_action( 'jupiterx_after_load_api' );
}

add_action( 'jupiterx_init', 'jupiterx_add_theme_support' );
/**
 * Add theme support.
 *
 * @since 1.0.0
 * @ignore
 *
 * @return void
 */
function jupiterx_add_theme_support() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

	// Gutenberg.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );

	// Jupiter specific.
	add_theme_support( 'jupiterx-default-styling' );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'woocommerce' );

	jupiterx_register_image_sizes();
}

add_action( 'jupiterx_get_sellkit_link_schedule_hook', 'jupiterx_get_sellkit_link_schedule' );

/**
 * Schedule event for getting sellkit pro download link.
 *
 * @param array $body array of necessary values for api.
 * @since 2.0.6
 */
function jupiterx_get_sellkit_link_schedule( $body ) {
	if ( empty( $body ) ) {
		return;
	}

	$link = jupiterx_get_sellkit_download_link( $body );

	if ( empty( $link ) ) {
		return;
	}

	set_transient( 'jupiterx_sellkit_pro_link', $link, 6 * HOUR_IN_SECONDS );
}

add_action( 'jupiterx_init', 'jupiterx_includes' );
/**
 * Include framework files.
 *
 * @since 1.0.0
 * @ignore
 *
 * @return void
 */
function jupiterx_includes() {

	// Include admin.
	if ( is_admin() ) {
		require_once JUPITERX_ADMIN_PATH . 'tgmpa/class-tgm-plugin-activation.php';
		require_once JUPITERX_ADMIN_PATH . 'tgmpa/functions.php';
		require_once JUPITERX_ADMIN_PATH . 'assets.php';
		require_once JUPITERX_ADMIN_PATH . 'core-install/core-install.php';
		require_once JUPITERX_ADMIN_PATH . 'functions.php';
		require_once JUPITERX_ADMIN_PATH . 'control-panel/control-panel.php';
		require_once JUPITERX_ADMIN_PATH . 'notices/feedback-notification-bar.php';
		require_once JUPITERX_ADMIN_PATH . 'welcome/welcome.php';
	}

	// Include assets.
	require_once JUPITERX_ASSETS_PATH . 'assets.php';

	// Include renderers.
	require_once JUPITERX_RENDER_PATH . 'template-parts.php';
	require_once JUPITERX_RENDER_PATH . 'fragments.php';
	require_once JUPITERX_RENDER_PATH . 'widget-area.php';
	require_once JUPITERX_RENDER_PATH . 'walker.php';
	require_once JUPITERX_RENDER_PATH . 'menu.php';
}

/**
 * Get download link for sellkit pro.
 *
 * @param array $body array of necessary values for api.
 * @since 2.0.6
 * @return string.
 */
function jupiterx_get_sellkit_download_link( $body ) {
	$response = wp_remote_get( 'https://my.getsellkit.com/wp-json/sellkit/v1/bundled/sellkit_pro/latest', [
		'timeout' => 10,
		'body' => $body,
	] );

	$response_code = wp_remote_retrieve_response_code( $response );

	if ( is_wp_error( $response ) && empty( $response['body'] ) && 200 !== (int) $response_code ) {
		return;
	}

	$sellkit_repo = str_replace( '"', '', stripslashes( $response['body'] ) );

	set_transient( 'jupiterx_sellkit_pro_link', $sellkit_repo, 6 * HOUR_IN_SECONDS );

	return $sellkit_repo;
}

add_action( 'admin_menu', 'jupiterx_register_theme_page' );
/**
 * Register theme page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function jupiterx_register_theme_page() {
	add_theme_page( JUPITERX_NAME, JUPITERX_NAME, 'edit_theme_options', JUPITERX_SLUG, function() {
		if ( ! jupiterx_has_required_plugins_activated() ) {
			include_once JUPITERX_ADMIN_PATH . '/welcome/view.php';

			return;
		}

		include_once JUPITERX_ADMIN_PATH . '/control-panel/views/layout/master.php';
	} );
}

add_action( 'jupiterx_init', 'jupiterx_load_textdomain' );
/**
 * Load text domain.
 *
 * @since 1.0.0
 * @ignore
 *
 * @return void
 */
function jupiterx_load_textdomain() {
	load_theme_textdomain( 'jupiterx-lite', JUPITERX_LANGUAGES_PATH );
}

/**
 * Fires before Jupiter loads.
 *
 * @since 1.0.0
 */
do_action( 'jupiterx_before_init' );

	/**
	 * Load Jupiter framework.
	 *
	 * @since 1.0.0
	 */
	do_action( 'jupiterx_init' );

/**
 * Fires after Jupiter loads.
 *
 * @since 1.0.0
 */
do_action( 'jupiterx_after_init' );
