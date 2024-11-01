<?php
/*
 * Plugin Name: Wechat Crossborder for WooCommerce
 * Plugin URI: https://www.wpweixin.net/product/2407.html
 * Description:跨境微信支付,官方直连,支持扫码支付和退款功能。若需要企业版本(支持公众号支付)，请访问<a href="https://www.wpweixin.net/product/2407.html" target="_blank">https://www.wpweixin.net/product/2407.html</a>
 * Version: 1.0.0
 * Author: 迅虎网络
 * Author URI:http://www.wpweixin.net
 * Text Domain: Wechat Crossborder for WooCommerce
 * WC tested up to: 3.4.3
 */
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

if (! defined ( 'XH_WC_WEIXINPAY' )) {define ( 'XH_WC_WEIXINPAY', 'XH_WC_WEIXINPAY' );} else {return;}
define('XH_WC_WeChat_VERSION','1.0.0');
define('XH_WC_WeChat_ID','xhwechatwcpaymentgateway' /*'xh-wechat'*/);
define('XH_WC_WeChat_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
define('XH_WC_WeChat_URL',rtrim(plugin_dir_url(__FILE__),'/'));
load_plugin_textdomain( 'wechatpay', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'xh_wechat_wc_payment_gateway_plugin_edit_link' );
add_action( 'init', 'xh_wechat_wc_payment_gateway_init' );

register_activation_hook ( __FILE__, function(){
    global $wpdb;
    $wpdb->query(
       "update {$wpdb->prefix}postmeta
        set meta_value='xh-wechat'
        where meta_key='_payment_method'
        and meta_value='xhwechatwcpaymentgateway';");
});

if(!function_exists('xh_wechat_wc_payment_gateway_init')){
    function xh_wechat_wc_payment_gateway_init() {
        if( !class_exists('WC_Payment_Gateway') )  return;
        require_once XH_WC_WeChat_DIR .'/class-xh-wechat-wc-payment-gateway.php';
        $api = new XHWechatWCPaymentGateway();

        $api->check_wechatpay_response();

        add_filter('woocommerce_payment_gateways',array($api,'woocommerce_wechatpay_add_gateway' ),10,1);
        add_action( 'wp_ajax_XH_WECHAT_PAYMENT_GET_ORDER', array($api, "get_order_status" ) );
        add_action( 'wp_ajax_nopriv_XH_WECHAT_PAYMENT_GET_ORDER', array($api, "get_order_status") );
        add_action( 'woocommerce_receipt_'.$api->id, array($api, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $api->id, array ($api,'process_admin_options') ); // WC >= 2.0
        add_action( 'woocommerce_update_options_payment_gateways', array ($api,'process_admin_options') );
        add_action( 'wp_enqueue_scripts', array ($api,'wp_enqueue_scripts') );
    }
}

function xh_wechat_wc_payment_gateway_plugin_edit_link( $links ){
    return array_merge(
        array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.XH_WC_WeChat_ID) . '">'.__( 'Settings', 'wechatpay' ).'</a>'
        ),
        $links
    );
}
?>
