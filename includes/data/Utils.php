<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Utils.php
 * Description :
 * Date        : 7-1-2024
 * Time        : 10:38
 */

namespace WikiApiary\data;

class Utils {

	/**
	 * @var array
	 */
	public static array $parameters;

	/**
	 * @param string $option
	 * @param bool $extract
	 * @return string|array
	 */
	public static function checkForMultiple( string $option, bool $extract = false ): string|array {
		$ret = [];
		if ( str_contains( $option, ','	) ) {
			$exploded = array_map( 'trim', explode(
				',',
				$option
			) );
			if ( $extract ) {
				foreach ( $exploded as $item ) {
					if ( str_contains( $item, '=' ) ) {
						$itemExploded = explode( '=', $item );
						$k = trim( $itemExploded[0] );
						$v = trim( $itemExploded[1] );
						$ret[$k] = $v;
					}
				}
			} else {
				$ret = $exploded;
			}
		} elseif ( $extract ) {
			if ( str_contains( $option, '=' ) ) {
				$itemExploded = explode( '=', $option );
				$k = $itemExploded[0];
				$v = $itemExploded[1];
				$ret[$k] = $v;
			}
		} else {
			return $option;
		}
		return $ret;
	}

	/**
	 * @param string $k
	 * @param bool $checkEmpty
	 *
	 * @return mixed
	 */
	public static function getOptionSetting( string $k, bool $checkEmpty = true ): mixed {
		if ( $checkEmpty ) {
			if ( isset( self::$parameters[ $k ] ) && self::$parameters[ $k ] != '' ) {
				return trim( self::$parameters[ $k ] );
			} else {
				return null;
			}
		} else {
			if ( isset( self::$parameters[ $k ] ) ) {
				return true;
			} else {
				return null;
			}
		}
	}
}
