<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Iste_Tos_Pos_Hash {

	public static function calculate( array $params, string $store_key ): string {
		
		$keys = array_keys( $params );
		natcasesort( $keys );

		$hash_val = '';

		foreach ( $keys as $key ) {
			$lower_key = strtolower( $key );
			if ( 'hash' === $lower_key || 'encoding' === $lower_key ) {
				continue;
			}

			$escaped_value = self::escape( (string) $params[ $key ] );
			$hash_val     .= $escaped_value . '|';
		}

		$hash_val .= self::escape( $store_key );

		$hex  = hash( 'sha512', $hash_val );
		$hash = base64_encode( pack( 'H*', $hex ) );

		return $hash;
	}

	private static function escape( string $value ): string {
		
		$value = str_replace( '\\', '\\\\', $value );
		$value = str_replace( '|', '\\|', $value );
		return $value;
	}
}
