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
	private function getTopSkins( int $limit, DBConnRef $dbr ): array {
		$select = [ '*',
			'count' => 'count(*)' ];
		$from = Structure::DBTABLE_SKINS;
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->groupBy( 'w8y_sk_name' )
			->orderBy( 'count',
				'DESC' )->limit( $limit )->caller( __METHOD__ )->fetchResultSet();
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t]['Count'] = $row['count'];
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_SKINS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
				}
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param int $limit
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getTopExtensions( int $limit, DBConnRef $dbr ): array {
		/*
		 * SELECT
    w8y_wikis.w8y_wi_page_id,
    w8y_extensions.*,
    COUNT(
        DISTINCT w8y_extensions.w8y_ex_name
    ) AS count
FROM
    `w8y_wikis`
INNER JOIN w8y_scrape_records ON w8y_wi_last_sr_id = w8y_scrape_records.w8y_sr_sr_id
INNER JOIN w8y_extensions ON w8y_scrape_records.w8y_sr_vr_id = w8y_extensions.w8y_ex_vr_id
GROUP BY
    w8y_extensions.w8y_ex_name
ORDER BY
    COUNT
DESC
    ;
		 */
		$select = [ '*',
			'count' => 'count(*)' ];
		$from = Structure::DBTABLE_EXTENSIONS;
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
				$result = $this->getTopSkins( $limit,
					$dbr );
				break;
		}
		$tables = [ 'count' ];

		switch ( $export ) {
			 case "table":
				 if ( $action === "extensions" ) {
					 return Utils::renderTable( $result,
						 'Top ' . $limit . ' used extensions',
						 array_merge( $tables,
							 $this->structure->returnTableColumns( Structure::DBTABLE_EXTENSIONS ) ),
						 true );
				 }
				 if ( $action === "skins" ) {
					 return Utils::renderTable( $result,
						 'Top ' . $limit . ' used skins',
						 array_merge( $tables,
							 $this->structure->returnTableColumns( Structure::DBTABLE_SKINS ) ),
						 true );
				 }
				 break;
				case "arrayfunctions":
					return [ Utils::exportArrayFunction( $result ), 'nowiki' => true ];
			case "lua":
				return $result;
			default:
				return "";
		}
		return "";
	}
}