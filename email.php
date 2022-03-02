<style>
	div#template_header_image > p img {
    display: none !important;
}
table#addresses {
    margin-top: 30px !important;
}

table#template_container {
    width: 100% !important;
    max-width: 800px !important;
}

table#template_container table#template_body {
    width: 100% !important;
}
table#template_footer {
    display: none !important;
}
</style>
<p><?php
/* translators: 1: first name 2: last name */
printf( __( 'You have received an order from %s %s Check the Details Below 👇👇', 'woocommerce-advanced-notifications' ), version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_first_name : $order->get_billing_first_name(), version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_last_name : $order->get_billing_last_name() );
?></p>

<?php
/**
 * This Data is Coming from ACF Field 
 * @supplier_message
 */
$supply_msg = get_field('supplier_message', $postid);
?>
<div class="card" style="background: #eee9;padding: 2px 9px 10px 9px;margin-bottom: 10px;border: 1px solid #eee;border-radius: 4px;">
    <h3>Message For Suppliyer</h3>
    <p>
        <?php echo $supply_msg; ?>
    </p>
</div>
<h2>
	<?php 
	if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
		$order_edit_url  = admin_url( 'post.php?post=' . $order->id . '&action=edit' );
		$order_date_c 	 = date_i18n( 'c', strtotime( $order->order_date ) );
		$order_date_text = date_i18n( wc_date_format(), strtotime( $order->order_date ) );
	} else {
		$order_edit_url  = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
		$order_date_c 	 = $order->get_date_created()->format( 'c' );
		$order_date_text = wc_format_datetime( $order->get_date_created() );
	}
	?>
	<?php printf( __( 'Order Date:', 'woocommerce-advanced-notifications' ), $order->get_order_number() ); ?> (<?php printf( '<time datetime="%s">%s</time>', $order_date_c, $order_date_text ); ?>)
</h2>


<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'woocommerce-advanced-notifications' ); ?></th>
			<th class="td" scope="col" style="text-align:left;" <?php if ( ! $show_prices ) : ?>colspan="2"<?php endif; ?>><?php esc_html_e( 'Quantity', 'woocommerce-advanced-notifications' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php
		

		foreach ( $order->get_items() as $item_id => $item ) :

			if ( is_callable( array( $item, 'get_product' ) ) ) {
				$_product = $item->get_product();
			} else {
				$_product = $order->get_product_from_item( $item );
			}

			$display = true;

			$product_id = $_product->is_type( 'variation' ) && version_compare( WC_VERSION, '3.0', '>=' ) ? $_product->get_parent_id() : $_product->get_id();

			if ( $triggers['all'] || in_array( $product_id, $triggers['product_ids'] ) || in_array( $_product->get_shipping_class_id(), $triggers['shipping_classes'] ) ) {
				$display = true;
			}

			if ( ! $display ) {

				$cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

				if ( sizeof( array_intersect( $cats, $triggers['product_cats'] ) ) > 0 )
					$display = true;

			}

			if ( ! $display )
				continue;

			$displayed_total += $order->get_line_total( $item, true );

			$item_meta = version_compare( WC_VERSION, '3.0', '<' ) ? new WC_Order_Item_Meta( $item ) : new WC_Order_Item_Product( $item_id );
			?>
			<tr>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php

					// Product name
					echo apply_filters( 'woocommerce_order_product_title', $item['name'], $_product );
                    echo '<br><br><a href='.get_permalink( $_product->get_id() ).' target="blank">Item Link</a>';
					// SKU
					echo $_product->get_sku() ? ' (#' . $_product->get_sku() . ')' : '';

					// allow other plugins to add additional product information here
					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

					// Variation
					if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
						echo $item_meta->meta ? '<br/><small>' . nl2br( $item_meta->display( true, true ) ) . '</small>' : '';
					} else {
						wc_display_item_meta( $item );
					}

					// allow other plugins to add additional product information here
					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
				?></td>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" <?php if ( ! $show_prices ) : ?>colspan="2"<?php endif; ?>><?php echo esc_html( $item['qty'] ) ;?></td>

				<?php if ( $show_prices ) : ?>
					<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?php echo wp_kses( $order->get_formatted_line_subtotal( $item ), $allowed_tags ); ?></td>
				<?php endif; ?>
			</tr>

		<?php endforeach; ?>
	</tbody>

	<tfoot>
		<?php
            if ( $order->get_customer_note() ) {
                ?><tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo $text_align; ?>;"><?php _e( 'Note:', 'woocommerce-advanced-notifications' ); ?></th>
                    <td class="td" style="text-align:<?php echo $text_align; ?>;"><?php echo wptexturize( $order->get_customer_note() ); ?></td>
                </tr><?php
            }
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php
//Customer Shipping Address
$order = new WC_Order( $order->get_id() );
?>


<div class="card" style="
    margin: 16px 0 0 0;
    padding: 10px 12px;
    background: #f4f4f487;
    border: 1px solid #eee;
    border-radius: 5px;
">
	<h2>Shipping Address:</h2>
	<p style="margin-bottom: 0 !important;">Name: <?php echo $order->shipping_first_name . ' ' . $order->shipping_last_name; ?></p>
	<p>Phone: <?php echo $order->billing_phone; ?></p>
	<p style="margin-bottom: 0 !important;">Company: <?php echo $order->shipping_company; ?></p>
	<p style="margin-bottom: 0 !important;">Address: <?php echo $order->shipping_address_1 . ' ' . $order->shipping_address_2; ?></p>
	<p style="margin-bottom: 0 !important;">City: <?php echo $order->shipping_city; ?></p>
	<p style="margin-bottom: 0 !important;">State: <?php echo $order->shipping_state; ?></p>
	<p style="margin-bottom: 0 !important;">Zip/PostCode: <?php echo $order->shipping_postcode; ?></p>
	<p style="margin-bottom: 0 !important;">Country: <?php echo $order->shipping_country; ?></p>
	
	<?php 
		foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
			$shipping_method_title   = $item->get_method_title();
		}
	?>
	<h2 style="margin-top: 20px !important;">🚚 Shipping Method: <?php echo $shipping_method_title; ?></h2>
</div>