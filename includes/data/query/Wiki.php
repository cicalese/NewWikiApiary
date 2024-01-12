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
use WikiApiary\data\Utils;

class Wiki {

	private const DBTABLE_WIKIS = 'w8y_wikis';
	private const DBTABLE_EXTENSIONS = 'w8y_extensions';
	private const DBTABLE_SKINS = 'w8y_skins';
	private const DBTABLE_SCRAPE = 'w8y_scrape_records';
	private const PAGEID_WIKI = 'w8y_wi_page_id';
	private const PAGEID_SCRAPE = 'w8y_sr_page_id';
	private const SCRAPE_ID = 'w8y_sr_sr_id';
	private const EXTENSION_SCRAPE_ID = 'w8y_ex_sr_id';

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

		 $select = [ self::DBTABLE_WIKIS . '.*', self::DBTABLE_SCRAPE . '.*', self::DBTABLE_EXTENSIONS . '.*' ];
		$from = self::DBTABLE_WIKIS;
		$where = [ self::DBTABLE_WIKIS . '.' . self::PAGEID_WIKI => $pageID ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )->leftJoin( self::DBTABLE_SCRAPE,
				null,
				self::DBTABLE_WIKIS . '.' . self::PAGEID_WIKI . ' = ' . self::DBTABLE_SCRAPE . '.' . self::PAGEID_SCRAPE )
			->leftJoin( self::DBTABLE_EXTENSIONS,
				null,
				self::DBTABLE_SCRAPE . '.' . self::SCRAPE_ID . ' = ' . self::DBTABLE_EXTENSIONS . '.' . self::EXTENSION_SCRAPE_ID
			)->where( $where )->caller( __METHOD__ )->fetchResultSet();


		$ret = [];
		foreach ( $res as $row ) {
			$ret[] = (array)$row;
		}
		echo "<pre>";
		print_r( $ret );
		echo "</pre>";
		return Utils::formatCSV( $ret );
	}
}