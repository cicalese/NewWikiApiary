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
		ResponseHandler::printDebugMessage( $get, "return" );
		ResponseHandler::printDebugMessage( $from, "table" );
		ResponseHandler::printDebugMessage( $where, "where" );
		ResponseHandler::printDebugMessage( $limit, "limit" );
		ResponseHandler::printDebugMessage( $format, "format" );
		if ( $from === null ) {
			ResponseHandler::addMessage( wfMessage( 'w8y_missing-table-argument' )->text() );
			return "";
		}
		if ( !$this->structure->tableExists( $from ) ) {
			ResponseHandler::addMessage( wfMessage( 'w8y_not-a-valid-table', $from )->text() );
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
	 * @param array|null $selectWhere
	 * @param int $limit
	 * @param string $format
	 *
	 * @return mixed
	 */
	private function query(
		string|array $select,
		string $table,
		?array $selectWhere,
		int $limit,
		string $format
	): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		$selectOptions = [ 'LIMIT' => $limit ];
		if ( $selectWhere === null ) {
			$selectWhere = '';
		}
		ResponseHandler::printDebugMessage( $selectWhere, '$selectWhere' );
		ResponseHandler::printDebugMessage( $table, '$table' );
		ResponseHandler::printDebugMessage( $select, '$select' );
		ResponseHandler::printDebugMessage( $selectOptions, '$selectOptions' );
		ResponseHandler::printDebugMessage( $format, '$format' );

		$res = $dbr->select( $table, $select, $selectWhere, __METHOD__, $selectOptions );
		$result = [];
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$result[] = $row;
			}
		}
		if ( $format === 'csv' ) {
			return $this->formatCSV( $result );
		}

		return $result;
	}

	/**
	 * @param array $result
	 *
	 * @return string
	 */
	private function formatCSV( array $result ): string {
		if ( empty( $result ) ) {
			return '';
		}
		$ret = '';
		foreach ( $result as $row ) {
			$ret .= implode( ',', $row ) . ';';
		}
		return $ret;
	}

}
