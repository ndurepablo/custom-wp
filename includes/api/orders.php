<?php
add_action('woocommerce_rest_prepare_shop_order_object', 'agregar_fecha_hora_entrega_en_api', 10, 3);

function agregar_fecha_hora_entrega_en_api($response, $order, $request) {
    // Agregar fecha y hora de entrega al objeto del pedido en la respuesta del endpoint "orders"
    $delivery_date = get_post_meta($order->get_id(), '_delivery_date', true);
    $delivery_time = get_post_meta($order->get_id(), '_delivery_time', true);

    $response->data['delivery_date'] = $delivery_date;
    $response->data['delivery_time'] = $delivery_time;

    return $response;
}


add_action('woocommerce_rest_prepare_shop_order_object', 'agregar_campos_personalizados_en_api', 10, 3);

function agregar_campos_personalizados_en_api($response, $order, $request) {
    // Agregar campos personalizados de usuario al objeto del cliente en la respuesta del endpoint "orders"
    $user_id = $order->get_user_id();
    if ($user_id > 0) {
        $customer = new WC_Customer($user_id);

        $response->data['customer']['plano_numero'] = $customer->get_meta('plano_numero');
        $response->data['customer']['plano_letra'] = $customer->get_meta('plano_letra');
        $response->data['customer']['latitud'] = $customer->get_meta('latitud');
        $response->data['customer']['longitud'] = $customer->get_meta('longitud');
    }

    return $response;
}


add_filter('woocommerce_rest_shop_order_query', 'agregar_filtro_por_fecha_entrega_en_api', 10, 2);

add_filter( 'woocommerce_rest_orders_prepare_object_query', 'filter_orders_by_delivery_date', 10, 2 );
function filter_orders_by_delivery_date( $args, $request ) {
    // Obtener el parÃ¡metro de la URL "delivery_date"
    $delivery_date = $request->get_param( 'delivery_date' );

    // Si no se proporcionÃ³ una fecha de entrega, no filtrar las Ã³rdenes
    if ( empty( $delivery_date ) ) {
        return $args;
    }

    // Agregar un filtro para buscar Ã³rdenes con el valor de la fecha de entrega
    $args['meta_query'][] = array(
        'key' => '_delivery_date',
        'value' => $delivery_date,
    );

    return $args;
}


add_filter('woocommerce_rest_prepare_shop_order_object', 'agregar_contador_productos_categoria_a_order_endpoint', 10, 3);

function agregar_contador_productos_categoria_a_order_endpoint($response, $order, $request) {
    $items = $order->get_items();
    $contador_productos_por_categoria = array();

    foreach ($items as $item) {
        $producto_id = $item->get_product_id();
        $producto_categorias = wp_get_post_terms($producto_id, 'product_cat');
        foreach ($producto_categorias as $categoria) {
            if (array_key_exists($categoria->slug, $contador_productos_por_categoria)) {
                $contador_productos_por_categoria[$categoria->slug] += $item->get_quantity();
            } else {
                $contador_productos_por_categoria[$categoria->slug] = $item->get_quantity();
            }
        }
    }

    $response->data['category_counter'] = $contador_productos_por_categoria;

    return $response;

}
