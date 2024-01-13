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
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;

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

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}

	/**
	 * @param int $pageID
	 *
	 * @return string
	 */
	public function doQuery( int $pageID ): string {
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


		$select = [ self::DBTABLE_WIKIS . '.*', self::DBTABLE_SCRAPE . '.*' ];
		$from = self::DBTABLE_WIKIS;
		$where = [ self::DBTABLE_WIKIS . '.' . self::WIKI_PAGEID => $pageID,
			self::DBTABLE_WIKIS . '.' . self::WIKI_DEFUNCT => 0 ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->join( self::DBTABLE_SCRAPE,
			null,
			self::DBTABLE_WIKIS . '.' . self::WIKI_LAST_SR_RCRD . ' = ' . self::DBTABLE_SCRAPE . '.' . self::SR_ID )
			->where( $where )->caller( __METHOD__ )->fetchResultSet();
		$ret = [];
		foreach ( $res as $row ) {
			$ret[] = (array)$row;
		}
		$ret = $ret[0];
		$result = [];
		foreach ( $ret as $k => $v ) {
			echo substr( $k, 0, 5 ) . PHP_EOL;
			switch ( substr( $k, 0, 6 ) ) {
				case "w8y_wi":
					$result['wiki'][$k] = $v;
					break;
				case "w8y_sr":
					$result['scrape'][$k] = $v;
					break;
			}
		}

		$select = [ '*' ];
		$from = self::DBTABLE_EXTENSIONS;
		$where = [ self::EXTENSION_SCRAPE_ID => $result['scrape'][self::SR_ID] ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->
		where( $where )->caller( __METHOD__ )->fetchResultSet();

		$ret = [];
		foreach ( $res as $row ) {
			$ret[] = (array)$row;
		}
		$result['extensions'] = $ret;
		echo "<pre>";
		print_r( $result );
		echo "</pre>";
		return "";
		//return Utils::formatCSV( $result );

		/*
		$select = [ self::DBTABLE_WIKIS . '.*', self::DBTABLE_SCRAPE . '.*', self::DBTABLE_EXTENSIONS . '.*' ];
		$from = self::DBTABLE_WIKIS;
		$where = [ self::DBTABLE_WIKIS . '.' . self::WIKI_PAGEID => $pageID ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->leftJoin( self::DBTABLE_SCRAPE,
				null,
				self::DBTABLE_WIKIS . '.' . self::WIKI_LAST_SR_RCRD . ' = ' . self::DBTABLE_SCRAPE . '.' . self::SR_ID )
			->leftJoin( self::DBTABLE_EXTENSIONS,
				null,
				self::DBTABLE_SCRAPE . '.' . self::SR_ID . ' = ' . self::DBTABLE_EXTENSIONS . '.' . self::EXTENSION_SCRAPE_ID
			)->where( $where )->caller( __METHOD__ )->fetchResultSet();


		$ret = [];
		foreach ( $res as $row ) {
			$ret[] = (array)$row;
		}
		echo "<pre>";
		print_r( $ret );
		echo "</pre>";
		return Utils::formatCSV( $ret );
		*/
	}
}