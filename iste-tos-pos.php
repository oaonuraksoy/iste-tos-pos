<?php
/**
 * Plugin Name:       İşte Tos Pos
 * Plugin URI:        https://onuraksoy.com.tr
 * Description:       İşte Tos Pos İş Bankası NetSpay için hazırlanmış. 3D Pay entegrasyonlu, güvenli ve hızlı WooCommerce Sanal POS ödeme eklentisi.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      8.3
 * Author:            Onur AKSOY
 * Author URI:        https://onuraksoy.com.tr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       iste-tos-pos
 * Domain Path:       /languages
 * WC requires at least: 9.0
 * WC tested up to:   9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ISTE_TOS_POS_VERSION', '1.2.1' );
define( 'ISTE_TOS_POS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

function iste_tos_pos_init(): void {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'iste_tos_pos_woocommerce_missing_notice' );
        
        return;
    }

    require_once ISTE_TOS_POS_PLUGIN_DIR . 'includes/class-iste-tos-pos-hash.php';
    require_once ISTE_TOS_POS_PLUGIN_DIR . 'includes/class-wc-gateway-iste-tos-pos.php';
    require_once ISTE_TOS_POS_PLUGIN_DIR . 'includes/class-iste-tos-pos-response-handler.php';

    if ( ! is_ssl() ) {
        add_action( 'admin_notices', 'iste_tos_pos_https_notice' );
        
        if ( function_exists( 'wc_get_logger' ) ) {
            wc_get_logger()->error(
                'İşte Tos Pos: Site HTTPS kullanmıyor. Güvenli ödeme için SSL gereklidir.',
                [ 'source' => 'iste-tos-pos' ]
            );
        }
    }

    add_filter( 'woocommerce_payment_gateways', 'iste_tos_pos_add_gateway' );

    add_action( 'woocommerce_init', 'iste_tos_pos_register_response_endpoints' );
}
add_action( 'plugins_loaded', 'iste_tos_pos_init' );

function iste_tos_pos_register_response_endpoints(): void {
    $handler = new Iste_Tos_Pos_Response_Handler();
    $handler->register_endpoints();
}

function iste_tos_pos_add_gateway( array $gateways ): array {
    $gateways[] = 'WC_Gateway_Iste_Tos_Pos';
    return $gateways;
}

function iste_tos_pos_woocommerce_missing_notice(): void {
    echo '<div class="notice notice-error"><p>' .
        esc_html__( 'İşte Tos Pos Gateway: WooCommerce eklentisi aktif değil. Lütfen WooCommerce\'i etkinleştirin.', 'iste-tos-pos' ) .
        '</p></div>';
}

function iste_tos_pos_https_notice(): void {
    echo '<div class="notice notice-warning"><p>' .
        esc_html__( 'İşte Tos Pos Gateway: Siteniz HTTPS kullanmıyor. Güvenli ödeme işlemleri için SSL sertifikası gereklidir.', 'iste-tos-pos' ) .
        '</p></div>';
}
