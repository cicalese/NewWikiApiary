<?php
/**
 * Created by  : Open CSP
 * Project     : WikiApiary
 * Filename    : Extensions.php
 * Description :
 * Date        : 30-1-2024
 * Time        : 21:42
 */

namespace WikiApiary\data\query;

use MediaWiki\MediaWikiServices;
use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use Wikimedia\Rdbms\DBConnRef;

class Extensions {

	/**
	 * @param string $extensionName
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getExtensionVersions( string $extensionName, DBConnRef $dbr ) {
		$select = [ Structure::EXTENSION_VERSION, 'count' => 'count(*)' ];
		$from = Structure::DBTABLE_WIKIS;
		$where = Structure::EXTENSION_NAME . ' LIKE "' . $extensionName . '"';
		// SELECT w8y_ed_version, COUNT(*) AS C
		// FROM w8y_wikis
		// JOIN w8y_scrape_records ON w8y_wi_last_sr_id = w8y_sr_sr_id
		// JOIN w8y_extension_links ON w8y_sr_vr_id = w8y_el_vr_id
		// JOIN w8y_extension_data ON w8y_el_ed_id = w8y_ed_ed_id
		// WHERE w8y_ed_name like "ParserFunctions"
		// GROUP BY w8y_ed_version
		// ORDER BY
		//C DESC;
		try {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS_LINK, null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_VID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS, null, Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_ID . '=' . Structure::DBTABLE_EXTENSIONS . '.' . Structure::EXTENSION_ID )->
			where( $where )->
			groupBy( Structure::EXTENSION_VERSION )->
			orderBy( 'count', 'DESC' )->
			caller( __METHOD__ )->
			fetchResultSet();
		} catch ( \Exception $e ) {
			wfDebug( $e->getMessage(), 'w8y' );
			return [];
		}
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t][Structure::EXTENSION_VERSION] = $row[Structure::EXTENSION_VERSION];
				$ret[$t]['w8y_count'] = $row['count'];
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param string $extensionName
	 * @param DBConnRef $dbr
	 *
	 * @return array
	 */
	private function getExtensionDocumentation( string $extensionName, DBConnRef $dbr ) {
		$select = [ Structure::EXTENSION_DOC_URL, 'count' => 'count(*)' ];
		$from = Structure::DBTABLE_WIKIS;
		$where = Structure::EXTENSION_NAME . ' LIKE "' . $extensionName . '" AND ';
		$where .= Structure::EXTENSION_DOC_URL . ' IS NOT NULL';
		// SELECT w8y_ed_doc_url,
		// COUNT(*) AS C
		// FROM w8y_wikis
		// JOIN w8y_scrape_records ON w8y_wi_last_sr_id = w8y_sr_sr_id
		// JOIN w8y_extension_links ON w8y_sr_vr_id = w8y_el_vr_id
		// JOIN w8y_extension_data ON w8y_el_ed_id = w8y_ed_ed_id
		// WHERE w8y_ed_name like "ParserFunctions"
		// AND w8y_ed_doc_url IS NOT NULL
		// GROUP BY w8y_ed_doc_url
		// ORDER BY C DESC;
		try {
			$res = $dbr->newSelectQueryBuilder()->
			select( $select )->
			from( $from )->
			leftJoin( Structure::DBTABLE_SCRAPE, null, Structure::DBTABLE_WIKIS . '.' . Structure::WIKI_LAST_SR_RCRD . '=' . Structure::DBTABLE_SCRAPE . '.' . Structure::SR_ID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS_LINK, null, Structure::DBTABLE_SCRAPE . '.' . Structure::SCRAPE_VR_ID . '=' . Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_VID )->
			leftJoin( Structure::DBTABLE_EXTENSIONS, null, Structure::DBTABLE_EXTENSIONS_LINK . '.' . Structure::EXTENSION_LINK_ID . '=' . Structure::DBTABLE_EXTENSIONS . '.' . Structure::EXTENSION_ID )->
			where( $where )->
			groupBy( Structure::EXTENSION_DOC_URL )->
			orderBy( 'count', 'DESC' )->
			caller( __METHOD__ )->
			fetchResultSet();
		} catch ( \Exception $e ) {
			wfDebug( $e->getMessage(), 'w8y' );
			return [];
		}
		$ret = [];
		$t = 0;
		if ( $res->numRows() > 0 ) {
			while ( $row = $res->fetchRow() ) {
				$ret[$t][Structure::EXTENSION_DOC_URL] = $row[Structure::EXTENSION_DOC_URL];
				$ret[$t]['w8y_count'] = $row['count'];
				$t++;
			}
		}

		return $ret;
	}

	/**
	 * @param string $extensionName
	 * @param string $queryType
	 * @param string $export
	 *
	 * @return mixed
	 */
	public function doQuery( string $extensionName, string $queryType, string $export = "table" ): mixed {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $lb->getConnectionRef( DB_REPLICA );
		$result = [];
		$tables = [];

		switch ( $queryType ) {
			case "version":
				$result = $this->getExtensionVersions( $extensionName, $dbr );
				$tables = [ Structure::w8yMessage( Structure::EXTENSION_VERSION ),
					Structure::w8yMessage( 'w8y_count' )
				];
				break;
			case "documentation":
				$result = $this->getExtensionDocumentation( $extensionName, $dbr );
				$tables = [ Structure::w8yMessage( Structure::EXTENSION_DOC_URL ),
					Structure::w8yMessage( 'w8y_count' )
				];
				break;
			default:
				$result = [];
		}

		switch ( $export ) {
			case "table":
				return Utils::renderTable( $result, '', $tables, true );
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