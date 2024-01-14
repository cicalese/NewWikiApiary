<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Wiki.php
 * Description :
 * Date        : 11-1-2024
 * Time        : 22:05
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use Wikimedia\Rdbms\DBConnRef;

class Wiki {



	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param int $scrapeId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getExtensions( int $scrapeId, DBConnRef $dbr ): array {
		$select = [ '*' ];
		$from = Structure::DBTABLE_EXTENSIONS;
		$where = [ Structure::EXTENSION_SCRAPE_ID => $scrapeId ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->
		where( $where )->caller( __METHOD__ )->fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_EXTENSIONS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
				}
				$t++;
			}
		}
		return $ret;
	}

	/**
	 * @param int $scrapeId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getSkins( int $scrapeId, DBConnRef $dbr ): array {
		$select = [ '*' ];
		$from = Structure::DBTABLE_SKINS;
		$where = [ Structure::SKIN_SCRAPE_ID => $scrapeId ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->
		where( $where )->caller( __METHOD__ )->fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( Structure::DBTABLE_SKINS ) as $tName ) {
					$ret[$t][$tName] = $row[$tName];
					$t++;
				}
			}
		}
		return $ret;
	}

	/**
	 * @param int $pageId
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getWikiAndScrapeRecord( int $pageId, DBConnRef $dbr ): array {
		$select = [ Structure::DBTABLE_WIKIS . '.*', Structure::DBTABLE_SCRAPE . '.*' ];
		$from = Structure::DBTABLE_WIKIS;
		$where = [ Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_PAGEID => $pageId,
			Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_DEFUNCT => 0 ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->join( Structure::DBTABLE_SCRAPE,
			null,
			Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . ' = ' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )
			->where( $where )->caller( __METHOD__ )->fetchResultSet();
		$ret = [];
		$result = [];
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$ret[] = (array)$row;
			}
			$ret = $ret[0];
			foreach ( $ret as $k => $v ) {
				switch ( substr( $k,
					0,
					6 ) ) {
					case "w8y_wi":
						$result['wiki'][$k] = $v;
						break;
					case "w8y_sr":
						$result['scrape'][$k] = $v;
						break;
				}
			}
		}
		return $result;
	}

	/**
	 * @param int $pageID
	 * @param string $export
	 *
	 * @return mixed
	 */
	public function doQuery( int $pageID, string $export = "table" ): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		// Let's get the wiki and scrape information first

		$result = $this->getWikiAndScrapeRecord( $pageID, $dbr );
		if ( empty( $result ) ) {
			return $result;
		}
		$result['wiki']['pageTitle'] = Utils::getPageTitleFromID( $pageID );
		$result['extensions'] = $this->getExtensions( $result['scrape'][Structure::SR_ID], $dbr );
		$result['skins'] = $this->getSkins( $result['scrape'][Structure::SR_ID], $dbr );

		return match ( $export ) {
			"table" => Utils::renderTable( $result,
				'Results for ' . $result['wiki']['pageTitle'] . ' ( pageID: ' . $pageID . ' )' ),
			"arrayfunctions" => [ Utils::exportArrayFunction( $result ), 'nowiki' => true ],
			"lua" => $result,
			default => "",
		};
	}
}