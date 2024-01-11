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

class Wiki {

	private const DBTABLE_WIKIS = 'w8y_wikis';
	private const DBTABLE_EXTENSIONS = 'w8y_extensions';
	private const DBTABLE_SKINS = 'w8y_skins';
	private const DBTABLE_SCRAPE = 'w8y_scrape_records';
	private const WIKI_PAGEID = 'w8y_wi_page_id';


	public function doQuery( int $pageID ) {
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
		$select = [ self::DBTABLE_WIKIS . '.*', self::DBTABLE_EXTENSIONS . '.*', self::DBTABLE_SCRAPE . '.*' ];
		$from = self::DBTABLE_WIKIS;
		$where = [ self::WIKI_PAGEID => $pageID ];
		$res = $dbr->newSelectQueryBuilder()->select( $select )->from( $from )
			->join( self::DBTABLE_EXTENSIONS, null, self::DBTABLE_WIKIS . '.' . self::WIKI_PAGEID . ' = ' )
		

	}
}