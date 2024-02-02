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
use WikiApiary\data\query\Extensions;
use WikiApiary\data\query\Query;
use WikiApiary\data\query\Stats;
use WikiApiary\data\query\Wiki;
use WikiApiary\data\ResponseHandler;
use WikiApiary\data\Utils;

class TagHooks {

	/**
	 * @var array
	 */
	private array $parameters;

	/**
	 * @param array $parameters
	 *
	 * @return array|string[]
	 */
	public static function handleIt( array $parameters ): array {
		$action = Utils::getOptionSetting( 'action', true, $parameters );
		$limit = Utils::getOptionSetting( 'limit', true, $parameters );
		$format = Utils::getOptionSetting( 'format', true, $parameters );
		$result = '';
		switch ( $action ) {
			case "extension":
				$eName = Utils::getOptionSetting( 'Extension name', true, $parameters );
				$eType = Utils::getOptionSetting( 'type', true, $parameters );
				$limit = Utils::getOptionSetting( 'limit', true, $parameters );
				if ( $eName === null || $eType === null ) {
					break;
				}
				if ( $limit === null ) {
					$limit = 10;
				} else {
					$limit = intval( trim( $limit ) );
				}
				$extension = new Extensions();
				$result = $extension->doQuery( $eName, $eType, $limit );
				break;
			case "query":
				$query = new Query();
				$get = Utils::getOptionSetting( 'return', true, $parameters );
				$table = Utils::getOptionSetting( 'from', true, $parameters );
				$where = Utils::getOptionSetting( 'where', true, $parameters );
				$result = $query->doQuery( $get,
					$table,
					$where,
					$limit,
					$format );
				break;
			case "wiki":
				$pId = Utils::getOptionSetting( 'pageId', true, $parameters );
				if ( $pId === null ) {
					ResponseHandler::addMessage( wfMessage( 'w8y_missing-page-id' )->text() );
				} else {
					$query = new Wiki();
					if ( $format === null ) {
						$format = 'table';
					}
					$result = $query->doQuery( intval( $pId ), $format );
				}
				break;
			case "stats":
				$type = Utils::getOptionSetting( 'for', true, $parameters );
				$where = Utils::getOptionSetting( 'where', true, $parameters );
				if ( $limit === null ) {
					$limit = 10;
				}
				if ( $where === null ) {
					$where = '';
				}
				if ( $type !== null ) {
					$query = new Stats();
					if ( $format === null ) {
						$format = 'table';
					}
					$result = $query->doQuery( $type, $where, $limit, $format );
				}
				break;
			case "addToDB":
				break;
		}
		if ( !empty( ResponseHandler::getMessages() ) ) {
			return [ 'status' => 'error', 'data' => ResponseHandler::getMessages() ];
		} elseif ( is_array( $result ) && $format !== 'json' ) {
			return [ 'status' => 'error', 'data' => "<pre>" . print_r( $result, true ) . "</pre>" ];
		} else {
			return [ 'status' => 'ok', 'data' => $result ];
		}
	}

	/**
	 * @param Parser &$parser
	 *
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

		$parameters = $this->extractOptions( array_slice( func_get_args(), 1 ) );
		Utils::$parameters = $parameters;
		$result = self::handleIt( $parameters );
		return $result['data'];
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real associative array in form [name] => value.
	 * If no "=" is provided, true is assumed like this: [name] => true.
	 *
	 * @param array $options
	 *
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
