<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Iste_Tos_Pos_Response_Handler {

	public function register_endpoints(): void {
		add_action( 'woocommerce_api_iste_tos_pos_return', [ $this, 'handle_response' ] );
		add_action( 'woocommerce_api_iste_tos_pos_callback', [ $this, 'handle_response' ] );
	}

	private function is_test_mode(): bool {
		$settings = get_option( 'woocommerce_iste_tos_pos_settings', [] );
		return isset( $settings['test_mode'] ) && 'yes' === $settings['test_mode'];
	}

	private function log_context(): array {
		return [ 'source' => 'iste-tos-pos' ];
	}

	private function log_debug( string $message ): void {
		
		if ( ! $this->is_test_mode() ) {
			return;
		}

		$logger = function_exists( 'wc_get_logger' ) ? wc_get_logger() : null;
		if ( $logger ) {
			$logger->debug( $message, $this->log_context() );
		}
	}

	private function log_error( string $message ): void {
		$logger = function_exists( 'wc_get_logger' ) ? wc_get_logger() : null;
		if ( $logger ) {
			$logger->error( $message, $this->log_context() );
		}
	}

	public function handle_response(): void {

		$is_callback = str_contains( $_SERVER['REQUEST_URI'] ?? '', 'iste_tos_pos_callback' );

		if ( $is_callback && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			$this->log_error( 'İşte Tos Pos callback handler: GET isteği reddedildi.' );
			status_header( 400 );
			exit( 'Bad Request' );
		}

		try {

			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$raw_data  = ! empty( $_POST ) ? $_POST : $_GET;
			$post_data = array_map( function ( $value ) {
				
				return is_scalar( $value ) ? trim( wp_unslash( $value ) ) : '';
			}, $raw_data );

			$sanitized = array_map( 'sanitize_text_field', $post_data );

			$this->log_debug( 'İşte Tos Pos yanıt alındı (' . $_SERVER['REQUEST_METHOD'] . '): ' . wp_json_encode( $sanitized ) );

			$this->log_debug( 'REQUEST_URI: ' . ( $_SERVER['REQUEST_URI'] ?? 'N/A' ) );
			$this->log_debug( 'GET params: ' . wp_json_encode( $_GET ) );

			$order_id = 0;
			if ( isset( $sanitized['oid'] ) && $sanitized['oid'] ) {
				$order_id = absint( $sanitized['oid'] );
			} elseif ( isset( $_GET['oid'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order_id = absint( $_GET['oid'] );
			}

			if ( ! $order_id ) {
				$this->log_error( 'İşte Tos Pos yanıt handler: Geçersiz veya eksik oid parametresi. Veri: ' . wp_json_encode( $post_data ) );
				status_header( 400 );
				exit( 'Bad Request: Missing order ID' );
			}

			$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;

			if ( ! $order ) {
				$this->log_error( "İşte Tos Pos yanıt handler: Sipariş bulunamadı. OID: {$order_id}" );
				status_header( 400 );
				exit( 'Bad Request: Order not found' );
			}

			$store_key = $this->get_store_key();

			$hash_verified = $this->verify_hash( $post_data, $store_key );

			if ( ! $hash_verified ) {
				
				if ( $order->has_status( 'processing' ) || $order->has_status( 'completed' ) ) {
					$this->log_debug( "HASH başarısız ama sipariş zaten tamamlanmış, yoksayıldı. OID: {$order_id}" );
					$this->redirect( $this->get_thank_you_url( $order ) );
					return;
				}

				$this->log_error( "İşte Tos Pos yanıt handler: HASH doğrulama başarısız. OID: {$order_id}" );
				$this->log_error( "HASH başarısız - Gelen tüm veri: " . wp_json_encode( $post_data ) );
				$this->log_error( "HASH başarısız - Store key uzunluğu: " . strlen( $store_key ) );

				$order->update_status(
					'failed',
					__( 'İşte Tos Pos: HASH doğrulama başarısız — güvenlik uyarısı.', 'iste-tos-pos' )
				);
				$order->add_order_note(
					__( 'GÜVENLİK UYARISI: İşte Tos Pos yanıtındaki HASH değeri doğrulanamadı. Yanıt değiştirilmiş olabilir.', 'iste-tos-pos' )
				);

				$order->update_meta_data( 'iste_tos_pos_hash_verified', 'false' );
				$order->update_meta_data( 'iste_tos_pos_response_raw', wp_json_encode( $post_data ) );
				$order->save();

				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice(
						__( 'Ödeme güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.', 'iste-tos-pos' ),
						'error'
					);
				}
				$this->redirect( wc_get_checkout_url() );
				return;
			}

			$this->update_order_status( $order, $sanitized );

		} catch ( \Throwable $e ) {
			
			$this->log_error( 'handle_response: Beklenmedik hata. Hata: ' . $e->getMessage() );
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice(
					__( 'Ödeme işlemi sırasında beklenmedik bir hata oluştu. Lütfen tekrar deneyin.', 'iste-tos-pos' ),
					'error'
				);
			}
			$this->redirect( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ) );
		}
	}

	public function verify_hash( array $post_data, string $store_key ): bool {
		if ( empty( $post_data['HASH'] ) ) {
			return false;
		}

		$received_hash = $post_data['HASH'];

		$calculated_hash = Iste_Tos_Pos_Hash::calculate( $post_data, $store_key );

		return hash_equals( (string) $calculated_hash, (string) $received_hash );
	}

	public function update_order_status( WC_Order $order, array $data ): void {
		
		if ( $order->has_status( 'processing' ) || $order->has_status( 'completed' ) ) {
			$this->log_debug( 'İşte Tos Pos yanıt handler: Sipariş zaten processing durumunda, yoksayıldı. OID: ' . $order->get_id() );
			$this->redirect( $this->get_thank_you_url( $order ) );
			return;
		}

		$md_status = isset( $data['mdStatus'] ) ? (string) $data['mdStatus'] : '';
		$auth_code = isset( $data['AuthCode'] ) ? (string) $data['AuthCode'] : '';
		$trans_id  = isset( $data['TransId'] ) ? (string) $data['TransId'] : '';
		$err_msg   = isset( $data['ErrMsg'] ) ? (string) $data['ErrMsg'] : '';

		$order->update_meta_data( 'iste_tos_pos_md_status', $md_status );
		$order->update_meta_data( 'iste_tos_pos_auth_code', $auth_code );
		$order->update_meta_data( 'iste_tos_pos_trans_id', $trans_id );
		$order->update_meta_data( 'iste_tos_pos_error_msg', $err_msg );
		$order->update_meta_data( 'iste_tos_pos_response_raw', wp_json_encode( $data ) );
		$order->update_meta_data( 'iste_tos_pos_hash_verified', 'true' );

		$success_statuses = [ '1', '2', '3', '4' ];

		if ( in_array( $md_status, $success_statuses, true ) ) {
			
			$order->payment_complete( $trans_id );
			$order->add_order_note(
				sprintf(
					/* translators: 1: mdStatus, 2: AuthCode, 3: TransId */
					__( 'İşte Tos Pos ödeme başarılı. mdStatus: %1$s, AuthCode: %2$s, TransId: %3$s', 'iste-tos-pos' ),
					$md_status,
					$auth_code,
					$trans_id
				)
			);
			$order->save();

			$this->log_debug( 'İşte Tos Pos ödeme başarılı. OID: ' . $order->get_id() . ", mdStatus: {$md_status}, AuthCode: {$auth_code}" );

			$this->redirect( $this->get_thank_you_url( $order ) );
		} else {
			
			$order->update_status(
				'failed',
				sprintf(
					/* translators: 1: mdStatus, 2: ErrMsg */
					__( 'İşte Tos Pos ödeme başarısız. mdStatus: %1$s, Hata: %2$s', 'iste-tos-pos' ),
					$md_status,
					$err_msg
				)
			);
			$order->save();

			$this->log_error( 'İşte Tos Pos ödeme başarısız. OID: ' . $order->get_id() . ", mdStatus: {$md_status}, ErrMsg: {$err_msg}" );

			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: error message */
						__( 'Ödeme başarısız oldu: %s', 'iste-tos-pos' ),
						! empty( $err_msg ) ? $err_msg : __( 'Bilinmeyen hata', 'iste-tos-pos' )
					),
					'error'
				);
			}
			$this->redirect( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ) );
		}
	}

	private function get_store_key(): string {
		if ( function_exists( 'WC' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['iste_tos_pos'] ) ) {
				return (string) $gateways['iste_tos_pos']->get_option( 'store_key' );
			}
		}

		$settings = get_option( 'woocommerce_iste_tos_pos_settings', [] );
		return isset( $settings['store_key'] ) ? (string) $settings['store_key'] : '';
	}

	private function get_thank_you_url( WC_Order $order ): string {
		if ( function_exists( 'wc_get_endpoint_url' ) ) {
			return $order->get_checkout_order_received_url();
		}
		return home_url( '/?order-received=' . $order->get_id() );
	}

	private function redirect( string $url ): void {
		if ( function_exists( 'wp_redirect' ) ) {
			wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		} else {
			header( 'Location: ' . $url );
		}
		
		if ( ! defined( 'ISBANK_TESTING' ) || ! ISBANK_TESTING ) {
			exit;
		}
	}
}
