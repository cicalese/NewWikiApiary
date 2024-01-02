<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : tagHooks.php
 * Description :
 * Date        : 1-1-2024
 * Time        : 22:03
 */

namespace WikiApiary;

use Parser;
use WikiApiary\data\query\Query;
use WikiApiary\data\ResponseHandler;

class TagHooks {

	/**
	 * @var array
	 */
	private array $parameters;

	/**
	 * @param Parser &$parser
	 * @return mixed
	 */
	public function w8y( Parser &$parser ): mixed {
		$result = '';
		$this->parameters = $this->extractOptions(
			array_slice(
				func_get_args(),
				1
			)
		);
		$action = $this->getOptionSetting( 'action' );

		switch ( $action ) {
			case "query":
				$query = new Query();
				$get = $this->checkForMultiple( $this->getOptionSetting( 'return' ) );
				$table = $this->getOptionSetting( 'from' );
				$where = $this->checkForMultiple( $this->getOptionSetting( 'where' ), true );
				$result = print_r( $get, true ) . PHP_EOL;
				$result .= print_r( $table, true ) . PHP_EOL;
				$result .= print_r( $where, true ) . PHP_EOL;
				$query->doQuery( $get, $table, $where );
				break;
			case "addToDB":
				break;
		}
		if ( !empty( ResponseHandler::getMessages() ) ) {
			return ResponseHandler::getMessages();
		} else {
			return "<pre>" . $result . "</pre>";
		}
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real associative array in form [name] => value.
	 * If no "=" is provided, true is assumed like this: [name] => true.
	 *
	 * @param array string $options
	 *
	 * @return array $results
	 */
	private function extractOptions( array $options ): array {
		$results = [];

		foreach ( $options as $option ) {
			$pair = array_map( 'trim',
							   explode( '=',
										$option,
										2 ) );

			if ( count( $pair ) === 2 ) {
				$results[$pair[0]] = $pair[1];
			}

			if ( count( $pair ) === 1 ) {
				$results[$pair[0]] = true;
			}
		}

		return $results;
	}

	/**
	 * @param string $option
	 * @param bool $extract
	 * @return string|array
	 */
	private function checkForMultiple( string $option, bool $extract = false ): string|array {
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
	 * @return bool|mixed
	 */
	private function getOptionSetting( string $k, bool $checkEmpty = true ): mixed {
		if ( $checkEmpty ) {
			if ( isset( $this->parameters[ $k ] ) && $this->parameters[ $k ] != '' ) {
				return trim( $this->parameters[ $k ] );
			} else {
				return false;
			}
		} else {
			if ( isset( $this->parameters[ $k ] ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

}
