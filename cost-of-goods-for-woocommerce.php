<?php
/**
 *	Plugin Name: Cost of Goods for WooCommerce
 *	Description: Save product purchase costs (cost of goods) in WooCommerce.
 *	Version: 1.0.0
 *	Text Domain: cost-of-goods-for-woocommerce
 *
 *  @package CostofGoodsforWooCommerce
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WC_COG_PLUGIN_DIR',  __FILE__ );

// Include the main WC COG class.
include_once dirname( WC_COG_PLUGIN_DIR ) . '/includes/class-wc-cog-core-functions.php';
