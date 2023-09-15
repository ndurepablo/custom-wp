<?php
// Agrega un nuevo endpoint a la API de WooCommerce
function product_category() {
    register_rest_route('wc/v3', 'product-category', array(
        'methods' => 'GET',
        'callback' => 'get_product_by_category',
    ));
}
add_action('rest_api_init', 'product_category');


// Función de callback para obtener productos por categoría
function get_product_by_category($request) {
    $category = isset($_GET['category']) ? $_GET['category'] : 'Vegetable';

    // Fecha que deseas buscar (por ejemplo, '01-08-2023')
    $date_to_search = isset($_GET['shipping_date']) ? $_GET['shipping_date'] : '22-09-2023';

    $args = array(
        'meta_key'      => 'custom_shipping_date', // Postmeta key field
        'meta_value'    => $date_to_search, // Postmeta value field
        'meta_compare'  => '=', // Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’ (only in WP >= 3.5), and ‘NOT EXISTS’ (also only in WP >= 3.5). Values ‘REGEXP’, ‘NOT REGEXP’ and ‘RLIKE’ were added in WordPress 3.7. Default value is ‘=’.
        'return'        => 'ids' // Accepts a string: 'ids' or 'objects'. Default: 'objects'.
    );
    // Obtener las órdenes que cumplen con el criterio de consulta
    $filtered_order_ids = wc_get_orders($args);

    // Inicializar un arreglo para almacenar la información completa de las órdenes
    $result = array();

    // Recorre las órdenes obtenidas
    foreach ($filtered_order_ids as $order_id) {
        $order = wc_get_order($order_id); // Obtener la instancia completa de la orden

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_id = $product->get_id(); // Obtener el ID del producto

            // Obtener información de las categorías del producto
            $product_categories = wp_get_post_terms($product_id, 'product_cat');
            $category_names = array();

            foreach ($product_categories as $category) {
                $category_names[] = $category->name;
            }

            // Verificar si el producto pertenece a la categoría 'Vegetable'
            if (in_array("Vegetable", $category_names)) {
                $user_id = $order->get_user_id();
                $user_info = get_userdata($user_id);
                $user = $user_info->user_login;
                $product_name = $product->get_name();
                $quantity_purchased = $item->get_quantity();

                // Verificar si el producto ya existe en $result
                if (!isset($result[$product_id])) {
                    $result[$product_id] = array(
                        'name' => $product_name,
                        'users' => array(),
                        'total_quantity' => 0, // Inicializar la suma total
                    );
                }

                // Agregar el comprador a la lista de compradores para este producto
                $result[$product_id]['users'][$user] = $quantity_purchased;
                // Sumar la cantidad al total
                $result[$product_id]['total_quantity'] += $quantity_purchased;
            }
        }
    }
    return $result;
}



add_action('woocommerce_rest_prepare_shop_order_object', 'agregar_fecha_hora_entrega_en_api', 10, 3);

function agregar_fecha_hora_entrega_en_api($response, $order, $request) {
    // Agregar fecha y hora de entrega al objeto del pedido en la respuesta del endpoint "orders"
    $delivery_date = get_post_meta($order->get_id(), '_delivery_date', true);
    $delivery_time = get_post_meta($order->get_id(), '_delivery_time', true);

    $response->data['delivery_date'] = $delivery_date;
    $response->data['delivery_time'] = $delivery_time;
    $response->data['custom_shipping_date'] = get_post_meta($order->get_id(), 'custom_shipping_date', true);


    return $response;
}


add_action('woocommerce_rest_prepare_shop_order_object', 'agregar_campos_personalizados_en_api', 10, 3);

function agregar_campos_personalizados_en_api($response, $order, $request) {
    // Agregar campos personalizados de usuario al objeto del cliente en la respuesta del endpoint "orders"
        $customer = new WC_Customer($user_id);

        $response->data['customer']['plano_numero'] = $customer->get_meta('plano_numero');
        $response->data['customer']['plano_letra'] = $customer->get_meta('plano_letra');
        $response->data['customer']['latitud'] = $customer->get_meta('latitud');
        $response->data['customer']['longitud'] = $customer->get_meta('longitud');

        // Agregar "custom_shipping_date" al objeto del pedido en la respuesta del endpoint "orders"
        $response->data['custom_shipping_date'] = get_post_meta($order->get_id(), 'custom_shipping_date', true);

    return $response;
}


add_filter('woocommerce_rest_shop_order_query', 'agregar_filtro_por_fecha_entrega_en_api', 10, 2);

add_filter( 'woocommerce_rest_orders_prepare_object_query', 'filter_orders_by_delivery_date', 10, 2 );
function filter_orders_by_delivery_date( $args, $request ) {
    // Obtener el parámetro de la URL "delivery_date" de la solicitud
    $delivery_date = $request->get_param( 'delivery_date' );

    // Si no se proporcionó una fecha de entrega, no filtrar las órdenes
    if ( empty( $delivery_date ) ) {
        return $args;
    }

    // Agregar un filtro para buscar órdenes con el valor de la fecha de entrega
    $args['meta_query'][] = array(
        'key' => 'custom_shipping_date', // Reemplaza con la clave correcta de metadatos
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
