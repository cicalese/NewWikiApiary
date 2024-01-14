<?php
/**
 * Created by  : Open CSP.
 * Project     : WikiApiary
 * Filename    : Stats.php
 * Description :
 * Date        : 14-1-2024
 * Time        : 21:11
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use Wikimedia\Rdbms\DBConnRef;

class Stats {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param int $limit
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getTopExtensions( int $limit, DBConnRef $dbr ): array {
		$select = [ '*',
			'count' => 'count(*)' ];
		$from = Structure::DBTABLE_EXTENSIONS;
		$selectOptions = [ 'GROUP BY' => 'w8y_ex_name',
			'ORDER BY' => 'count DESC',
			'LIMIT' => $limit ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->groupBy( 'w8y_ex_name' )
			->orderBy( 'count',
				'DESC' )->limit( $limit )->caller( __METHOD__ )->fetchResultSet();
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t]['Count'] = $row['count'];
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_EXTENSIONS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
				}
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param string $action
	 * @param int $limit
	 * @param string $export
	 *
	 * @return mixed
	 */
	public function doQuery( string $action, int $limit = 10, string $export = "table" ): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );
		$result = [];
		switch ( $action ) {
			case "extensions":
				$result = $this->getTopExtensions( $limit,
					$dbr );
				break;
			case "skins":
				break;
		}
		$tables = [ 'count' ];

		return match ( $export ) {
			"table" => Utils::renderTable( $result,
				'Top ' . $limit . ' used extensions',
				array_merge( $tables,
					$this->structure->returnTableColumns( Structure::DBTABLE_EXTENSIONS ) ),
				true ),
			"arrayfunctions" => [ Utils::exportArrayFunction( $result ),
				'nowiki' => true ],
			"lua" => $result,
			default => "",
		};
	}

}