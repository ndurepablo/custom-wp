<?php
add_action('woocommerce_rest_prepare_shop_order_object', 'agregar_categoria_producto_en_pedido_en_api', 10, 3);

function agregar_categoria_producto_en_pedido_en_api($response, $order, $request) {
    // Recorrer la lista de productos en el pedido y agregar la categoria de cada producto
    foreach ($response->data['line_items'] as &$line_item) {
        // Obtener la categoria del producto
        $product_categories = wp_get_post_terms($line_item['product_id'], 'product_cat', array('fields' => 'names'));

        // Agregar la categoria al objeto de producto en la respuesta del endpoint "orders"
        $line_item['category'] = $product_categories ? $product_categories[0] : '';
    }
    return $response;
}