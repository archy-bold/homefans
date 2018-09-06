<?php

function grandtour_deposits_form_output($productID) {
    $product = get_post($productID);
    if ( !is_null($product) && WC_Deposits_Product_Manager::deposits_enabled( $product->ID ) ) {
        wp_enqueue_script( 'wc-deposits-frontend' );
        wc_get_template( 'deposit-form.php', array( 'post' => $product ), 'woocommerce-deposits', WC_DEPOSITS_TEMPLATE_PATH );
    }
}
