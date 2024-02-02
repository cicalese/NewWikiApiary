<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Query.php
 * Description :
 * Date        : 2-1-2024
 * Time        : 09:44
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\ResponseHandler;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;

class Query {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param string|null $get
	 * @param string|null $from
	 * @param string|null $where
	 * @param string|null $limit
	 * @param string|null $format
	 *
	 * @return mixed
	 */
	public function doQuery( ?string $get, ?string $from, ?string $where, ?string $limit, ?string $format ): mixed {
		if ( $from === null ) {
			ResponseHandler::addMessage( wfMessage( 'w8y_missing-table-argument' )->text() );
			return "";
		}
		if ( !$this->structure->tableExists( $from ) ) {
			ResponseHandler::addMessage( wfMessage( 'w8y_not-a-valid-table', $from )->parse() );
			return "";
		}
		if ( $get === null ) {
			$get = '*';
		} else {
			$get = Utils::checkForMultiple( $get );
		}

		if ( $limit === null ) {
			$limit = 10;
		} else {
			$limit = intval( $limit );
		}

		if ( $format === null ) {
			$format = 'csv';
		}

		$errFound = false;
		if ( $where !== null ) {
			$where = Utils::checkForMultiple( $where, true );
		}
		if ( is_array( $where ) ) {
			foreach ( $where as $k => $v ) {
				if ( !$this->structure->columnExists( $from, $k ) ) {
					$errFound = true;
					ResponseHandler::addMessage( wfMessage( 'w8y_not-a-valid-column', $k, $from )->text() );
				}
			}
		}
		if ( $errFound ) {
			return "";
		}

		return $this->query( $get, $from, $where, $limit, $format );
	}

	/**
	 * @param string|array $select
	 * @param string $table
	 * @param string|array|null $selectWhere
	 * @param int $limit
	 * @param string $format
	 *
	 * @return mixed
	 */
	private function query(
		string|array $select,
		string $table,
		null|string|array $selectWhere,
		int $limit,
		string $format
	): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		$selectOptions = [ 'LIMIT' => $limit ];
		if ( $selectWhere === null ) {
			$selectWhere = '';
		}
		$res = $dbr->select( $table, $select, $selectWhere, __METHOD__, $selectOptions );
		$result = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				if ( $format === 'json' ) {
					foreach ( $row as $k => $v ) {
						if ( !is_int( $k ) ) {
							$result[$t][Structure::w8yMessage( $k )] = $v;
						}
					}
				} else {
					$result[] = $row;
				}
				$t++;
			}
		}
		if ( $format === 'csv' ) {
			return Utils::formatCSV( $result );
		}
		if ( $format === 'json' ) {
			return $result;
		}

		return $result;
	}



}
