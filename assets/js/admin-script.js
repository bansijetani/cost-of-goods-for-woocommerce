/*!
 * admin-script.js v1.0.0
 *
 */

jQuery(document).ready(function(){
	var old_cost = jQuery('._wc_cog_cost_field #_wc_cog_cost').val();
	jQuery(document).on('input','._wc_cog_cost_field #_wc_cog_cost ', function(){

		var regular_price = ( jQuery('#_regular_price').val() != '') ? jQuery('#_regular_price').val() : 0 ;
		var sale_price    = ( jQuery('#_sale_price').val() != '') ? jQuery('#_sale_price').val() : 0 ;
		wc_cog_cost_error_msg( sale_price, regular_price, jQuery(this), old_cost );

	});

	const variation_interval = setInterval( wc_cog_variation_init, 1000 );
	function wc_cog_variation_init(){

		if( jQuery('.woocommerce_variation').length > 0 ){
			
			jQuery('.woocommerce_variation').each(function(i){
				var old_cost = jQuery('#variable_wc_cog_cost_'+i).val();
				jQuery(document).on('input','#variable_wc_cog_cost_'+i, function(){

					var regular_price = ( jQuery('#variable_regular_price_'+i).val() != '') ? jQuery('#variable_regular_price_'+i).val() : 0 ;
					var sale_price    = ( jQuery('#variable_sale_price'+i).val() != '') ? jQuery('#variable_sale_price'+i).val() : 0 ;
					wc_cog_cost_error_msg( sale_price, regular_price, jQuery(this), old_cost, i );

				});
			})
			clearInterval( variation_interval );
		}
	}

});


function wc_cog_cost_error_msg( sale_price, regular_price, cost_input, old_cost, loop = null ){
	
	var error_text	  = '';

	if( sale_price == 0 && regular_price == 0) {
		error_text = 'Please enter product price first! ';	
	}
	if( sale_price > 0 && parseInt( cost_input.val() ) > sale_price) {
		error_text = 'Cost value should be greater than sale price! ';			
	}
	if( sale_price == 0 && parseInt( cost_input.val() ) > regular_price) {
		error_text = 'Cost value should be greater than regular price!';			
	}

	if( error_text != '' ){
		var parent_ele = ( loop != null ) ? '.variable_wc_cog_cost_'+ loop +'_field' : '._wc_cog_cost_field' ;
		jQuery('<p class="form-field wc_cog_error_msg"><label></label> <span style="width:100%;background-color:#d82223;color:#fff;padding: 5px 20px">' + error_text + '</span> </p>').insertAfter( cost_input.parents( parent_ele ) ).fadeOut(5000);
				
		cost_input.val(old_cost);
	}
}