<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Iste_Tos_Pos extends WC_Payment_Gateway {

	const TEST_GATE_URL = 'https://istest.asseco-see.com.tr/fim/est3Dgate';

	const LIVE_GATE_URL = 'https://sanalpos.isbank.com.tr/fim/est3Dgate';

	const INSTALLMENT_OPTIONS = [ '1', '2', '3', '6', '9', '12' ];

	public function __construct() {
		$this->id                 = 'iste_tos_pos';
		$this->has_fields         = true;
		$this->method_title       = __( 'İşte Tos Pos', 'iste-tos-pos' );
		$this->method_description = __( 'İşte Tos Pos 3D Pay Hosting ile güvenli ödeme. Kart bilgileriniz İşte Tos Pos\'nın güvenli sayfasında işlenir.', 'iste-tos-pos' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			[ $this, 'process_admin_options' ]
		);

		add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'receipt_page' ] );
	}

	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'      => [
				'title'   => __( 'Etkinleştir/Devre Dışı Bırak', 'iste-tos-pos' ),
				'type'    => 'checkbox',
				'label'   => __( 'İşte Tos Pos ödeme yöntemini etkinleştir', 'iste-tos-pos' ),
				'default' => 'no',
			],
			'title'        => [
				'title'       => __( 'Başlık', 'iste-tos-pos' ),
				'type'        => 'text',
				'description' => __( 'Müşterinin checkout sayfasında göreceği ödeme yöntemi adı.', 'iste-tos-pos' ),
				'default'     => __( 'Kredi/Banka Kartı (İşte Tos Pos)', 'iste-tos-pos' ),
				'desc_tip'    => true,
			],
			'description'  => [
				'title'       => __( 'Açıklama', 'iste-tos-pos' ),
				'type'        => 'textarea',
				'description' => __( 'Müşterinin checkout sayfasında göreceği ödeme yöntemi açıklaması.', 'iste-tos-pos' ),
				'default'     => __( 'İşte Tos Pos güvenli ödeme sayfası üzerinden kredi veya banka kartıyla ödeme yapın.', 'iste-tos-pos' ),
				'desc_tip'    => true,
			],
			'client_id'    => [
				'title'       => __( 'Client ID (Üye İşyeri Numarası)', 'iste-tos-pos' ),
				'type'        => 'text',
				'description' => __( 'İşte Tos Pos tarafından size verilen üye işyeri numarası.', 'iste-tos-pos' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'store_key'    => [
				'title'       => __( 'Store Key (Gizli Anahtar)', 'iste-tos-pos' ),
				'type'        => 'password',
				'description' => __( 'İşte Tos Pos tarafından size verilen gizli anahtar. Bu değer hiçbir zaman HTML çıktısına yazılmaz.', 'iste-tos-pos' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'test_mode'    => [
				'title'       => __( 'Test Modu', 'iste-tos-pos' ),
				'type'        => 'checkbox',
				'label'       => __( 'Test modunu etkinleştir (3D Gate URL otomatik ayarlanır)', 'iste-tos-pos' ),
				'default'     => 'yes',
				'description' => __( 'Test modunda işlemler İşte Tos Pos test ortamına yönlendirilir.', 'iste-tos-pos' ),
				'desc_tip'    => true,
			],
			'gate_url'     => [
				'title'       => __( '3D Gate URL', 'iste-tos-pos' ),
				'type'        => 'text',
				'description' => __( 'İşte Tos Pos 3D ödeme kapısı adresi. Test modu açıkken otomatik olarak test URL\'i kullanılır.', 'iste-tos-pos' ),
				'default'     => self::TEST_GATE_URL,
				'desc_tip'    => true,
			],
			'installments' => [
				'title'       => __( 'İzin Verilen Taksit Seçenekleri', 'iste-tos-pos' ),
				'type'        => 'multiselect',
				'description' => __( 'Checkout sayfasında sunulacak taksit seçeneklerini seçin.', 'iste-tos-pos' ),
				'options'     => [
					'1'  => __( 'Tek Çekim', 'iste-tos-pos' ),
					'2'  => __( '2 Taksit', 'iste-tos-pos' ),
					'3'  => __( '3 Taksit', 'iste-tos-pos' ),
					'6'  => __( '6 Taksit', 'iste-tos-pos' ),
					'9'  => __( '9 Taksit', 'iste-tos-pos' ),
					'12' => __( '12 Taksit', 'iste-tos-pos' ),
				],
				'default'     => [ '1' ],
				'desc_tip'    => true,
			],
		];
	}

	public function process_admin_options(): bool {
		$result = parent::process_admin_options();

		$test_mode = $this->get_option( 'test_mode' );
		if ( 'yes' === $test_mode ) {
			$this->update_option( 'gate_url', self::TEST_GATE_URL );
		} else {
			
			$current_url = $this->get_option( 'gate_url' );
			if ( empty( $current_url ) || self::TEST_GATE_URL === $current_url ) {
				$this->update_option( 'gate_url', self::LIVE_GATE_URL );
			}
		}

		return $result;
	}

	public function is_available(): bool {
		if ( ! parent::is_available() ) {
			return false;
		}

		$client_id = $this->get_option( 'client_id' );
		$store_key = $this->get_option( 'store_key' );

		if ( empty( $client_id ) || empty( $store_key ) ) {
			return false;
		}

		return true;
	}

	public function payment_fields(): void {
		if ( $this->description ) {
			echo '<p>' . wp_kses_post( $this->description ) . '</p>';
		}

		$allowed_installments = $this->get_option( 'installments', [ '1' ] );
		if ( ! is_array( $allowed_installments ) ) {
			$allowed_installments = [ '1' ];
		}

		if ( ! in_array( '1', $allowed_installments, true ) ) {
			array_unshift( $allowed_installments, '1' );
		}

		if ( count( $allowed_installments ) <= 1 ) {
			return;
		}

		$all_labels = [
			'1'  => __( 'Tek Çekim', 'iste-tos-pos' ),
			'2'  => __( '2 Taksit', 'iste-tos-pos' ),
			'3'  => __( '3 Taksit', 'iste-tos-pos' ),
			'6'  => __( '6 Taksit', 'iste-tos-pos' ),
			'9'  => __( '9 Taksit', 'iste-tos-pos' ),
			'12' => __( '12 Taksit', 'iste-tos-pos' ),
		];

		echo '<fieldset id="iste-tos-pos-installment-fieldset">';
		echo '<legend>' . esc_html__( 'Taksit Seçeneği', 'iste-tos-pos' ) . '</legend>';

		foreach ( $allowed_installments as $value ) {
			$value = (string) $value;
			$label = isset( $all_labels[ $value ] ) ? $all_labels[ $value ] : sprintf( __( '%s Taksit', 'iste-tos-pos' ), $value );
			printf(
				'<label style="display:block;margin-bottom:4px;"><input type="radio" name="iste_tos_pos_instalment" value="%s"%s /> %s</label>',
				esc_attr( $value ),
				'1' === $value ? ' checked="checked"' : '',
				esc_html( $label )
			);
		}

		echo '</fieldset>';
	}

	private function get_logger() {
		return function_exists( 'wc_get_logger' ) ? wc_get_logger() : null;
	}

	private function log_context(): array {
		return [ 'source' => 'iste-tos-pos' ];
	}

	private function log_debug( string $message ): void {
		$logger = $this->get_logger();
		if ( ! $logger ) {
			return;
		}
		
		$logger->debug( $message, $this->log_context() );
	}

	private function log_error( string $message ): void {
		$logger = $this->get_logger();
		if ( $logger ) {
			$logger->error( $message, $this->log_context() );
		}
	}

	public function process_payment( $order_id ): array {
		try {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$this->log_error( "process_payment: Sipariş bulunamadı. OID: {$order_id}" );
				wc_add_notice( __( 'Sipariş bulunamadı. Lütfen tekrar deneyin.', 'iste-tos-pos' ), 'error' );
				return [];
			}

			$this->log_debug( "process_payment başlatıldı. OID: {$order_id}" );

			$order->update_status( 'pending', __( 'İşte Tos Pos 3D ödeme başlatıldı.', 'iste-tos-pos' ) );

			$amount = number_format( (float) $order->get_total(), 2, '.', '' );

			$api_url = WC()->api_request_url( 'iste_tos_pos_return' );
			$ok_url       = add_query_arg( 'oid', $order_id, $api_url );
			$fail_url     = add_query_arg( 'oid', $order_id, $api_url );
			$callback_url = WC()->api_request_url( 'iste_tos_pos_callback' );

			$instalment = '';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['iste_tos_pos_instalment'] ) ) {
				$selected = sanitize_text_field( wp_unslash( $_POST['iste_tos_pos_instalment'] ) );
				$allowed  = $this->get_option( 'installments', [] );
				if ( is_array( $allowed ) && in_array( $selected, $allowed, true ) && '1' !== $selected ) {
					$instalment = $selected;
				}
			}

			$rnd    = substr( str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ), 0, 20 );
			$params = [
				'clientid'      => $this->get_option( 'client_id' ),
				'amount'        => $amount,
				'okurl'         => $ok_url,
				'failUrl'       => $fail_url,
				'callbackUrl'   => $callback_url,
				'TranType'      => 'Auth',
				'Instalment'    => $instalment,
				'currency'      => '949',
				'rnd'           => $rnd,
				'storetype'     => '3D_PAY_HOSTING',
				'hashAlgorithm' => 'ver3',
				'lang'          => 'tr',
				'BillToName'    => $this->to_ascii_name( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
				'BillToCompany' => '',
				'refreshtime'   => '5',
				'oid'           => (string) $order->get_id(),
			];

			$store_key      = $this->get_option( 'store_key' );
			$params['HASH'] = Iste_Tos_Pos_Hash::calculate( $params, $store_key );

			$gate_url = $this->get_3dgate_url();

			$this->log_debug( 'process_payment params: ' . wp_json_encode( array_merge( $params, [ 'store_key_len' => strlen( $store_key ) ] ) ) );
			$this->log_debug( "3D Gate'e yönlendiriliyor. OID: {$order_id}, URL: {$gate_url}, Taksit: " . ( $instalment ?: 'tek çekim' ) );

			set_transient( 'iste_tos_pos_params_' . $order_id, [ 'gate_url' => $gate_url, 'params' => $params ], 600 );

			return [
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			];

		} catch ( \Throwable $e ) {
			
			$this->log_error( "process_payment: Beklenmedik hata. OID: {$order_id}, Hata: " . $e->getMessage() );
			wc_add_notice(
				__( 'Ödeme işlemi sırasında beklenmedik bir hata oluştu. Lütfen tekrar deneyin.', 'iste-tos-pos' ),
				'error'
			);
			return [
				'result'   => 'failure',
				'redirect' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
			];
		}
	}

	public function receipt_page( int $order_id ): void {
		$data = get_transient( 'iste_tos_pos_params_' . $order_id );

		if ( ! $data || empty( $data['params'] ) ) {
			
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				echo '<p>' . esc_html__( 'Sipariş bulunamadı.', 'iste-tos-pos' ) . '</p>';
				return;
			}

			$amount       = number_format( (float) $order->get_total(), 2, '.', '' );
			$api_url = WC()->api_request_url( 'iste_tos_pos_return' );
			$ok_url       = add_query_arg( 'oid', $order_id, $api_url );
			$fail_url     = add_query_arg( 'oid', $order_id, $api_url );
			$callback_url = WC()->api_request_url( 'iste_tos_pos_callback' );

			$rnd    = substr( str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ), 0, 20 );
			$params = [
				'clientid'      => $this->get_option( 'client_id' ),
				'amount'        => $amount,
				'okurl'         => $ok_url,
				'failUrl'       => $fail_url,
				'callbackUrl'   => $callback_url,
				'TranType'      => 'Auth',
				'Instalment'    => '',
				'currency'      => '949',
				'rnd'           => $rnd,
				'storetype'     => '3D_PAY_HOSTING',
				'hashAlgorithm' => 'ver3',
				'lang'          => 'tr',
				'BillToName'    => $this->to_ascii_name( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
				'BillToCompany' => '',
				'refreshtime'   => '5',
				'oid'           => (string) $order->get_id(),
			];

			$store_key      = $this->get_option( 'store_key' );
			$params['HASH'] = Iste_Tos_Pos_Hash::calculate( $params, $store_key );
			$gate_url       = $this->get_3dgate_url();
		} else {
			$gate_url = $data['gate_url'];
			$params   = $data['params'];
			delete_transient( 'iste_tos_pos_params_' . $order_id );
		}

		echo $this->build_redirect_form( $gate_url, $params ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function to_ascii_name( string $name ): string {
		
		$ascii_name = remove_accents( $name );
		
		$ascii_name = preg_replace( '/[^\x20-\x7E]/', '', $ascii_name );
		
		return trim( (string) $ascii_name );
	}

	private function build_redirect_form( string $gate_url, array $params ): string {
		$fields = '';
		foreach ( $params as $name => $value ) {
			$fields .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">' . "\n";
		}

		$form_id     = 'iste-tos-pos-redirect-form';
		$button_text = esc_html__( 'İşte Tos Pos Ödeme Sayfasına Git', 'iste-tos-pos' );

		return sprintf(
			'<form id="%s" action="%s" method="POST" style="display:none;">
%s
<noscript><input type="submit" value="%s"></noscript>
</form>
<p>%s</p>
<script>document.getElementById("%s").style.display="block";document.getElementById("%s").submit();</script>',
			esc_attr( $form_id ),
			esc_url( $gate_url ),
			$fields,
			$button_text,
			esc_html__( 'İşte Tos Pos ödeme sayfasına yönlendiriliyorsunuz, lütfen bekleyin...', 'iste-tos-pos' ),
			esc_attr( $form_id ),
			esc_attr( $form_id )
		);
	}

	public function get_3dgate_url(): string {
		$test_mode = $this->get_option( 'test_mode' );
		if ( 'yes' === $test_mode ) {
			return self::TEST_GATE_URL;
		}

		$gate_url = $this->get_option( 'gate_url' );
		return ! empty( $gate_url ) ? $gate_url : self::LIVE_GATE_URL;
	}

	public function get_return_url_ok( WC_Order $order ): string {
		return WC()->api_request_url( 'iste_tos_pos_return' );
	}

	public function get_return_url_fail( WC_Order $order ): string {
		return WC()->api_request_url( 'iste_tos_pos_return' );
	}

	public function get_callback_url( WC_Order $order ): string {
		return WC()->api_request_url( 'iste_tos_pos_callback' );
	}
}
