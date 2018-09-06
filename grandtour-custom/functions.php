<?php

function gt_custom_enqueue_front_page_scripts()  {
    // Enqueue the JS
    wp_enqueue_script('grandtour-custom-all', get_stylesheet_directory_uri() . '/script.js', false, "", true);
}
add_action( 'wp_enqueue_scripts', 'gt_custom_enqueue_front_page_scripts' );

/**
*	Setup add product to cart function
**/
add_action('wp_ajax_gt_custom_add_to_cart', 'gt_custom_add_to_cart');
add_action('wp_ajax_nopriv_gt_custom_add_to_cart', 'gt_custom_add_to_cart');

function gt_custom_add_to_cart() {
	if(isset($_GET['product_id']) && !empty($_GET['product_id']) && class_exists('Woocommerce'))
	{
		$product_ID = $_GET['product_id'];

		//Check if variable product
		$obj_product = wc_get_product($product_ID);
		$woocommerce = grandtour_get_woocommerce();

		if($obj_product->is_type('variable'))
		{
			//Get all product variation
			$args = array(
			 'post_type'     => 'product_variation',
			 'post_status'   => array( 'private', 'publish' ),
			 'numberposts'   => -1,
			 'orderby'       => 'menu_order',
			 'order'         => 'ASC',
			 'post_parent'   => $product_ID
			);
			$variations = get_posts( $args );

			foreach ($variations as $variation)
			{
				//Get variation ID
				$variation_ID = $variation->ID;

				if(isset($_POST[$variation_ID]) && !empty($_POST[$variation_ID]))
				{
					$woocommerce->cart->add_to_cart($product_ID, intval($_POST[$variation_ID]), $variation_ID);
				}
			}
		}
		else
		{
            $quantity = 1;
            if (isset($_POST['quantity'])) {
                $quantity = intval($_POST['quantity']);
            }
			$woocommerce->cart->add_to_cart($product_ID, $quantity);
		}
	}

	die();
}

function gt_custom_deposits_form_output($productID) {
    $product = get_post($productID);
    if ( !is_null($product) && WC_Deposits_Product_Manager::deposits_enabled( $product->ID ) ) {
        wp_enqueue_script( 'wc-deposits-frontend' );
        wc_get_template( 'deposit-form.php', array( 'post' => $product ), 'woocommerce-deposits', WC_DEPOSITS_TEMPLATE_PATH );
    }
}

function gt_custom_get_formatted_deposit_amount( $product_id ) {
    $product = wc_get_product( $product_id );

    if ( $amount = self::get_deposit_amount_for_display( $product ) ) {
        $type    = self::get_deposit_type( $product_id );

        $item = __( 'person', 'woocommerce-deposits' );

        if ( 'percent' === $type ) {
            return sprintf( __( 'Pay a %1$s deposit per %2$s', 'woocommerce-deposits' ), '<span class="wc-deposits-amount">' . $amount . '</span>', $item );
        } else {
            return sprintf( __( 'Pay a deposit of %1$s per %2$s', 'woocommerce-deposits' ), '<span class="wc-deposits-amount">' . $amount . '</span>', $item );
        }
    }
    return '';
}
