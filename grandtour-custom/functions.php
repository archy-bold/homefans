<?php

function grandtour_custom_enqueue_front_page_scripts()  {
    // Enqueue the JS
    wp_enqueue_script('grandtour-custom-all', get_stylesheet_directory_uri() . '/script.js', false, "", true);
}
add_action( 'wp_enqueue_scripts', 'grandtour_custom_enqueue_front_page_scripts' );

function grandtour_deposits_form_output($productID) {
    $product = get_post($productID);
    if ( !is_null($product) && WC_Deposits_Product_Manager::deposits_enabled( $product->ID ) ) {
        wp_enqueue_script( 'wc-deposits-frontend' );
        wc_get_template( 'deposit-form.php', array( 'post' => $product ), 'woocommerce-deposits', WC_DEPOSITS_TEMPLATE_PATH );
    }
}
