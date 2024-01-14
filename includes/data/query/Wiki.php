<?php
/**
 * Created by  : Wikibase Solutions B.V.
 * Project     : dev1-03
 * Filename    : Wiki.php
 * Description :
 * Date        : 11-1-2024
 * Time        : 22:05
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\ResponseHandler;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use Wikimedia\Rdbms\DBConnRef;

class Wiki {

	private const DBTABLE_WIKIS = 'w8y_wikis';
	private const DBTABLE_EXTENSIONS = 'w8y_extensions';
	private const DBTABLE_SKINS = 'w8y_skins';
	private const DBTABLE_SCRAPE = 'w8y_scrape_records';
	private const WIKI_PAGEID = 'w8y_wi_page_id';
	private const WIKI_DEFUNCT = 'w8y_wi_is_defunct';
	private const WIKI_LAST_SR_RCRD = 'w8y_wi_last_sr_id';
	private const SR_ID = 'w8y_sr_sr_id';
	private const EXTENSION_SCRAPE_ID = 'w8y_ex_sr_id';
	private const SKIN_SCRAPE_ID = 'w8y_sk_sr_id';

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
		$from = self::DBTABLE_EXTENSIONS;
		$where = [ self::EXTENSION_SCRAPE_ID => $scrapeId ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->
		where( $where )->caller( __METHOD__ )->fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( self::DBTABLE_EXTENSIONS ) as $tName ) {
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
		$from = self::DBTABLE_SKINS;
		$where = [ self::SKIN_SCRAPE_ID => $scrapeId ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->
		where( $where )->caller( __METHOD__ )->fetchResultSet();

		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				foreach ( $this->structure->returnTableColumns( self::DBTABLE_SKINS ) as $tName ) {
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
		$select = [ self::DBTABLE_WIKIS . '.*', self::DBTABLE_SCRAPE . '.*' ];
		$from = self::DBTABLE_WIKIS;
		$where = [ self::DBTABLE_WIKIS . '.' . self::WIKI_PAGEID => $pageId,
			self::DBTABLE_WIKIS . '.' . self::WIKI_DEFUNCT => 0 ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->join( self::DBTABLE_SCRAPE,
			null,
			self::DBTABLE_WIKIS . '.' . self::WIKI_LAST_SR_RCRD . ' = ' . self::DBTABLE_SCRAPE . '.' . self::SR_ID )
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
		/*
		 *
		Wiki - given page ID:
	•	extensions from last scrape (name, version, URL)
	•	skins from last scrape (name, version, URL)
	•	last scrape timestamp or day and time
	•	is alive
	•	MediaWiki version
	•	database type and version
	•	PHP version
	•	language
	•	JSON of general attributes
	•	JSON of statistics
		 */
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );

		// Let's get the wiki and scrape information first

		$result = $this->getWikiAndScrapeRecord( $pageID, $dbr );
		if ( empty( $result ) ) {
			return $result;
		}
		$result['extensions'] = $this->getExtensions( $result['scrape'][self::SR_ID], $dbr );
		$result['skins'] = $this->getSkins( $result['scrape'][self::SR_ID], $dbr );
		switch ( $export ) {
			case "table":
				//ResponseHandler::printDebugMessage( $result, "sql result" );
				return Utils::renderTable( $result, 'Results for ' . Utils::getPageTitleFromID( $pageID ) .
				' ( pageID: ' . $pageID . ' )' );
			case "arrayfunctions":
				//ResponseHandler::printDebugMessage( $result, "sql result" );
				return [ Utils::exportArrayFunction( $result ), 'nowiki' => true ];
			case "lua":
				return $result;
			default:
				return "";
		}
	}
}