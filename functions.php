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



// // En functions.php
// add_action('wp_enqueue_scripts', 'agregar_scripts_ajax');
// function agregar_scripts_ajax() {
//     wp_enqueue_script('ajax-script', get_stylesheet_directory_uri() . '/includes/custom/shipping/js/custom-shipping.js', array('jquery'), true);
//     wp_localize_script('ajax-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
// }
//
// // En functions.php
// add_action('wp_ajax_actualizar_campo2', 'actualizar_campo2');
// add_action('wp_ajax_nopriv_actualizar_campo2', 'actualizar_campo2');
//
// function actualizar_campo2() {
//     // Obtén el valor seleccionado del primer campo de selección
//     $valorSeleccionado = $_POST['valorSeleccionado'];
//
//     // Aquí, dependiendo de $valorSeleccionado, genera las opciones para el segundo campo de selección
//     $opciones = array();
//     if ($valorSeleccionado === 'abecedario') {
//         $opciones = array('A', 'B', 'C', /* ... */ 'Z');
//     } elseif ($valorSeleccionado === 'numeros') {
//         $opciones = array(1, 2, 3, /* ... */ 10);
//     }
//
//     // Genera las opciones HTML
//     $htmlOpciones = '';
//     foreach ($opciones as $opcion) {
//         $htmlOpciones .= '<option value="' . $opcion . '">' . $opcion . '</option>';
//     }
//
//     // Devuelve las opciones como respuesta AJAX
//     echo $htmlOpciones;
//
//     // Importante: ¡Asegúrate de que no haya ninguna otra salida antes de esta línea!
//     die();
// }
