<?php
/**
 * Created by  : Wikibase Solutions B.V.
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

class Query {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param string|array $get
	 * @param string $from
	 * @param array|null $where
	 * @return mixed
	 */
	public function doQuery( string|array $get, string $from, ?array $where ): mixed {
		if ( !$this->structure->tableExists( $from ) ) {
			ResponseHandler::addMessage( wfMessage( 'w8y_not-a-valid-table', $from )->text() );
			return "";
		}
		$errFound = false;
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
		return $this->query( $get, $from, $where );
	}

	/**
	 * @param string|array $select
	 * @param string $table
	 * @param array|null $selectWhere
	 * @return array
	 */
	private function query( string|array $select, string $table, ?array $selectWhere ): array {
		$lb          = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr         = $lb->getConnectionRef( DB_REPLICA );

		$selectOptions = [
			'LIMIT'    => 1
		];
		if ( $selectWhere === null ) {
			$selectWhere = '';
		}

		$res = $dbr->select(
			$table,
			$select,
			$selectWhere,
			__METHOD__,
			$selectOptions
		);
		$result = [];
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$result[] = $row;
			}
			return $result;
		} else {
			return [];
		}
	}

}
