<?php
//Add data for recently view tours
grandtour_set_recently_view_tours();

/**
 * The main template file for display single post page.
 *
 * @package WordPress
*/

get_header();

//Include custom header feature
get_template_part("/templates/template-tour-header");

$page_tagline = get_the_excerpt();
?>

    <div class="inner">

    	<!-- Begin main content -->
    	<div class="inner_wrapper">

	    	<div class="sidebar_wrapper">

	    		<div class="sidebar_top"></div>

	    		<div class="sidebar">

	    			<div class="content">

		    			<?php
							//Get tour price
							$tour_price = get_post_meta($post->ID, 'tour_price', true);

							if(!empty($tour_price))
							{
								$tour_discount_price = get_post_meta($post->ID, 'tour_discount_price', true);
						?>
						<div class="single_tour_header_price">
							<div class="single_tour_price">
								<?php
								if(!empty($tour_discount_price))
								{
								?>
									<span class="normal_price">
										<?php echo esc_html(grandtour_format_tour_price($tour_price)); ?>
									</span>
									<?php echo esc_html(grandtour_format_tour_price($tour_discount_price)); ?>
								<?php
								}
								else
								{
								?>
									<?php echo esc_html(grandtour_format_tour_price($tour_price)); ?>
								<?php
								}
								?>
							</div>
							<div class="single_tour_per_person">
								<?php esc_html_e('Per Person', 'grandtour' ); ?>
							</div>
						</div>
						<?php
							}
						?>

						<?php
							//Get tour booking method
			    			$tour_booking_method = get_post_meta($post->ID, 'tour_booking_method', true);
						?>
	    				<div class="single_tour_booking_wrapper themeborder <?php echo esc_attr($tour_booking_method); ?>">
		    				<?php
			    				//Check how to display booking form
			    				switch($tour_booking_method)
			    				{
				    				case 'contact_form7':
				    				default:
				    					$tour_booking_contactform7 = get_post_meta($post->ID, 'tour_booking_contactform7', true);
										echo do_shortcode('[contact-form-7 id="'.esc_attr($tour_booking_contactform7).'"]');
				    				break;

				    				case 'woocommerce_product':
				    					$tour_booking_product = get_post_meta($post->ID, 'tour_booking_product', true);
                                        $tour_availability = get_post_meta($post->ID, 'tour_availability', true);
				    		?>
				    				<div class="single_tour_booking_woocommerce_wrapper">
					    				<?php
						    				$obj_product = wc_get_product($tour_booking_product);

						    				if(class_exists('Woocommerce') && !empty($tour_booking_product))
						    				{
							    				$obj_product = wc_get_product($tour_booking_product);

							    				if($obj_product->is_type('simple'))
							    				{
						    			?>
                                        <form id="tour_variable_form">
                                            <div class="tour_quantity">
                                                <input type="number" name="quantity" id="<?php echo esc_attr('quantity_' . $variation_ID); ?>" class="input-text qty text" step="1" min="1" max="<?php echo $tour_availability; ?>" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" aria-labelledby="" />
                                                <?php esc_html_e("Person / People", 'grandtour-custom'); ?>
                                            </div>
                                            <?php grandtour_deposits_form_output($tour_booking_product); ?>
                                        </form>
						    			<?php esc_html_e("Click button below to book this tour and make a payment.", 'grandtour'); ?>
                                        <button data-product="<?php echo esc_attr($tour_booking_product); ?>" data-processing="<?php esc_html_e("Please wait...", 'grandtour'); ?>" data-url="<?php echo admin_url('admin-ajax.php').esc_attr("?action=grandtour_custom_add_to_cart&product_id=".$tour_booking_product); ?>" class="single_tour_add_to_cart_custom button"><?php esc_html_e("Book This Tour", 'grandtour'); ?></button>
						    			<?php
							    				} //end simple product
							    				else if($obj_product->is_type('variable'))
							    				{
								    	?>
								    	<form id="tour_variable_form">
								    	<?php
								    				//Get all product variation
								    				$args = array(
														'post_type'     => 'product_variation',
														'post_status'   => array( 'private', 'publish' ),
														'numberposts'   => -1,
														'orderby'       => 'menu_order',
														'order'         => 'ASC',
														'post_parent'   => $tour_booking_product
													);
													$variations = get_posts( $args );

													foreach ($variations as $variation)
													{

														//Get variation ID
														$variation_ID = $variation->ID;

														//Get variations meta
														$product_variation = new WC_Product_Variation($variation_ID);

														//Get variation title & price
														$variation_price = $product_variation->get_price();
														$variation_price_html = $product_variation->get_price_html();

														$variation_title = wc_get_formatted_variation($product_variation->get_variation_attributes(), true, false);
											?>

											<div class="tour_product_variable_wrapper">
												<div class="tour_product_variable_title">
													<?php echo esc_html($variation_title); ?>
												</div>
												<div class="tour_product_variable_qty">
													<input type="number" name="<?php echo esc_attr($variation_ID); ?>" id="<?php echo esc_attr($variation_ID); ?>" value="0"/>
												</div>
												&nbsp;x&nbsp;
												<div id="tour_product_variable_price_<?php echo esc_attr($variation_ID); ?>" class="tour_product_variable_price" data-price="<?php echo esc_attr($variation_price); ?>">
													<?php echo $variation_price_html; ?>
												</div>
											</div>

											<?php
													}
                                                    grandtour_deposits_form_output($tour_booking_product);
											?>
								    	</form>

										<button data-product="<?php echo esc_attr($tour_booking_product); ?>" data-processing="<?php esc_html_e("Please wait...", 'grandtour'); ?>" data-url="<?php echo admin_url('admin-ajax.php').esc_attr("?action=grandtour_custom_add_to_cart&product_id=".$tour_booking_product); ?>" class="single_tour_add_to_cart_custom product_variable button"><?php esc_html_e("Book This Tour", 'grandtour'); ?></button>

								    		<?php
							    				} //end variable product
							    				else
							    				{
								    	?>
                                        <form id="tour_variable_form">
                                            <div class="tour_quantity">
                                                <input type="number" name="quantity" id="<?php echo esc_attr('quantity_' . $variation_ID); ?>" class="input-text qty text" step="1" min="1" max="<?php echo $tour_availability; ?>" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" aria-labelledby="">
                                                <?php esc_html_e("Person / People", 'grandtour-custom'); ?>
                                            </div>
                                            <?php grandtour_deposits_form_output($tour_booking_product); ?>
                                        </form>
								    	<?php esc_html_e("Click button below to book this tour and make a payment.", 'grandtour'); ?>
						    			<button data-product="<?php echo esc_attr($tour_booking_product); ?>" data-processing="<?php esc_html_e("Please wait...", 'grandtour'); ?>" data-url="<?php echo admin_url('admin-ajax.php').esc_attr("?action=grandtour_custom_add_to_cart&product_id=".$tour_booking_product); ?>" class="single_tour_add_to_cart_custom button"><?php esc_html_e("Book This Tour", 'grandtour'); ?></button>
								    	<?php
							    				}
							    		?>
				    				</div>
				    		<?php
					    					}
				    				break;

				    				case 'external':
				    					$tour_booking_url = get_post_meta($post->ID, 'tour_booking_url', true);
				    		?>
				    				<div class="single_tour_booking_external_wrapper">
				    					<?php esc_html_e("Click button below to begin booking", 'grandtour'); ?>&nbsp;<?php the_title(); ?>
				    					<a href="<?php echo esc_url($tour_booking_url); ?>" class="button" target="_blank"><?php esc_html_e("Book This Tour", 'grandtour'); ?></a>
				    				</div>
				    		<?php
				    				break;
			    				}

			    				$tour_view_count = grandtour_get_post_view($post->ID, true);
			    				if($tour_view_count > 0)
			    				{
				    		?>
				    		<div class="single_tour_view_wrapper themeborder">
				    			<div class="single_tour_view_desc">
					    			<?php esc_html_e("This tour's been viewed", 'grandtour'); ?>&nbsp;<?php echo number_format($tour_view_count); ?>&nbsp;<?php esc_html_e("times in the past week", 'grandtour'); ?>
				    			</div>

				    			<div class="single_tour_view_icon ti-alarm-clock"></div>
				    		</div>
				    		<br class="clear"/>
				    		<?php
			    				}
			    			?>
	    				</div>

	    				<?php
		    				//Check if enable tour sharing
							$tg_tour_single_share = kirki_get_option('tg_tour_single_share');

							if(!empty($tg_tour_single_share))
							{
		    			?>
	    				<a id="single_tour_share_button" href="javascript:;" class="button ghost themeborder"><span class="ti-email"></span><?php esc_html_e("Share this tour", 'grandtour'); ?></a>
	    				<?php
		    				}
		    			?>

	    				<?php
							if (is_active_sidebar('single-tour-sidebar')) { ?>
			    	    	<ul class="sidebar_widget">
			    	    	<?php dynamic_sidebar('single-tour-sidebar'); ?>
			    	    	</ul>
			    	    <?php } ?>

	    				<?php
		    				if (function_exists('users_online') && !isset($_COOKIE['grandtour_users_online'])): ?>
						   <div class="single_tour_users_online_wrapper themeborder">
							   <div class="single_tour_users_online_icon">
								   	<span class="ti-info-alt"></span>
							   </div>
							   <div class="single_tour_users_online_content">
							   		<?php users_online(); ?>
							   </div>
						   </div>
						<?php endif; ?>

	    			</div>

	    		</div>
	    		<br class="clear"/>

	    		<div class="sidebar_bottom"></div>
	    	</div>

    		<div class="sidebar_content">

				<h1><?php the_title(); ?></h1>
				<?php
					//Display tour tagline
			    	if(!empty($page_tagline))
			    	{
			    ?>
			    	<div class="page_tagline">
			    		<?php echo nl2br($page_tagline); ?>
			    	</div>
			    <?php
			    	}

			    	//Display tour attributes
			    	$tour_days = get_post_meta($post->ID, 'tour_days', true);
					$tour_minimum_age = get_post_meta($post->ID, 'tour_minimum_age', true);
			    	$tour_months = get_post_meta($post->ID, 'tour_months', true);
			    	$tour_availability = get_post_meta($post->ID, 'tour_availability', true);

			    	if(!empty($tour_days) OR !empty($tour_minimum_age) OR !empty($tour_months) OR !empty($tour_availability))
			    	{
				?>
					<div class="single_tour_attribute_wrapper themeborder">
						<?php
							if(!empty($tour_days))
							{
						?>
							<div class="one_fourth">
								<div class="tour_attribute_icon ti-time"></div>
								<div class="tour_attribute_content">
								<?php
									//Display tour durations
									echo grandtour_get_tour_duration($post->ID);
								?>
								</div>
							</div>
						<?php
							}
						?>

						<?php
							if(!empty($tour_minimum_age))
							{
						?>
							<div class="one_fourth">
								<div class="tour_attribute_icon ti-id-badge"></div>
								<div class="tour_attribute_content">
									<?php esc_html_e("Age", 'grandtour'); ?>
									<?php echo intval($tour_minimum_age).'+'; ?>
								</div>
							</div>
						<?php
							}
						?>

						<?php
							if(!empty($tour_months))
							{
						?>
							<div class="one_fourth">
								<div class="tour_attribute_icon ti-calendar"></div>
								<div class="tour_attribute_content">
									<?php
										if(is_array($tour_months))
										{
											if(count($tour_months) == 12)
											{
												echo esc_html__("All Months", 'grandtour');
											}
											else
											{
												$i = 0;
												$len = count($tour_months);
												foreach($tour_months as $tour_month)
												{
													echo date_i18n("M", strtotime("1 ".$tour_month." 2017"));

													if ($i != $len - 1)
													{
														echo ',&nbsp;';
													}

													$i++;
												}
											}
										}
									?>
								</div>
							</div>
						<?php
							}
						?>

						<?php
							if(!empty($tour_availability))
							{
						?>
							<div class="one_fourth last">
								<div class="tour_attribute_icon ti-user"></div>
								<div class="tour_attribute_content">
									<?php esc_html_e("Availability", 'grandtour'); ?>
									<?php echo intval($tour_availability); ?>
								</div>
							</div>
						<?php
							}
						?>
					</div><br class="clear"/>
				<?php
			    	}

			    	if (have_posts()) : while (have_posts()) : the_post();
			    ?>
			    	<div class="single_tour_content">
				    	<?php the_content(); ?>
			    	</div>
			    <?php endwhile; endif; ?>

			    <?php
				    //Display tour departure information
				    $tour_departure = get_post_meta($post->ID, 'tour_departure', true);
				    $tour_departure_time = get_post_meta($post->ID, 'tour_departure_time', true);
				    $tour_return_time = get_post_meta($post->ID, 'tour_return_time', true);
				    $tour_included = get_post_meta($post->ID, 'tour_included', true);
				    $tour_not_included = get_post_meta($post->ID, 'tour_not_included', true);
				    $tour_map_address = get_post_meta($post->ID, 'tour_map_address', true);
				?>
				<ul class="single_tour_departure_wrapper themeborder">
					<?php
						if(!empty($tour_departure))
						{
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Departure", 'grandtour'); ?></div>
						<div class="single_tour_departure_content"><?php echo esc_html($tour_departure); ?></div>
					</li>
					<?php
						}
					?>

					<?php
						if(!empty($tour_departure_time))
						{
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Departure Time", 'grandtour'); ?></div>
						<div class="single_tour_departure_content"><?php echo esc_html($tour_departure_time); ?></div>
					</li>
					<?php
						}
					?>

					<?php
						if(!empty($tour_return_time))
						{
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Return Time", 'grandtour'); ?></div>
						<div class="single_tour_departure_content"><?php echo esc_html($tour_return_time); ?></div>
					</li>
					<?php
						}
					?>

					<?php
						if(!empty($tour_included))
						{
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Included", 'grandtour'); ?></div>
						<div class="single_tour_departure_content">
							<?php
								if(!empty($tour_included) && is_array($tour_included))
								{
									foreach($tour_included as $key => $tour_included_item)
									{
										$last_class = '';
										if(($key+1)%2 == 0)
										{
											$last_class = 'last';
										}
							?>
							<div class="one_half <?php echo esc_attr($last_class); ?>">
								<span class="ti-check"></span><?php echo esc_html($tour_included_item); ?>
							</div>
							<?php
									}
								}
							?>
						</div>
					</li>
					<?php
						}
					?>

					<?php
						if(!empty($tour_included))
						{
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Not Included", 'grandtour'); ?></div>
						<div class="single_tour_departure_content">
							<?php
								if(!empty($tour_not_included) && is_array($tour_not_included))
								{
									foreach($tour_not_included as $key => $tour_not_included_item)
									{
										$last_class = '';
										if(($key+1)%2 == 0)
										{
											$last_class = 'last';
										}
							?>
							<div class="one_half <?php echo esc_attr($last_class); ?>">
								<span class="ti-close"></span><?php echo esc_html($tour_not_included_item); ?>
							</div>
							<?php
									}
								}
							?>
						</div>
					</li>
					<?php
						}
					?>

					<?php
						if(!empty($tour_map_address))
						{
							$tg_tour_map_marker = kirki_get_option('tg_tour_map_marker');
					?>
					<li>
						<div class="single_tour_departure_title"><?php esc_html_e("Maps", 'grandtour'); ?></div>
						<div class="single_tour_departure_content"><?php echo do_shortcode('[tg_map width="1000" height="300" address="'.esc_attr($tour_map_address).'" zoom="13" marker="'.esc_url($tg_tour_map_marker).'"]'); ?></div>
					</li>
					<?php
						}
					?>
				</ul>

				<?php
					//Check if enable tour review
					$tg_tour_single_review = kirki_get_option('tg_tour_single_review');

					//Display tour comment
					if (comments_open($post->ID) && !empty($tg_tour_single_review))
					{
				?>
					<div class="fullwidth_comment_wrapper sidebar">
						<?php comments_template( '', true ); ?>
					</div>
				<?php
					}
				?>

    	</div>

    </div>
    <!-- End main content -->

    <?php
	    $tg_tour_display_related = kirki_get_option('tg_tour_display_related');

	    if(!empty($tg_tour_display_related))
	    {

		$tags = wp_get_object_terms($post->ID, 'tourtag');

		if($tags) {

		    $tag_in = array();

		  	//Get all tags
		  	foreach($tags as $tags)
		  	{
		      	$tag_in[] = $tags->term_id;
		  	}

		  	$args=array(
		  		  'tax_query' => array(
					    array(
					        'taxonomy' => 'tourtag',
					        'field' => 'id',
					        'terms' => $tag_in
					    )
					),
		      	  'post_type' => 'tour',
		      	  'post__not_in' => array($post->ID),
		      	  'showposts' => 3,
		      	  'ignore_sticky_posts' => 1,
		      	  'orderby' => 'rand'
		  	 );
		  	$my_query = new WP_Query($args);
		  	$i_post = 1;

		  	if( $my_query->have_posts() ) {
	 ?>
	 	<br class="clear"/>
	  	<div class="tour_related">
		<h3 class="sub_title"><?php echo esc_html_e('Similar Tours', 'grandtour' ); ?></h3>
	<?php
		if (have_posts())
		{
	?>
		<div id="portfolio_filter_wrapper" class="gallery classic three_cols portfolio-content section content clearfix" data-columns="3">
	    <?php
	       while ($my_query->have_posts()) : $my_query->the_post();

	       $image_url = '';
			$tour_ID = get_the_ID();

			if(has_post_thumbnail($tour_ID, 'grandtour-gallery-grid'))
			{
			    $image_id = get_post_thumbnail_id($tour_ID);
			    $small_image_url = wp_get_attachment_image_src($image_id, 'grandtour-gallery-grid', true);
			}

			$permalink_url = get_permalink($tour_ID);
	    ?>
	    <div class="element grid classic3_cols animated<?php echo esc_attr($key+1); ?>">
			<div class="one_third gallery3 classic static filterable portfolio_type themeborder" data-id="post-<?php echo esc_attr($key+1); ?>">
				<?php
					if(!empty($small_image_url[0]))
					{
				?>
						<a class="tour_image" href="<?php echo esc_url($permalink_url); ?>">
							<img src="<?php echo esc_url($small_image_url[0]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
							<?php
								//Get tour price
								$tour_price = get_post_meta($post->ID, 'tour_price', true);

								if(!empty($tour_price))
								{
									$tour_discount_price = get_post_meta($post->ID, 'tour_discount_price', true);
							?>
							<div class="tour_price <?php if(!empty($tour_discount_price)) { ?>has_discount<?php } ?>">
								<?php
								if(!empty($tour_discount_price))
								{
								?>
									<span class="normal_price">
										<?php echo esc_html(grandtour_format_tour_price($tour_price)); ?>
									</span>
									<?php echo esc_html(grandtour_format_tour_price($tour_discount_price)); ?>
								<?php
								}
								else
								{
								?>
									<?php echo esc_html(grandtour_format_tour_price($tour_price)); ?>
								<?php
								}
								?>
							</div>
							<?php
								}
							?>
		                </a>

						<div class="portfolio_info_wrapper">
	        			    <a class="tour_link" href="<?php echo esc_url($permalink_url); ?>"><h4><?php the_title(); ?></h4></a>
	        			    <div class="tour_excerpt"><?php the_excerpt(); ?></div>
	        			    <div class="tour_attribute_wrapper">
		        			    <?php
			        				$overall_rating_arr = grandtour_get_review($tour_ID, 'overall_rating');
									$overall_rating = intval($overall_rating_arr['average']);
									$overall_rating_count = intval($overall_rating_arr['count']);

									if(!empty($overall_rating))
									{
							?>
									<div class="tour_attribute_rating">
							<?php
										if($overall_rating > 0)
										{
							?>
										<div class="br-theme-fontawesome-stars-o">
											<div class="br-widget">
							<?php
											for( $i=1; $i <= $overall_rating; $i++ ) {
												echo '<a href="javascript:;" class="br-selected"></a>';
											}

											$empty_star = 5 - $overall_rating;

											if(!empty($empty_star))
											{
												for( $i=1; $i <= $empty_star; $i++ ) {
													echo '<a href="javascript:;"></a>';
												}
											}
								?>
											</div>
										</div>
								<?php
										}

										if($overall_rating_count > 0)
										{
								?>
										<div class="tour_attribute_rating_count">
											<?php echo intval($overall_rating_count); ?>&nbsp;
											<?php
												if($overall_rating_count > 1)
												{
													echo esc_html__('reviews', 'grandtour' );
												}
												else
												{
													echo esc_html__('review', 'grandtour' );
												}
											?>
										</div>
								<?php
										}
								?>
									</div>
								<?php
									}
			        			?>

			        			<?php
				        			$tour_days = get_post_meta($tour_ID, 'tour_days', true);

				        			if(!empty($tour_days))
				        			{
				        		?>
			        			    <div class="tour_attribute_days">
				        			    <span class="ti-time"></span>
				        			    <?php
											echo intval($tour_days).'&nbsp;';

											if($tour_days > 1)
											{
												echo esc_html__("days", 'grandtour');
											}
											else
											{
												echo esc_html__("day", 'grandtour');
											}
										?>
			        			    </div>
			        			<?php
				        			}
				        		?>
	        			    </div>
	        			    <br class="clear"/>
					    </div>
				<?php
					}
				?>
			</div>
		</div>
	    <?php
	     		$i_post++;
		 		endwhile;

		 		}

		 		wp_reset_postdata();
	    ?>
	    </div>
	  	</div>
	<?php
	  	}
	}
	    } //end if show related
	?>

</div>
<br class="clear"/>
</div>
<?php get_footer(); ?>
