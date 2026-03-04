<?php
/**
 * My Buddy Claude theme functions
 *
 * @package mybuddyclaude
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup.
 */
function mybuddyclaude_setup(): void {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'mybuddyclaude' ),
			'footer'  => __( 'Footer Navigation', 'mybuddyclaude' ),
		)
	);
}
add_action( 'after_setup_theme', 'mybuddyclaude_setup' );

/**
 * Enqueue Google Fonts.
 */
function mybuddyclaude_enqueue_fonts(): void {
	$font_url = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&display=swap';

	wp_enqueue_style(
		'mybuddyclaude-fonts',
		$font_url,
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'mybuddyclaude_enqueue_fonts' );
add_action( 'admin_enqueue_scripts', 'mybuddyclaude_enqueue_fonts' );
add_action( 'enqueue_block_editor_assets', 'mybuddyclaude_enqueue_fonts' );

/**
 * Enqueue theme styles.
 */
function mybuddyclaude_enqueue_styles(): void {
	wp_enqueue_style(
		'mybuddyclaude-theme',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'mybuddyclaude-global',
		get_template_directory_uri() . '/assets/css/global.css',
		array( 'mybuddyclaude-theme' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'mybuddyclaude_enqueue_styles' );

/**
 * Add editor styles.
 */
function mybuddyclaude_editor_styles(): void {
	add_editor_style(
		array(
			'assets/css/global.css',
			'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&display=swap',
		)
	);
}
add_action( 'after_setup_theme', 'mybuddyclaude_editor_styles' );

/**
 * Register block pattern categories.
 */
function mybuddyclaude_register_pattern_categories(): void {
	register_block_pattern_category(
		'mybuddyclaude',
		array( 'label' => __( 'My Buddy Claude', 'mybuddyclaude' ) )
	);
}
add_action( 'init', 'mybuddyclaude_register_pattern_categories' );

/**
 * Register custom post type: Tools
 */
function mybuddyclaude_register_post_types(): void {
	register_post_type(
		'mbc_tool',
		array(
			'labels'             => array(
				'name'               => __( 'Tools', 'mybuddyclaude' ),
				'singular_name'      => __( 'Tool', 'mybuddyclaude' ),
				'add_new'            => __( 'Add New Tool', 'mybuddyclaude' ),
				'add_new_item'       => __( 'Add New Tool', 'mybuddyclaude' ),
				'edit_item'          => __( 'Edit Tool', 'mybuddyclaude' ),
				'new_item'           => __( 'New Tool', 'mybuddyclaude' ),
				'view_item'          => __( 'View Tool', 'mybuddyclaude' ),
				'view_items'         => __( 'View Tools', 'mybuddyclaude' ),
				'search_items'       => __( 'Search Tools', 'mybuddyclaude' ),
				'not_found'          => __( 'No tools found.', 'mybuddyclaude' ),
				'not_found_in_trash' => __( 'No tools found in Trash.', 'mybuddyclaude' ),
				'menu_name'          => __( 'Tools', 'mybuddyclaude' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'tools' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-hammer',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'mybuddyclaude_register_post_types' );

/**
 * Register custom taxonomy: Tool Category
 */
function mybuddyclaude_register_taxonomies(): void {
	register_taxonomy(
		'tool_category',
		'mbc_tool',
		array(
			'labels'       => array(
				'name'          => __( 'Tool Categories', 'mybuddyclaude' ),
				'singular_name' => __( 'Tool Category', 'mybuddyclaude' ),
			),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'tool-category' ),
		)
	);
}
add_action( 'init', 'mybuddyclaude_register_taxonomies' );

/**
 * Add meta fields for tools.
 */
function mybuddyclaude_register_tool_meta(): void {
	$meta_fields = array(
		'mbc_tool_url'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
		),
		'mbc_tool_status' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'mbc_tool_tech'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
	);

	foreach ( $meta_fields as $key => $config ) {
		register_post_meta(
			'mbc_tool',
			$key,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => $config['type'],
				'sanitize_callback' => $config['sanitize_callback'],
				'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
			)
		);
	}
}
add_action( 'init', 'mybuddyclaude_register_tool_meta' );
