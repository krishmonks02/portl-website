<?php
/**
 * Checkout deposit button
 */

defined( 'ABSPATH' ) || exit;

?>


<tr class="awcdp-deposit-checkout-button">
	<td colspan="2">

		<div class="awcdp-deposits-wrapper "  >
			<div class="awcdp-deposits-option <?php echo ($has_payment_plan == true ? 'awcdp-wide' : '' ); ?>">
				<div class="awcdp-radio pay-deposit">
					<div>
						<input id="awcdp-option-pay-deposit" name="awcdp_deposit_option" type="radio" value="deposit" <?php checked( $default_checked, 'deposit' ); ?> class="awcdp-deposit-radio" >
						<label for="awcdp-option-pay-deposit" class="awcdp-radio-label"><?php echo esc_html($deposit_text); ?></label>
					</div>
					<div class="awcdp-deposits-description" <?php echo wp_kses_post( $display); ?> >
						<?php if( $amount_type == 'payment_plan' ){ ?>
						<?php if ($has_payment_plan && $default_checked == 'deposit') { ?>
							<div class="awcdp-payment-plan">
								<ul>
									<?php
									foreach ($payment_plans as $plan_id => $payment_plan) {
		              	if(empty($selected_plan)) {
											$selected_plan = $plan_id;
										}
										?>
										<li>
											<div class="awcdp-toggle <?php echo ( $selected_plan == $plan_id ? 'awcdp-active' : ''); ?>" >
												<div class="awcdp-tick"></div>
												<div class="awcdp-plan-title">
													<div class="awcdp-plan-label"><?php echo $payment_plan['name']; ?> </div>

													<input type="radio" value="<?php echo $plan_id; ?>" <?php checked($selected_plan, $plan_id); ?> class="awcdp-plan-radio" name="awcdp-selected-plan"/>
												</div>
											</div>
											<?php
		                  if ($selected_plan == $plan_id) {
			                  $display_plan  = WC()->cart->deposit_info['payment_schedule'];
			                  ?>
												<div class="awcdp-plan-details awcdp-deposit-checkout" >
													<div class="awcdp-plan-deposit" ><?php echo $deposit_text; ?> : <?php echo wc_price(WC()->cart->deposit_info['deposit_amount']); ?></div>
													<p class="awcdp-plan-description" ><?php echo $payment_plan['description']; ?></p>
													<table>
														<thead>
															<th><?php _e('Payment Date', 'deposits-partial-payments-for-woocommerce') ?></th>
															<th><?php _e('Amount', 'deposits-partial-payments-for-woocommerce') ?></th>
														</thead>
														<tbody>
															<?php
															$payment_timestamp = current_time('timestamp');
		                          	foreach ($display_plan as $payment_timestamp => $plan_line) {
																?>
																	<tr>
																			<td><?php echo date_i18n(get_option('date_format'), $payment_timestamp) ?></td>
																			<td><?php echo wc_price($plan_line['total']); ?></td>
																	</tr>
																<?php
																}
														?>
														</tbody>
													</table>
											</div>
										<?php } ?>
										</li>
									<?php
								}
								?>
								</ul>
							</div>
						<?php }  ?>
						<?php } else { ?>
							<?php echo esc_html( $deposit_option_text); ?>
							<?php if ( $amount_type === 'percent') {
								?><span id='awcdp-deposit-amount'><?php echo wp_kses_post( wc_price($deposit_amount)); ?></span><?php
							} else {
								?> <span id='awcdp-deposit-amount'><?php echo wp_kses_post( wc_price($deposit_amount)); ?></span><?php
							} ?>
						<?php } ?>
					</div>
				</div>
				<div class="awcdp-radio pay-full" <?php echo $hide_full; ?> >
					<input id="awcdp-option-pay-full" name="awcdp_deposit_option" value="full" type="radio" <?php checked( $default_checked, 'full' ); ?> class="awcdp-deposit-radio" <?php echo $disbld; ?> >
					<label for="awcdp-option-pay-full" class="awcdp-radio-label"><?php echo esc_html($full_text); ?></label>
				</div>
			</div>
		</div>

	</td>
</tr>
<?php
