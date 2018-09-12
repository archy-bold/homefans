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

// Scheduler to send balance emails out
if ( ! wp_next_scheduled( 'gt_custom_balance_emails_task_hook' ) ) {
    $time = strtotime('midnight', time());
    wp_schedule_event( time(), 'daily', 'gt_custom_balance_emails_task_hook' );
}
add_action( 'gt_custom_balance_emails_task_hook', 'gt_custom_send_balance_emails' );
function gt_custom_send_balance_emails() {
    // Get the orders in status of partially paid
    $orders = wc_get_orders( array(
        'limit' => -1,
        'post_status' => array('wc-partial-payment'),
    ) );
    var_dump(count($orders));
    foreach ($orders as $order) {
        $item = null;
        $dueDate = gt_custom_get_order_due_date($order, $item);
        // If there's a due date and we're a week before it.
        if ($dueDate) {
            var_dump(strtotime('-1 week', strtotime($dueDate)));
        }
        if ($dueDate && strtotime('-1 week', strtotime($dueDate)) < time()) {
            /// Can't be payment plan
            if (!WC_Deposits_Order_Item_Manager::get_payment_plan( $item )) {
                // Check if we've already sent the order.
                $remaining                  = $item['deposit_full_amount'] - $order->get_line_total( $item, true );
				$remaining_balance_order_id = ! empty( $item['remaining_balance_order_id'] ) ? absint( $item['remaining_balance_order_id'] ) : 0;
				$remaining_balance_paid     = ! empty( $item['remaining_balance_paid'] );

                var_dump($remaining);
                if (// No remaining balance order
                    ($remaining_balance_order_id == 0 || !wc_get_order( $remaining_balance_order_id )) &&
                    // Balance hasn't been paid
                    !$remaining_balance_paid &&
                    // not without future payments
                    ! WC_Deposits_Order_Manager::has_deposit_without_future_payments( $order->get_id() )
                ) {
                    gt_custom_invoice_remaining_balance($order, $item);
                }
            }
        }
    }
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
    $dueDate = gt_custom_get_order_due_date($order);

    echo '<div style="margin-bottom: 40px;">';

    echo '<p>' . __( 'Thank you for your business.', 'grandtour-custom' ) .  '</p>';

    if (WC_Deposits_Order_Manager::has_deposit($order)) {
        $depositTerms1 = sprintf(__('Deposit payment (%s) is non-refundable.', 'grandtour-custom'), $totalRows['order_total']['value']);
        $depositTerms2 = sprintf(__('Please proceed with pending payment (%s) a month before the trip.', 'grandtour-custom'), $totalRows['future']['value']);
        if ($dueDate) {
            $depositTerms2 = sprintf(__('Please proceed with pending payment (%s) by %s.', 'grandtour-custom'), $totalRows['future']['value'], date('F j, Y', strtotime($dueDate)));
        }
        $depositTerms3 = __('We will send you a payment link closer to the date.', 'grandtour-custom');
        echo '<p>' . $depositTerms1 . '</p>';
        echo '<p>' . $depositTerms2 . ' ' . $depositTerms3 . '</p>';
    }

    echo '</div>';
}

add_action( 'woocommerce_email_customer_details', 'gt_custom_email_footer', 50, 4);
function gt_custom_email_footer( $email ) {
    echo '<h4>' . __( 'Terms and Conditions', 'grandtour-custom' ) .  '</h4>';
    $terms1 = __( 'Homefans Ltd is a registered company in England and Wales, Company Number 09737660. By receiving this invoice you agree with Homefans Booking Conditions', 'grandtour-custom' );
    $terms2 = __( 'Should you have any questions, please call Daniel at +447460626600 or send us a message to', 'grandtour-custom' );
    echo '<p style="font-size: 12px;">' . $terms1 . ': ';
    echo '<a href="https://homefans.net/booking-conditions/">https://homefans.net/booking-conditions/</a></p>';
    echo '<p style="font-size: 12px;">' . $terms2;
    echo ' <a href="mailto:daniel@homefans.net">daniel@homefans.net</a></p>';
}

function gt_custom_get_order_due_date($order, &$itemRef = null) {
    $dueDate = null;
    $items = $order->get_items();
    foreach ($items as $key => $item) {
        $product = wc_get_product( $item['product_id'] );
        if ($product) {
            $dueDate = get_post_meta($product->get_id(), 'balance_due_date', true);
            if ($dueDate) {
                $itemRef = $item;
                break;
            }
        }
    }
    return $dueDate;
}

function gt_custom_invoice_remaining_balance($order, $item) {
    $manager = WC_Deposits_Order_Manager::get_instance();
    // Used for products with fixed deposits or percentage based deposits. Not used for payment plan products
    // See WC_Deposits_Schedule_Order_Manager::schedule_orders_for_plan for creating orders for products with payment plans

    // First, get the deposit_full_amount_ex_tax - this contains the full amount for the item excluding tax - see
    // WC_Deposits_Cart_Manager::add_order_item_meta_legacy or add_order_item_meta for where we set this amount
    // Note that this is for the line quantity, not necessarily just for quantity 1
    $full_amount_excl_tax = floatval( $item['deposit_full_amount_ex_tax'] );

    // Next, get the initial deposit already paid, excluding tax
    $amount_already_paid = floatval( $item['deposit_deposit_amount_ex_tax'] );

    // Then, set the item subtotal that will be used in create order to the full amount less the amount already paid
    $subtotal = $full_amount_excl_tax - $amount_already_paid;

    // Add WC3.2 Coupons upgrade compatibility
    if( version_compare( WC_VERSION, '3.2', '>=' ) ){
        // Lastly, subtract the deferred discount from the subtotal to get the total to be used to create the order
        $discount_excl_tax = isset($item['deposit_deferred_discount_ex_tax']) ? floatval( $item['deposit_deferred_discount_ex_tax'] ) : 0;
        $total = $subtotal - $discount_excl_tax;
    } else {
        $discount = floatval( $item['deposit_deferred_discount'] );
        $total = empty( $discount ) ? $subtotal : $subtotal - $discount;
    }
    // And then create an order with this item
    $create_item = array(
        'product'   => $order->get_product_from_item( $item ),
        'qty'       => $item['qty'],
        'subtotal'  => $subtotal,
        'total'     => $total
    );

    $new_order_id = $manager->create_order( current_time( 'timestamp' ), $order->get_id(), 2, $create_item, 'pending-deposit' );

    wc_add_order_item_meta( $item_id, '_remaining_balance_order_id', $new_order_id );

    // Email invoice
    $emails = WC_Emails::instance();
    $emails->customer_invoice( wc_get_order( $new_order_id ) );
}
