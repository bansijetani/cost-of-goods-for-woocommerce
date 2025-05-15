<?php 
/**
 * Cost of Goods for WooCommerce - Products Class.
 *
 * @version 1.0.0
 * @package CostofGoodsforWooCommerce\classes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_COG_Products' ) ) :

	/**
	 * WC_COG_Products class
	 * 
	 */
	class WC_COG_Products {

		/**
		 * Product profit html template
		 *
		 */
		public $product_profit_html_template;

		/**
		 * WC_COG_Products Constructor
		 */
		public function __construct() {

			$this->product_profit_html_template = apply_filters( 'wc_cog_product_profit_html_template',  __( '%profit%', 'cost-of-goods-for-woocommerce' ) ); 
			$this->wc_cog_add_hooks();
		}

		/**
		 * Initialize hooks
		 * 
		 */
		public function wc_cog_add_hooks(){
			add_filter( 'manage_edit-product_columns', array( $this, 'wc_cog_add_product_columns' ) );
			add_action( 'manage_product_posts_custom_column', array( $this, 'wc_cog_render_product_columns' ), 10, 2 );
			add_action( 'admin_head-edit.php', array( $this, 'wc_cog_product_columns_style' ) );

			// Cost field validation
			add_action( 'admin_enqueue_scripts', array( $this, 'wc_cog_product_js' ) );
		}

		/**
		 * Get product cost	
		 *
		 * @param int $product_id single product id
		 *
		 * @return int product cost value
		 */
		public function wc_cog_get_product_cost( $product_id ) {
			return ( get_post_meta( $product_id, '_wc_cog_cost', true ) ) ? get_post_meta( $product_id, '_wc_cog_cost', true ) : 0 ;
		}

		/**
		 * Get product price
		 *	
		 * @param object $product single product object
		 * @param null $args 
		 *
		 * @return mixed
		 */
		public function wc_cog_get_product_price( $product ) {		
			return ( $product->get_price() ) ? $product->get_price() : 0 ;
		}

		/**
		 * Get product profit
		 *
		 * @param int $product_id single product id
		 *
		 * @return product cost
		 */
		public function wc_cog_get_product_profit( $product_id ) {
			$product = wc_get_product( $product_id );
			return ( '' === ( $cost = $this->wc_cog_get_product_cost( $product_id ) ) || '' === ( $price = $this->wc_cog_get_product_price( $product ) ) ? '' : $price - $cost );
		}

		/**
		 * Get product cost HTML
		 *
		 * @param int $prodyct_id single product id
		 *
		 * @return string cost html
		 */
		public function wc_cog_get_product_cost_html( $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product->is_type( 'variable' ) ) {
				return $this->wc_cog_get_variable_product_html( $product_id, 'cost', '%cost%' );
			} else {
				return ( '' === ( $cost = $this->wc_cog_get_product_cost( $product_id ) ) ? '' : $this->wc_cog_price_html( $cost ) ); 
			}
		}

		/**
		 * Get price HTML
		 *
		 * @param int $price price
		 *
		 * @return string price html with shop currency symbol
		 */
		public function wc_cog_price_html( $price ) {
			return wc_price( $price );
		}

		/**
		 * Get simple product profit html
		 *
		 * @param int $product_id single product id
		 * @param string $template 
		 */
		public function wc_cog_get_product_profit_html( $product_id, $template = '%profit%' ) {
			$product = wc_get_product( $product_id );
			if ( is_a( $product, 'WC_Product' ) ) {
				if ( $product->is_type( 'variable' ) ) {
					return $this->wc_cog_get_variable_product_html( $product_id, 'profit', $template );
				} else {
					if ( '' === ( $profit = $this->wc_cog_get_product_profit( $product_id ) ) ) {
						return '';
					} else {
						$placeholders = array(
							'%profit%' => $this->wc_cog_price_html( $profit ),
						);

						return str_replace( array_keys( $placeholders ), $placeholders, $template );
					}
				}
			} else {
				return '';
			}
		}

		/**
		 * Get variable product profit html
		 *
		 * @param int $product_id single product id
		 * @param string $profit_or_cost
		 * @param string $template 
		 */
		public function wc_cog_get_variable_product_html( $product_id, $profit_or_cost, $template ) {
			$product = wc_get_product( $product_id );
			$data    = array();
			foreach ( $product->get_children() as $variation_id ) {
				$data[ $variation_id ] = ( 'profit' === $profit_or_cost ? $this->wc_cog_get_product_profit( $variation_id ) : $this->wc_cog_get_product_cost( $variation_id ) );
			}
			if ( empty( $data ) ) {
				return '';
			} else {
				asort( $data );
				if ( 'profit' === $profit_or_cost ) {
					$product_ids    = array_keys( $data );
					$product_id_min = current( $product_ids );
					$product_id_max = end(     $product_ids );
				}
				$min = (float) current( $data );
				$max = (float) end( $data );
				$placeholders = array();
				if ( $min !== $max ) {
					$placeholders[ "%{$profit_or_cost}%" ] = wc_format_price_range( $min, $max );
					if ( 'profit' === $profit_or_cost ) {
						$cost_min                         = (float) $this->wc_cog_get_product_cost( $product_id_min );
						$cost_max                         = (float) $this->wc_cog_get_product_cost( $product_id_max );
						$profit_min                       = ( 0 != $cost_min ? $min / $cost_min * 100 : '' );
						$profit_max                       = ( 0 != $cost_max ? $max / $cost_max * 100 : '' );
						$price_min                        = $this->wc_cog_get_product_price( wc_get_product( $product_id_min ) );
						$price_max                        = $this->wc_cog_get_product_price( wc_get_product( $product_id_max ) );
					}
				} else {
					$placeholders[ "%{$profit_or_cost}%" ] = $this->wc_cog_price_html( $min );
					if ( 'profit' === $profit_or_cost ) {
						$cost                             = (float) $this->wc_cog_get_product_cost( $product_id_min );
						$price                            = $this->wc_cog_get_product_price( wc_get_product( $product_id_min ) );
					}
				}
				return str_replace( array_keys( $placeholders ), $placeholders, $template );
				
			}
		}

		/**
		 * Add new column to product listing
		 * 
		 * @param array $columns all products columns array
		 *
		 * @return merged array of product columns
		 */
		public function wc_cog_add_product_columns( $columns ){
			if ( ! apply_filters( 'wc_cog_product_columns_show', true ) ) {
				return $columns;
			}

			$this->product_columns = array();
			$this->product_columns['cost']   = apply_filters( 'wc_cog_product_listing_column_lable' , __( 'Cost', 'cost-of-goods-for-woocommerce' ) );

			$wc_cog_core_functions = new WC_COG_Core_Functions();

			return $wc_cog_core_functions->wc_cog_insert_in_array( $columns, $this->product_columns, 'price' );

		}

		/**
		 * Render column to product listing
		 * 
		 * @param string $column render single column name
		 * @param int $product_id single product id
		 *
		 */
		public function wc_cog_render_product_columns( $column, $product_id ){
			if ( ! apply_filters( 'wc_cog_product_columns_show', true ) ) {
				return;
			}

			if ( 'cost' === $column ) {
				echo ( 'cost' === $column ? $this->wc_cog_get_product_cost_html( $product_id ) : $this->wc_cog_get_product_profit_html( $product_id, $this->product_profit_html_template ) );
			}
		}

		/**
		 * Add product column style
		 * 
		 */		
		public function wc_cog_product_columns_style(){
			global $post_type;			
			if ( 'product' !== $post_type ) {
				return;
			}
			$width_unit         = apply_filters( 'wc_cog_products_columns_width_unit', '%' );
			$cost_width_style   = apply_filters( 'wc_cog_products_columns_cost_width', '10' ); 
			?>
			<style>
				.wp-list-table .column-cost {
					<?php echo 'width:'.$cost_width_style.$width_unit; ?>
				}
			</style>
			
			<?php
		}

		/**
		 * Add cost field validation js
		 * 
		 */	
		public function wc_cog_product_js( $hook ) {
			global $post;

	        if( $post && $post->post_type == 'product' && in_array($hook, array( 'post.php', 'post-new.php' )) ) {
	        	
	            wp_enqueue_script('wc_cog_admin_script', plugin_dir_url( __DIR__ ) . '/assets/js/admin-script.js');

	        }
		}

	}

endif;

return new WC_COG_Products();