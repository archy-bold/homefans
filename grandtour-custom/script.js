jQuery(document).ready(function(){
	"use strict";

	jQuery('.single_tour_add_to_cart_custom').on( 'click', function(e) {
		e.preventDefault();
		jQuery(this).attr("disabled","disabled");

		var productID = jQuery(this).attr('data-product');
		var processing = jQuery(this).attr('data-processing');
		var ajaxURL = jQuery(this).attr('data-url');
		var cartURL = jQuery('#tg_cart_url').val();

		jQuery(this).html(processing);

		jQuery.ajax({
			url: ajaxURL,
			data: jQuery('#tour_variable_form').serialize(),
			type:'POST',
			success:function(results) {
				location.href = cartURL;
			}
		});

		return false;
	});
});
