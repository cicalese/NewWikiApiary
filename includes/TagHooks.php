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

use MediaWiki\MediaWikiServices;
use Parser;
use WikiApiary\data\query\Query;
use WikiApiary\data\ResponseHandler;
use WikiApiary\data\Utils;

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
		// First set global debug status
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if ( $config->has( 'WikiApiary' ) ) {
			$w8yConfig = $config->get( 'WikiApiary' );
			DBHooks::$debug = $w8yConfig['debug'];
		} else {
			DBHooks::$debug = false;
		}

		$result = '';
		Utils::$parameters = $this->extractOptions(
			array_slice(
				func_get_args(),
				1
			)
		);
		$action = Utils::getOptionSetting( 'action' );

		switch ( $action ) {
			case "query":
				$query = new Query();
				$get = Utils::getOptionSetting( 'return' );
				$table = Utils::getOptionSetting( 'from' );
				$limit = Utils::getOptionSetting( 'limit' );
				$format = Utils::getOptionSetting( 'format' );
				$where = Utils::getOptionSetting( 'where' );
				$result = $query->doQuery( $get, $table, $where, $limit, $format );
				ResponseHandler::printDebugMessage( $result, "sql result" );
				break;
			case "addToDB":
				break;
		}
		if ( !empty( ResponseHandler::getMessages() ) ) {
			return ResponseHandler::getMessages();
		} elseif ( is_array( $result ) ) {
			return "<pre>" . print_r( $result, true ) . "</pre>";
		} else {
			return $result;
		}
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real associative array in form [name] => value.
	 * If no "=" is provided, true is assumed like this: [name] => true.
	 * @param array $options
	 * @return array
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
}
