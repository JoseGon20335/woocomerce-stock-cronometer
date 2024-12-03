<?php
/*
Plugin Name: WooCommerce Stock Cron
Plugin URI: http://localhost
Description: Plugin que actualiza automáticamente el stock de productos.
Author: Jose Gonzalez
Version: 1.3
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

global $time_interval;
global $product_stock_updates;
global $basic_auth; // Flag para manejar el tipo de autenticación

$time_interval = 300; // Intervalo en segundos (5 minutos)
$product_stock_updates = [
    ['SKU' => 'pc01', 'stock' => 10],
    ['SKU' => 'pc02', 'stock' => 20],
    ['SKU' => 'pc03', 'stock' => 30],
    ['SKU' => 'pc04', 'stock' => 40],
];
$basic_auth = true;

function wc_stock_cron_activation() {
    if (!wp_next_scheduled('wc_update_stock_event')) {
        wp_schedule_event(time(), 'custom_interval', 'wc_update_stock_event');
    }
}
register_activation_hook(__FILE__, 'wc_stock_cron_activation');

function wc_stock_cron_deactivation() {
    $timestamp = wp_next_scheduled('wc_update_stock_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'wc_update_stock_event');
    }
}
register_deactivation_hook(__FILE__, 'wc_stock_cron_deactivation');

add_filter('cron_schedules', function($schedules) {
    global $time_interval;
    $schedules['custom_interval'] = [
        'interval' => $time_interval,
        'display'  => __('Intervalo personalizado')
    ];
    return $schedules;
});

// Registrar la tarea del cron
add_action('wc_update_stock_event', 'wc_update_stock_cron_task');

function wc_update_stock_cron_task() {
    global $product_stock_updates;

    // esto debido a que tuve problemas para poder hacer el update usando las keys, explicancion del problema en foro:
    // https://stackoverflow.com/questions/42186757/woocommerce-woocommerce-rest-cannot-view-status-401
    // https://github.com/woocommerce/woocommerce/blob/trunk/docs/rest-api/getting-started.md
    global $basic_auth; // Usamos el flag global para definir el método de autenticación

    // Credenciales para Basic Auth
    $admin_username = 'admin';
    $admin_password = '123';

    // Claves de WooCommerce
    $consumer_key = 'ck_5da09678fd16b844ac2a017befe3bba8b53f5b74';
    $consumer_secret = 'cs_313ad2ecf4782c15f0653ce9298e51a';

    foreach ($product_stock_updates as $product_update) {
        $sku = $product_update['SKU'];
        $stock = $product_update['stock'];

        error_log("Procesando SKU: $sku con stock: $stock");

        // URL de la API
        $auth_get_url = "http://localhost/wordpress/wp-json/wc/v3/products?sku=$sku";

        // Headers según el tipo de autenticación
        $headers = [];
        if ($basic_auth) {
            $headers['Authorization'] = 'Basic ' . base64_encode("$admin_username:$admin_password");
        } else {
            $auth_get_url .= "&consumer_key=$consumer_key&consumer_secret=$consumer_secret";
        }

        // Realizar la solicitud GET
        $response = wp_remote_get($auth_get_url, [
            'headers' => $headers,
        ]);

        // Depuración: imprimir respuesta
        error_log("Respuesta GET: " . print_r($response, true));

        // Verificar si hubo error
        if (is_wp_error($response)) {
            error_log("Error al obtener el producto: " . $response->get_error_message());
            continue;
        }

        // Decodificar respuesta
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data[0]['id'])) {
            error_log("Producto con SKU: $sku no encontrado.");
            continue;
        }

        $product_id = $data[0]['id'];
        error_log("Producto encontrado. ID: $product_id");

        // URL para actualizar stock
        $auth_update_url = "http://localhost/wordpress/wp-json/wc/v3/products/$product_id";

        // Headers para PUT
        $put_headers = $headers;
        $put_headers['Content-Type'] = 'application/json';

        // Actualizar stock
        $update_response = wp_remote_post($auth_update_url, [
            'method' => 'PUT',
            'headers' => $put_headers,
            'body' => json_encode([
                'stock_quantity' => $stock,
                'manage_stock'   => true,
            ]),
        ]);

        // Depuración: imprimir respuesta de la actualización
        error_log("Respuesta PUT: " . print_r($update_response, true));

        // Verificar si hubo error en la actualización
        if (is_wp_error($update_response)) {
            error_log("Error en la actualización del producto: " . $update_response->get_error_message());
            continue;
        }

        $http_code = wp_remote_retrieve_response_code($update_response);
        if ($http_code != 200) {
            error_log("Error en la actualización del producto. Código HTTP: $http_code");
        } else {
            error_log("Actualización exitosa para el producto SKU: $sku con stock: $stock");
        }
    }
}
