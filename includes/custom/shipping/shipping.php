<?php
include_once dirname( __FILE__ ) . '/jsonreader.php';

function mytheme_enqueue_scripts() {
    wp_enqueue_script( 'custom-shipping', get_stylesheet_directory_uri() . '/includes/custom/shipping/js/custom-shipping.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0', true );
    wp_enqueue_style('custom-css', get_stylesheet_directory_uri() . '/includes/custom/shipping/custom.css');
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css' );
}

add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_scripts' );

/**
 * Add custom field to the checkout page
 */
add_action( 'woocommerce_after_order_notes', function ( $checkout ) {
    echo '<div id="custom_checkout_field"><h2>' . __( 'Seleccione la zona y fecha de entrega:' ) . '</h2>';

    // Obtener el subtotal del pedido
    $subtotal = WC()->cart->subtotal;
    // Mostrar el valor del subtotal en un encabezado h1
    echo '<h1>Subtotal: ' . $subtotal . '</h1>';

    $gba_shipping_cost = ($subtotal > 100) ? 0 : 300;
    $country_shipping_cost = ($subtotal > 500) ? 0 : 500;
    
    // Opción 1: Domicilio en Capital Federal y GBA
    woocommerce_form_field( 'radio_region', array(
        'type'    => 'radio',
        'class'   => array('form-row-radio'),
        'options' => array(
            'gba' => __('Domicilio en Capital Federal y GBA', 'woocommerce'),
            'country' => __('Barrio privado / Country', 'woocommerce'),
        ),
        
    ), $checkout->get_value( 'radio_region' ) );

//     // Llamada al json reader que  retorna un arr con las zonas mas generales
//     $hoodsJsonFile = get_stylesheet_directory_uri() . '/includes/custom/shipping/js/hoods.json';
//     $gba_region_list = json_reader($hoodsJsonFile);
//
//     $countriesJsonFile = get_stylesheet_directory_uri() . '/includes/custom/shipping/js/countries.json';
//     $countries_region_list = json_reader($countriesJsonFile);
//
//     // jsonreaderhoods retorna una lista con los barrios o countries del json
//     // este codigo habria que borrar
//     $countries = json_reader_hoods($countriesJsonFile, 'zona_norte', 'countries');
//
//
//     // jsonreaderhoods retorna una lista de los barrios con posibilidad de acceder a sus valores
//     $hoods = json_reader_hoods($hoodsJsonFile, 'caba', 'hoods');
//     $south_hoods = json_reader_hoods($hoodsJsonFile, 'zona_sur', 'hoods');
//
//
//
//     // creo un arreglo y defino los valores de nombre de region y costo de envio
//     $hoods_options = array('select_opt' => __('Seleccione una opción', 'woocommerce'), );
//     foreach ($hoods as $region) {
//         $name = $region['name'];
//         $shippingCost = $region['slug'];
//         $hoods_options[$shippingCost] = $name; // ['shippingCost' => $name]
//     }
//     woocommerce_form_field('custom_shipping_cost', array(
//         'type'    => 'select',
//         'class'   => array('form-row-radio', 'caba_hoods', 'hiden'),
//         'label'         => __('Seleccione una opción'),
//         'options' => $hoods_options, // aca se envia shippingCost como value del option y el name como el texto de option
//         'required' => true,
//         'default' => 'select_opt'
//     ), $checkout->get_value('custom_shipping_cost'));
//
//     $cost = json_reader_shipping_cost($hoodsJsonFile, 'caba', 'hoods');

    // Mostrar el campo en la sección de envío
    woocommerce_form_field( 'custom_shipping_date', array(
        'type'          => 'text',
        'class'         => array('form-row-wide', 'hiden'),
        'value'         => get_post_meta( $post->ID, 'custom_shipping_date', true ),
        'label'         => __('Fecha de envío'),
        'placeholder'   => __('Seleccione una fecha'),
        'required'      => true,
        'autocomplete'  => 'off',
    ), get_user_meta( get_current_user_id(), 'custom_shipping_date', true ));
    echo '</div>';


} );
/**
 * Muestra el valor del nuevo campo DATE en la página de edición del pedido
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'mostrar_campo_personalizado_en_admin_pedido', 10, 1 );
 
function mostrar_campo_personalizado_en_admin_pedido($order){
    echo '<p><strong>'.__('Fecha de entrega').':</strong> ' . get_post_meta( $order->id, 'custom_shipping_date', true ) . '</p>';
}


/**
 * Checkout Process
 */

// Validacion para que el campo custom_shipping_cost sea olbigatorio
add_action( 'woocommerce_checkout_process', 'customised_checkout_field_process' );
function customised_checkout_field_process() {
    if ( ! $_POST['custom_shipping_cost'] || $_POST['custom_shipping_cost'] === 'select_opt' ) {
        wc_add_notice( __( 'Please enter cost!' ), 'error' );
    }

    if ( ! $_POST['custom_shipping_date'] ) {
        wc_add_notice( __( 'Please enter date!' ), 'error' );
    }
}

/**
 * Update the value given in custom field
 */
// Si custom_shipping_cost se envia por post y no está vacio, hace update del order_id en post_meta
add_action( 'woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta' );
function custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['custom_shipping_cost'] || $_POST['custom_shipping_cost'] === 'select_opt' ) ) {
        update_post_meta( $order_id, 'custom_shipping_cost', sanitize_text_field( $_POST['custom_shipping_cost'] ) );
    }
    if ( ! empty( $_POST['custom_shipping_date'] ) ) {
        update_post_meta( $order_id, 'custom_shipping_date', sanitize_text_field( $_POST['custom_shipping_date'] ) );
    }
}

// reinicia al cargar en cart y checkout
add_action( 'wp', 'reset_custom_shipping_cost' );
function reset_custom_shipping_cost() {
    if ( ! is_admin() && is_cart() ) {
        WC()->session->set( 'custom_shipping_cost', 0 ); // Establecer el valor en cero al cargar la página del carrito
    }
    elseif ( ! is_admin() && is_checkout() ) {
        WC()->session->set( 'custom_shipping_cost', 0 ); // Establecer el valor en cero al cargar la página del carrito
    }
}
// Agrega 'costo_envio' al fee para calcular el total cost
add_action( 'woocommerce_cart_calculate_fees', 'agregar_costo_envio' );
function agregar_costo_envio() {
    $custom_shipping_cost = WC()->session->get( 'custom_shipping_cost' ); // Obtener el valor actual de custom_shipping_cost guardado
    
    if ( ! is_numeric( $custom_shipping_cost ) ) {
        $custom_shipping_cost = 0; // Establecer el valor en cero si no es un número válido
    }
    
    $costo_envio = (float) $custom_shipping_cost; // Convertir el valor a flotante (o ajusta el tipo de dato según sea necesario)
    WC()->cart->add_fee( 'Costo de envío', $costo_envio ); // agrega al fee 'costo de envio'
}

// AJAX JQUERY FUNCTION
// Actualizar el costo de envío al cambiar el campo custom_shipping_cost
add_action( 'wp_ajax_actualizar_costo_envio', 'actualizar_costo_envio' );
add_action( 'wp_ajax_nopriv_actualizar_costo_envio', 'actualizar_costo_envio' );
function actualizar_costo_envio() {
    if ( isset( $_POST['custom_shipping_cost'] ) ) {
        $customShippingCost = sanitize_text_field( $_POST['custom_shipping_cost'] );
        $zoneSelected = sanitize_text_field( $_POST['zone_selected'] );

        // Obtener el subtotal del pedido
        $subtotal = WC()->cart->subtotal;
        $hoodsJsonFile = get_stylesheet_directory_uri() . '/includes/custom/shipping/js/hoods.json';
        $cost = json_reader_shipping_cost($hoodsJsonFile, $zoneSelected, 'hoods');
        foreach ($cost as $slug => $info) {
            if ($customShippingCost === $slug) {
                $shippingCost = ($subtotal > $info['freeShippingThreshold']) ? 0 : $info['shippingCost'];
            }
        }


        // Establecer el valor de 'custom_shipping_cost' en la sesión para guardarlo al momento de hacer el pago
        WC()->session->set( 'custom_shipping_cost', $shippingCost );
        
        // Actualizar el costo de envío y el total
        WC()->cart->set_shipping_total( $shippingCost );
        WC()->cart->calculate_totals();
    }

    // Finalizar la ejecución del script
    wp_die();
}

