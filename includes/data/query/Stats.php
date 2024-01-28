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
	 * @param string $where
	 *
	 * @return array
	 */
	private function getTopSkins( int $limit, DBConnRef $dbr, string $where = '' ): array {
		$select = [ Structure::SKIN_NAME, 'count' => 'count(*)' ];
		$from = Structure::DBTABLE_WIKIS;

		if ( $where !== '' ) {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_SKINS_LINK,	null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_VID )->
			leftJoin( Structure::DBTABLE_SKINS, null, Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_ID . '=' . Structure::DBTABLE_SKINS . '.' . Structure::SKIN_ID )->
			where( $where )->
			groupBy( Structure::SKIN_NAME )->
			orderBy( 'count', 'DESC' )->
			limit( $limit )->
			caller( __METHOD__ )->
			fetchResultSet();
		} else {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_SKINS_LINK,	null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_VID )->
			leftJoin( Structure::DBTABLE_SKINS, null, Structure::DBTABLE_SKINS_LINK . '.' . Structure::SKIN_LINK_ID . '=' . Structure::DBTABLE_SKINS . '.' . Structure::SKIN_ID )->
			groupBy( Structure::SKIN_NAME )->
			orderBy( 'count', 'DESC' )->
			limit( $limit )->
			caller( __METHOD__ )->
			fetchResultSet();
		}
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t]['Count'] = $row['count'];
				$ret[$t]['Name'] = $row[Structure::SKIN_NAME];
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param int $limit
	 * @param DBConnRef $dbr
	 * @param string $where
	 *
	 * @return array
	 */
	private function getTopExtensions( int $limit, DBConnRef $dbr, string $where = '' ): array {
		$select = [ Structure::EXTENSION_NAME, 'count' => 'count(*)' ];
		$from = Structure::DBTABLE_WIKIS;

		if ( $where !== '' ) {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS_LINK,	null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_VID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS, null, Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_ID . '=' . Structure::DBTABLE_EXTENSIONS . '.' . Structure::EXTENSION_ID )->
			where( $where )->
			groupBy( Structure::EXTENSION_NAME )->
			orderBy( 'count', 'DESC' )->
			limit( $limit )->
			caller( __METHOD__ )->
			fetchResultSet();
		} else {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS_LINK, null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_VID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS, null, Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_ID . '=' . Structure::DBTABLE_EXTENSIONS . '.' . Structure::EXTENSION_ID )->
			groupBy( Structure::EXTENSION_NAME )->
			orderBy( 'count', 'DESC' )->
			limit( $limit )->
			caller( __METHOD__ )->
			fetchResultSet();
		}
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t]['Count'] = $row['count'];
				$ret[$t]['Name'] = $row[Structure::EXTENSION_NAME];
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param string $action
	 * @param string $where
	 * @param int $limit
	 * @param string $export
	 *
	 * @return mixed
	 */
	public function doQuery( string $action, string $where, int $limit = 10, string $export = "table" ): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );
		$result = [];
		switch ( $action ) {
			case "extensions":
				$result = $this->getTopExtensions( $limit,
					$dbr, $where );
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
						 array_merge( $tables, [ Structure::w8yMessage( Structure::EXTENSION_NAME ) ] ),
						 true );
				 }
				 if ( $action === "skins" ) {
					 return Utils::renderTable( $result,
						 'Top ' . $limit . ' used skins',
						 array_merge( $tables, [ Structure::w8yMessage( Structure::SKIN_NAME ) ] ),
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