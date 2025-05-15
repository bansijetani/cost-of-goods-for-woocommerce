<?php
/**
 * Cost of Goods for WooCommerce - Functions
 *
 * @version 1.0.0
 * @package CostofGoodsforWooCommerce\classes
 */

if ( ! class_exists( 'WC_COG_Core_Functions' ) ) :

    /**
     * WC_COG_Core_Functions class.
     */
    class WC_COG_Core_Functions {
        
        /**
         * WC_COG_Core_Functions Constructor
         */
        public function __construct() {
            $this->wc_cog_setup_actions();

            // Include required files
			$this->wc_cog_includes();
        }
        
        /**
         * Setting up Hooks
         */
        public function wc_cog_setup_actions() {
            //Main plugin hooks
            register_activation_hook( WC_COG_PLUGIN_DIR, array( $this, 'wc_cog_activate' ) );
            register_deactivation_hook( WC_COG_PLUGIN_DIR, array( $this, 'wc_cog_deactivate' ) );
        }
        
        /**
         * Activate callback
         */
        public function wc_cog_activate() {
            if ( ! $this->is_plugin_activate( 'woocommerce/woocommerce.php' ) ){
            	return false;
            }
        }
        
        /**
         * Deactivate callback
         */
        public function wc_cog_deactivate() {
            //Deactivation code in here
        }

        /**
         * Check for plugin is activate or not
         *
         * @param string $plugin plugin file name
         * @return boolean 
         */
        public function is_plugin_activate( $plugin ){
        	return ( in_array( $plugin, apply_filters( 'wc_cog_active_plugins', get_option( 'active_plugins' ) ) ) );
        }

        /**
		 * Include required core files
		 */
		public function wc_cog_includes() {
            include_once dirname( WC_COG_PLUGIN_DIR ) . '/includes/class-wc-cog-inputs.php';
            include_once dirname( WC_COG_PLUGIN_DIR ) . '/includes/class-wc-cog-products.php';
            include_once dirname( WC_COG_PLUGIN_DIR ) . '/includes/class-wc-cog-orders.php';
		}

        /**
         * Insert array after perticular position
         *
         * @param array $original_array Array in insert
         * @param array $array_to_insert Array to insert
         * @param string $key_to_insert_after Position of element
         * @return array Updated array 
         */
        public function wc_cog_insert_in_array( $original_array, $array_to_insert, $key_to_insert_after ) {
            if ( empty( $array_to_insert ) ) {
                return $original_array;
            }
            $result   = array();
            $is_found = false;
            foreach ( $original_array as $key => $title ) {
                $result[ $key ] = $title;
                if ( $key_to_insert_after === $key ) {
                    $result   = array_merge( $result, $array_to_insert );
                    $is_found = true;
                }
            }
            return ( $is_found ? $result : array_merge( $result, $array_to_insert ) );
        }
        
    }

endif;

return new WC_COG_Core_Functions();