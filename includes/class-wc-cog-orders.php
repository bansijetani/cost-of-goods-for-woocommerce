<?php 
/**
 * Cost of Goods for WooCommerce - Order Class.
 *
 * @version 1.0.0
 * @package CostofGoodsforWooCommerce\classes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_COG_Orders' ) ) :

    /**
     * WC_COG_Orders class.
     */
    class WC_COG_Orders {

        /**
         * WC_COG_Orders Constructor
         */
        public function __construct() { 
            $this->wc_cog_add_hooks();
        }

        /**
         * Initialize hooks
         */
        public function wc_cog_add_hooks() {
            // Add cost & profict columns in order listing page
            add_filter( 'manage_edit-shop_order_columns', array( $this, 'wc_cog_add_order_columns' ) );
            add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wc_cog_render_order_columns' ), 10, 2 );

            // Add cost & profit columns to edit order page
            add_action( 'woocommerce_admin_order_item_headers', array( $this, 'wc_cog_admin_order_item_headers' ), 10, 1 );
            add_action( 'woocommerce_admin_order_item_values', array( $this, 'wc_cog_admin_order_item_values' ), 10, 3 );

            // Add Total Cost & Profit to order total section
            add_action('woocommerce_admin_order_totals_after_total', array( $this,'wc_cog_show_order_total_cost'), 10, 1 );
            add_action('woocommerce_admin_order_totals_after_total', array( $this,'wc_cog_show_order_total_profit'), 10, 1 );

            // Add cost and profit in order meta
            add_action('woocommerce_thankyou', array( $this, 'wc_cog_add_cost_profit_to_order_meta' ), 10, 1);

            // Add profit to admin email
            add_action( 'woocommerce_email_order_meta', array( $this, 'wc_cog_add_cost_profit_to_admin_order_email' ), 10, 2 );
        }

        /**
         * Add cost column to order listing page
         * 
         * @param array $columns Default columns array
         */
        public function wc_cog_add_order_columns( $columns ) {
            if ( ! apply_filters( 'wc_cog_order_columns_show', true ) ) {
                return $columns;
            }

            $this->order_columns = array();
            $this->order_columns['cost']    =  apply_filters( 'wc_cog_order_listing_cost_column_lable',  __( 'Cost', 'cost-of-goods-for-woocommerce' ) );
            $this->order_columns['profit']  =  apply_filters( 'wc_cog_order_listing_profit_column_lable',  __( 'Profit', 'cost-of-goods-for-woocommerce' ) );

            $wc_cog_core_functions = new WC_COG_Core_Functions();

            return $wc_cog_core_functions->wc_cog_insert_in_array( $columns, $this->order_columns, 'order_total' );

        }

        /**
         * Render cost column to order listing page
         * 
         * @param string $column Cost 
         * 
         */
        public function wc_cog_render_order_columns( $column, $order_id ) {
            if ( ! apply_filters( 'wc_cog_order_columns_show', true ) ) {
                return;
            }

            if ( in_array( $column, array_keys( $this->order_columns ) ) ) {
                $order_status = ( isset( $this->column_order_status[ $column ] ) ? $this->column_order_status[ $column ] : array() );
                if ( ! empty( $order_status ) && ( ! ( $order = wc_get_order( $order_id ) ) || ! $order->has_status( $order_status ) ) ) {
                    return;
                }
                $key   = '_wc_cog_order_total_' . $column;
                $value = get_post_meta( $order_id, $key, true );
                echo ( '' !== $value ? wc_price( $value ) : '' );
            }
        }

        /**
         * Add cost column to order item table header
         * 
         * @param object $order Order object 
         * 
         */
        public function wc_cog_admin_order_item_headers( $order ) {
            if ( ! apply_filters( 'wc_cog_order_item_columns_show', true ) ) {
                return;
            }
            // Set the column name
            $cost_column    = apply_filters( 'wc_cog_order_listing_cost_column_lable',  __( 'Cost price', 'cost-of-goods-for-woocommerce' ) );
            $profit_column  = apply_filters( 'wc_cog_order_listing_profit_column_lable',  __( 'Profit', 'cost-of-goods-for-woocommerce' ) );
            
            // Display the column name
            echo '<th class="item_cost sortable" data-sort="float">' . $cost_column . '</th>';
            echo '<th class="item_profit sortable" data-sort="float">' . $profit_column . '</th>';
        }

        /**
         * Add cost column to order item table body
         * 
         * @param object $product Product object 
         * @param object $item Single order item
         * @param int $item_id Product id
         * 
         */
        public function wc_cog_admin_order_item_values( $product = null, $item, $item_id ) {
            if ( ! apply_filters( 'wc_cog_order_item_columns_show', true ) ) {
                return;
            }

            if ( $item->get_type() == 'shop_order_refund' ) {
                $item = new WC_Order_Refund( $item_id );
            } else {
                $item = new WC_Order_Item_Product( $item_id );

                // Only for "line_item" items type, to avoid errors
                if ( ! $item->is_type( 'line_item' ) ) return;
            }

            $product         = wc_get_product( $item['product_id'] );

            $wc_cog_products = new WC_COG_Products();
            $cost            = get_post_meta( $item_id, '_wc_cog_order_items_cost', true );
            $cost_html       = $wc_cog_products->wc_cog_price_html( $cost );  
            $profit          = get_post_meta( $item_id, '_wc_cog_order_items_profit', true );
            $profit_html     = $wc_cog_products->wc_cog_price_html( $profit );    

            echo '<td class="item_cost" data-sort-value="' . $cost . '">' . $cost_html . '</td>';
            echo '<td class="item_profit" data-sort-value="' . $profit . '">' . $profit_html . '</td>';
        }

        /**
         * Add cost total to order total table
         * 
         * @param int $order_id Order id
         * 
         */
        public function wc_cog_show_order_total_cost( $order_id  ) {
            if ( ! apply_filters( 'wc_cog_order_total_cost_show', true ) ) {
                return;
            }
            $wc_cog_products = new WC_COG_Products();
            $cost_label      = apply_filters( 'wc_cog_order_total_cost_label', __( 'Total Cost', 'cost-of-goods-for-woocommerce' ) );
            $total_cost      = $wc_cog_products->wc_cog_price_html( get_post_meta( $order_id, '_wc_cog_order_total_cost', true ) ); 
            ?>
                <tr>
                    <td class="label"><?php echo $cost_label; ?>:</td>
                    <td width="1%"></td>
                    <td class="cost-total"><?php echo $total_cost; ?></td>
                </tr>
            <?php

        }

        /**
         * Add profit total to order total table
         * 
         * @param int $order_id Order id
         * 
         */
        public function wc_cog_show_order_total_profit( $order_id  ) {
            if ( ! apply_filters( 'wc_cog_order_total_profit_show', true ) ) {
                return;
            }
            $wc_cog_products = new WC_COG_Products();
            $profit_label    = apply_filters( 'wc_cog_order_total_profit_label', __( 'Total Profit', 'cost-of-goods-for-woocommerce' ) );
            $total_profit    = $wc_cog_products->wc_cog_price_html( get_post_meta( $order_id, '_wc_cog_order_total_profit', true ) );
            ?>
                <tr>
                    <td class="label"><?php echo $profit_label; ?>:</td>
                    <td width="1%"></td>
                    <td class="cost-total"><?php echo $total_profit; ?></td>
                </tr>
            <?php

        }

        /**
         * Add cost and profit to order meta
         *
         * @param int $order_id Order id
         */
        public function wc_cog_add_cost_profit_to_order_meta( $order_id ){
            if ( ! $order_id ) {
                return;
            }
            
            // Allow code execution only once 
            if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

                // Get an instance of the WC_Order object
                $order = wc_get_order( $order_id );
                $wc_cog_products  = new WC_COG_Products();
                $total_order_cost = $total_order_profit = 0;

                foreach ( $order->get_items() as $item_id => $item ) {
                    $product     = $item->get_product(); 
                    if( $product->get_type() == 'variable' ){
                        $product = new WC_Product_Variation( $item->get_variation_id() );
                    }else{
                        $product = $item->get_product();                    
                    }
                    $product_id             = $product->get_id();
                    $quantity               = $item->get_quantity();
                    $cost                   = $wc_cog_products->wc_cog_get_product_cost( $product_id );
                    $item_cost              = $cost;    
                    $product_original_price = $wc_cog_products->wc_cog_get_product_price( $product );
                    $item_profit            = ( ( $product_original_price - $cost ) * $quantity ) - ( $item->get_subtotal() - $item->get_total() ) ;

                    update_post_meta( $item_id, '_wc_cog_order_items_cost', $item_cost );
                    update_post_meta( $item_id, '_wc_cog_order_items_profit', $item_profit );

                    $order_discount         = $order->discount_total;
                    $total_order_cost       += $cost * $quantity ;  
                }

                $total_order_profit     += $order->get_total() - $total_order_cost;
                update_post_meta( $order_id, '_wc_cog_order_total_cost', $total_order_cost );
                update_post_meta( $order_id, '_wc_cog_order_total_profit', $total_order_profit );
            }
        }

        /**
         * Add cost and profit to admin order email
         *
         * @param object $order_obj Order object
         * @param boolean $sent_to_admin True / False
         */
        public function wc_cog_add_cost_profit_to_admin_order_email( $order_obj, $sent_to_admin ) {

            if ( ! $sent_to_admin || ! apply_filters( 'wc_cog_admin_order_email_profit_and_cost_show', true ) || empty( $order_obj->get_id() ) ) {
                return;
            }

            $wc_cog_products  = new WC_COG_Products();
            $order_id         = $order_obj->get_id();
            $cost             = get_post_meta( $order_id, '_wc_cog_order_total_cost', true );
            $profit           = get_post_meta( $order_id, '_wc_cog_order_total_profit', true );
            
            $table_args       = array(
                'table_style'        => 'width:100%;margin-bottom: 40px',
                'table_heading_type' => 'vertical',
                'table_attributes'   => array( 'cellspacing' => 0, 'cellpadding' => 6, 'border' => 1 ),
                'table_class'        => 'td',
                'columns_styles'     => array( 'text-align' => 'right', 'border-left' => 0, 'border-top' => 0 ),
                'columns_classes'    => array( 'td', 'td' ),
            );
            
            $table_data       = array(
                array( apply_filters( 'wc_cog_order_listing_cost_column_lable',  __( 'Total Cost', 'cost-of-goods-for-woocommerce' ) ) , ( $wc_cog_products->wc_cog_price_html( $cost ) ) ),
                array( apply_filters( 'wc_cog_order_listing_profit_column_lable', __( 'Total Profit', 'cost-of-goods-for-woocommerce' ) ), ( $wc_cog_products->wc_cog_price_html( $profit ) ) ),
            );
            
            $cog_meta_heading = apply_filters( 'wc_cog_admin_order_email_meta_table_heading',  __( 'Cost of goods', 'cost-of-goods-for-woocommerce' ) ); 
            ?>
            <h2><?php echo $cog_meta_heading;  ?></h2>
            <?php echo $this->wc_cog_get_table_html( $table_data, $table_args ); 
        }

        /**
         * Get table HTML
         *
         * @param array $data an array of table data
         * @param array $args an array of table style
         */
        public function wc_cog_get_table_html( $data, $args = array() ) {
            $args = array_merge( array(
                'table_class'        => '',
                'table_style'        => '',
                'row_styles'         => '',
                'table_heading_type' => '',
                'columns_classes'    => array(),
                'columns_styles'     => array(),
                'table_attributes'   => array(),
            ), $args );
            // Custom attribute handling.
            $table_attributes = array();
            if ( ! empty( $args['table_attributes'] ) ) {
                foreach ( $args['table_attributes'] as $attribute => $attribute_value ) {
                    $table_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }
            $html = '';
            $html .= '<table' . ( '' == $args['table_class']  ? '' : ' class="' . $args['table_class'] . '"' ) .
                 ' '.implode( ' ', $table_attributes ).
                ( '' == $args['table_style']  ? '' : ' style="' . $args['table_style'] . '"' ) . '>';
            $html .= '<tbody>';
            $row_styles = ( '' == $args['row_styles'] ? '' : ' style="' . $args['row_styles']  . '"' );
            foreach( $data as $row_num => $row ) {
                $html .= '<tr' . $row_styles . '>';
                foreach( $row as $column_num => $value ) {
                    $th_or_td     = ( ( 0 === $row_num && 'horizontal' === $args['table_heading_type'] ) || ( 0 === $column_num && 'vertical' === $args['table_heading_type'] ) ? 'th' : 'td' );
                    $column_class = ( isset( $args['columns_classes'][ $column_num ] ) ? ' class="' . $args['columns_classes'][ $column_num ] . '"' : '' );
                    $column_style = ( isset( $args['columns_styles'][ $column_num ] )  ? ' style="' . $args['columns_styles'][ $column_num ]  . '"' : '' );
                    $html .= '<' . $th_or_td . $column_class . $column_style . '>';
                    $html .= $value;
                    $html .= '</' . $th_or_td . '>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '</table>';
            return $html;
        }

    }

endif;

return new WC_COG_Orders();