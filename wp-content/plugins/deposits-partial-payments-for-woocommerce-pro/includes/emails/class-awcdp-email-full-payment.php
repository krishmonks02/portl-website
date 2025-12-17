<?php
/**
 * Full Payment email
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AWCDP_Email_Full_Payment' ) ) {

	class AWCDP_Email_Full_Payment extends WC_Email {

		public function __construct() {
			$this->id          = 'awcdp_full_payment';
			$this->title       = __( 'Acowebs Full Payment', 'deposits-partial-payments-for-woocommerce' );
			$this->description = __( 'This Emails will be sent when an order is completely paid', 'deposits-partial-payments-for-woocommerce' );

			$awwlm_es = get_option('woocommerce_awcdp_full_payment_settings');
			$pp_subject = ( isset($awwlm_es['subject']) && $awwlm_es['subject'] != '' ) ? $awwlm_es['subject'] : __( '[{site_title}] Order completely paid ({order_number}) - {order_date}', 'deposits-partial-payments-for-woocommerce' );
			$pp_heading = ( isset($awwlm_es['heading']) && $awwlm_es['heading'] != '' ) ? $awwlm_es['heading'] : __( 'Order payment completed', 'deposits-partial-payments-for-woocommerce' );
			$pp_recipient = ( isset($awwlm_es['recipient']) ) ? $awwlm_es['recipient'] : get_option('admin_email');

			$this->heading = $pp_heading;
			$this->subject = $pp_subject;

			$this->template_html  = 'emails/admin-order-fully-paid.php';
			$this->template_plain = 'emails/plain/admin-order-fully-paid.php';

			// Triggers for this email.
			add_action('woocommerce_order_status_partially-paid_to_processing_notification', array($this, 'trigger'));
			add_action('woocommerce_order_status_partially-paid_to_completed_notification', array($this, 'trigger'));

			// Call parent constructor.
			parent::__construct();
			$this->template_base = AWCDP_PLUGIN_PATH.'/templates/';

			// Other settings.
			$this->recipient = $pp_recipient;
			if ( ! $this->recipient ) {
				$this->recipient = get_option('admin_email');
			}
			// $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		}


		function trigger( $order_id ){
			if( $order_id ){
				$this->object = wc_get_order( $order_id );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			}

			if( ! $this->is_enabled() || ! $this->get_recipient() ){
				return;
			}

			$this->send( $this->get_recipient() , $this->get_subject() , $this->get_content() , $this->get_headers() , $this->get_attachments() );
		}

		function get_content_html(){

			ob_start();
			wc_get_template(
					$this->template_html ,
					array(
						'order' => $this->object,
						'email_heading' => $this->get_heading(),
						'additional_content' => version_compare( WOOCOMMERCE_VERSION, '3.7.0' ,'<') ?'' : $this-> get_additional_content(),
						'sent_to_admin' => true,
						'plain_text' => false,
						'email' => $this
					),
					'',
					$this->template_base
				);
				return ob_get_clean();
		}

		function get_content_plain(){

			ob_start();
			wc_get_template(
				$this->template_plain,
				array(
					'order' => $this->object,
					'email_heading' => $this->get_heading(),
					'additional_content' => version_compare( WOOCOMMERCE_VERSION, '3.7.0' ,'<') ?'' : $this->get_additional_content(),
					'sent_to_admin' => true,
					'plain_text' => true,
					'email' => $this
				),
				'',
				$this->template_base
			);
			return ob_get_clean();

		}

		function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title' => esc_html__( 'Enable/Disable', 'deposits-partial-payments-for-woocommerce' ),
					'type' => 'checkbox',
					'label' => esc_html__( 'Enable this email notification' , 'deposits-partial-payments-for-woocommerce' ),
					'default' => 'yes'
				) ,
				'recipient' => array(
					'title' => esc_html__( 'Recipient(s)', 'deposits-partial-payments-for-woocommerce' ),
					'type' => 'text',
					'description' => sprintf( wp_kses(__( 'Email recipients (comma separated). Defaults to <code>%s</code>.' , 'deposits-partial-payments-for-woocommerce' ),array('code'=> array())) , esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default' => get_option( 'admin_email' ),
				) ,
				'subject' => array(
					'title' => esc_html__( 'Subject', 'deposits-partial-payments-for-woocommerce' ),
					'type' => 'text',
					'description' => sprintf( wp_kses( __( 'Email subject. Leave blank to use the default subject: <code>%s</code>.' , 'deposits-partial-payments-for-woocommerce' ), array('code'=>array())), $this->subject ),
					'placeholder' => $this->subject,
					'default' => $this->subject,
				) ,
				'heading' => array(
					'title' => esc_html__( 'Email Heading' , 'deposits-partial-payments-for-woocommerce' ),
					'type' => 'text',
					'description' => sprintf( wp_kses(__( 'Main heading contained within the email. Leave blank to use the default heading: <code>%s</code>.', 'deposits-partial-payments-for-woocommerce' ), array('code'=>array())), $this->heading ),
					'placeholder' => $this->heading,
					'default' => $this->heading
				) ,
				'email_type' => array(
					'title' => esc_html__( 'Email type' , 'deposits-partial-payments-for-woocommerce' ),
					'type' => 'select',
					'default' => 'html',
					'class' => 'email_type',
					'options' => array(
						'plain' => esc_html__( 'Plain text' , 'deposits-partial-payments-for-woocommerce' ),
						'html' => esc_html__( 'HTML' , 'deposits-partial-payments-for-woocommerce' ),
						'multipart' => esc_html__( 'Multipart' , 'deposits-partial-payments-for-woocommerce' ),
					)
				)
			);
		}

	}
}


return new AWCDP_Email_Full_Payment();
