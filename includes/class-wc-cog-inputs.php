<?php
/**
 * Cost of Goods for WooCommerce - input fields 
 *
 * @version 1.0.0
 * @package CostofGoodsforWooCommerce\classes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_COG_Inpute' ) ) :

	/**
	 * WC_COG_Inpute class.
	 */
    class WC_COG_Inpute {

    	/**
		 * Cost field label
		 *
		 * @var string
		 */
		public $cost_field_label;

    	/**
		 * WC_COG_Inpute Constructor
		 */
        public function __construct() {
			$this->wc_cog_init_hooks();
		}

		/**
		 * Initialize all hooks
		 */
		public function wc_cog_init_hooks(){
			add_action( 'woocommerce_product_options_pricing', array( $this, 'wc_cog_add_cost_input' ) );
			add_action( 'save_post_product', array( $this, 'wc_cog_save_cost_input' ) );

			add_action( 'woocommerce_variation_options_pricing', array( $this, 'wc_cog_add_cost_input_variation' ), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'wc_cog_save_cost_input_variation' ), 10, 2 );

			$this->cost_field_label = apply_filters( 'wc_cog_product_cost_field_label', sprintf( __( 'Cost (excl. tax) (%s)', 'cost-of-goods-for-woocommerce' ), '%currency_symbol%' ) );

		}

		/**
		 * Add cost input
		 */
		public function wc_cog_add_cost_input_variable(){
			if ( ( $product = wc_get_product() ) && $product->is_type( 'variable' ) ) {
				echo '<div class="options_group wc_cost_of_product show_if_variable">';
				$this->wc_cog_add_cost_input();
				echo '</div>';
			}
		}

		/**
		 * Add cost input field
		 */
		public function wc_cog_add_cost_input(){
			$product_id = get_the_ID();	
			$wc_cog_products = new WC_COG_Products();

			$profit = $wc_cog_products->wc_cog_get_product_profit_html( $product_id, $wc_cog_products->product_profit_html_template);

			woocommerce_wp_text_input( array(
				'id'          => '_wc_cog_cost',
				'value'       => wc_format_localized_price( $wc_cog_products->wc_cog_get_product_cost( $product_id ) ),
				'data_type'   => 'price',
				'label'       => str_replace( '%currency_symbol%', $this->wc_cog_get_default_shop_currency_symbol(), $this->cost_field_label ),
				'description' => apply_filters( 'wc_cog_product_cost_field_description', sprintf( __( 'Profit: %s', 'cost-of-goods-for-woocommerce' ), ( $profit ) ? $profit : __( 'N/A', 'cost-of-goods-for-woocommerce' ) ) ),
			) );
			do_action( 'wc_cog_cost_input_simple', $product_id );
		}

		/**
		 * Add cost input in variation
		 *
		 * @param int $loop variation cost occurence
		 * @param array $variation_data array of variation cost value
		 * @param object $variation single variation object
		 */
		public function wc_cog_add_cost_input_variation( $loop, $variation_data, $variation ) {
			$wc_cog_products = new WC_COG_Products();

			if ( ! isset( $variation_data['_wc_cog_cost'][0] ) || empty( $value = $variation_data['_wc_cog_cost'][0] ) ) {
				$product           = wc_get_product( $variation->ID );
				$parent_product_id = $product->get_parent_id();
				$value             = $wc_cog_products->wc_cog_get_product_cost( $parent_product_id );
			}

			$hook_data = array(
				'variation_id'   => $variation->ID,
				'value'          => $value,
				'variation_data' => $variation_data,
				'loop'           => $loop,
			);

			$profit = $wc_cog_products->wc_cog_get_product_profit_html( $variation->ID, $wc_cog_products->product_profit_html_template);

			woocommerce_wp_text_input( array(
				'id'            => "variable_wc_cog_cost_{$loop}",
				'name'          => "variable_wc_cog_cost[{$loop}]",
				'value'         => wc_format_localized_price( $value ),
				'label'         => str_replace( '%currency_symbol%', $this->wc_cog_get_default_shop_currency_symbol(), $this->cost_field_label ),
				'data_type'     => 'price',
				'wrapper_class' => 'form-row form-row-full',
				'description'   => apply_filters( 'wc_cog_product_cost_field_description', sprintf( __( 'Profit: %s', 'cost-of-goods-for-woocommerce' ), ( $profit ) ? $profit : __( 'N/A', 'cost-of-goods-for-woocommerce' ) ) ),
			) );
			do_action( 'wc_cog_cost_input_variation', $hook_data );
		}

		/**
		 * Save cost input
		 *
		 * @param int $product_id single product id
		 * @todo save cost value to product meta for simple product
		 */
		public function wc_cog_save_cost_input( $product_id ) {
			if ( isset( $_POST['_wc_cog_cost'] ) ) {
				update_post_meta( $product_id, '_wc_cog_cost', wc_clean( $_POST['_wc_cog_cost'] ) );
			}
		}

		/**
		 * Save cost input variation
		 *
		 * @param int $variation_id single variation id
		 * @param int $i variation cost occurence
		 * @todo save cost value to product meta for variable product
		 */
		public function wc_cog_save_cost_input_variation( $variation_id, $i ) {
			if ( isset( $_POST['variable_wc_cog_cost'][ $i ] ) ) {
				update_post_meta( $variation_id, '_wc_cog_cost', wc_clean( $_POST['variable_wc_cog_cost'][ $i ] ) );
			}
		}

		/**
		 * Get default shop currency symbol
		 *
		 * @return string WC currency symbol
		 */
		public function wc_cog_get_default_shop_currency_symbol() {
			return get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) );
		}

    }

endif;

return new WC_COG_Inpute();