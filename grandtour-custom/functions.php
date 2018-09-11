<?php

if ($_GET['testing'] == 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    add_action( 'wp_loaded', 'gt_custom_test' );
}

function gt_custom_test () {
    // Do whatever here to test
    exit;
}

define( 'GT_CUSTOM_THEME_PATH', untrailingslashit( get_template_directory() ) . '/' );

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
        wc_get_template( 'woocommerce-deposits/templates/deposit-form.php', array( 'post' => $product ) );
    }
}

function gt_custom_get_formatted_deposit_amount( $product_id ) {
    $product = wc_get_product( $product_id );

    if ( $amount = WC_Deposits_Product_Manager::get_deposit_amount_for_display( $product ) ) {
        $type    = WC_Deposits_Product_Manager::get_deposit_type( $product_id );

        $item = __( 'person', 'woocommerce-deposits' );

        if ( 'percent' === $type ) {
            return sprintf( __( 'Pay a %1$s deposit per %2$s', 'woocommerce-deposits' ), '<span class="wc-deposits-amount">' . $amount . '</span>', $item );
        } else {
            return sprintf( __( 'Pay a deposit of %1$s per %2$s', 'woocommerce-deposits' ), '<span class="wc-deposits-amount">' . $amount . '</span>', $item );
        }
    }
    return '';
}

// Add order item product description
add_action('woocommerce_order_item_meta_end', 'gt_custom_render_product_description', 10, 3);
function gt_custom_render_product_description($item_id, $item, $order){
    $_product = $order->get_product_from_item( $item );
    echo "<br>" . apply_filters('the_content', $_product->post->post_content);
}

add_action('woocommerce_email_after_order_table', 'gt_custom_render_additional_order_notes', 10, 3);
function gt_custom_render_additional_order_notes($order){
    // Get the totals in.
    $orderManager = WC_Deposits_Order_Manager::get_instance();
    $totalRows = $order->get_order_item_totals();
    $totalRows = $orderManager->woocommerce_get_order_item_totals($totalRows, $order);
    // Get the due date from the first order item product.
    $dueDate = null;
    $items = $order->get_items();
    foreach ($items as $key => $item) {
        $product = wc_get_product( $item['product_id'] );
        $dueDate = get_post_meta($product->get_id(), 'balance_due_date', true);
        if ($dueDate) {
            break;
        }
    }

    echo '<div style="margin-bottom: 40px;">';

    echo '<p>Thank You for your business.</p>';

    if (WC_Deposits_Order_Manager::has_deposit($order)) {
        echo '<p>Deposit payment (' . $totalRows['order_total']['value'] . ') is non-refundable.</p>';
        echo '<p>Please proceed with pending payment (' . $totalRows['future']['value'] . ') ';
        if ($dueDate) {
            echo 'by ' . date('F j, Y', strtotime($dueDate)) . '. ';
        }
        else {
            echo 'a month before the trip. ';
        }
        echo 'We will send you a payment link closer to the date.</p>';
    }

    echo '</div>';
}
