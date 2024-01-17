<?php
/**
 * Created by  : Wikibase Solutions B.V.
 * Project     : dev1-03
 * Filename    : MediaWiki.php
 * Description :
 * Date        : 15-1-2024
 * Time        : 21:36
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use Wikimedia\Rdbms\DBConnRef;

class MediaWiki {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param string $version
	 * @param int $limit
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getMediaWikiVersionInfo( string $version, int $limit, DBConnRef $dbr ): array {
		$explodedVersions = explode( '.', $version );
		$version1 = false;
		$version2 = false;
		$version3 = false;
		$where = false;
		if ( isset( $explodedVersions[0] ) ) {
			$where .= $explodedVersions[0];
		}
		if ( isset( $explodedVersions[1] ) ) {
			$where .= '.' . $explodedVersions[1];
		}
		if ( isset( $explodedVersions[2] ) ) {
			$where .= '.' . $explodedVersions[2];
		}
		$where = [ Structure::SCRAPE_MEDIAWIKI_VERSION => $where ];
		$select = [ Structure::SCRAPE_MEDIAWIKI_VERSION, Structure::SR_ID,
			'count' => 'count(*)' ];
		$from = Structure::DBTABLE_SCRAPE;
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->where( $where )->
		groupBy( Structure::SCRAPE_MEDIAWIKI_VERSION )->orderBy( 'count',
				'DESC' )->limit( $limit )->caller( __METHOD__ )->fetchResultSet();
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t]['Count'] = $row['count'];
				$ret[$t]['version'] = $row[ Structure::SCRAPE_MEDIAWIKI_VERSION ];
				$ret[$t]['sid'] = $row[ Structure::SR_ID ];
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param string $version
	 * @param int $limit
	 * @param string $export
	 *
	 * @return mixed
	 */
	public function doQuery( string $version, int $limit = 10, string $export = "table" ): mixed {
		/*
		 * MediaWiki version - given major version:
	•	also major/minor/special versions associated with major version
	•	list of wikis actively using with version
		 */
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );
		$result = [];

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