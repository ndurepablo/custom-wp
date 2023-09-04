<?php
include_once dirname( __FILE__ ) . '/includes.php';

// Css import files
function enqueue_styles_child_theme() {

	$parent_style = 'parent-style';
	$child_style  = 'child-style';
	$cutom_account = 'cutom-account';

	wp_enqueue_style( $parent_style,
				get_template_directory_uri() . '/style.css' );

	wp_enqueue_style( $cutom_account,
				get_stylesheet_directory_uri() . '/includes/css/custom-account.css',
				);

	wp_enqueue_style( $child_style,
				get_stylesheet_directory_uri() . '/style.css',
				array( $parent_style ),
				wp_get_theme()->get('Version')
				);
}
add_action( 'wp_enqueue_scripts', 'enqueue_styles_child_theme' );

add_filter( 'woocommerce_rest_check_permissions', '__return_true' );
