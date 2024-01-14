<?php
/**
 * Created by  : Open CSP.
 * Project     : WikiApiary
 * Filename    : Structure.php
 * Description :
 * Date        : 2-1-2024
 * Time        : 09:46
 */

namespace WikiApiary\data;

class Structure {

	/**
	 * @var array
	 */
	private array $dbStructure;

	public const DBTABLE_WIKIS = 'w8y_wikis';
	public const DBTABLE_EXTENSIONS = 'w8y_extensions';
	public const DBTABLE_SKINS = 'w8y_skins';
	public const DBTABLE_SCRAPE = 'w8y_scrape_records';
	public const WIKI_PAGEID = 'w8y_wi_page_id';
	public const WIKI_DEFUNCT = 'w8y_wi_is_defunct';
	public const WIKI_LAST_SR_RCRD = 'w8y_wi_last_sr_id';
	public const SR_ID = 'w8y_sr_sr_id';
	public const EXTENSION_SCRAPE_ID = 'w8y_ex_sr_id';
	public const SKIN_SCRAPE_ID = 'w8y_sk_sr_id';

	/**
	 *
	 */
	public function __construct() {
		global $IP;
		$jsonFile = $IP . '/extensions/WikiApiary/sql/tables.json';
		if ( file_exists( $jsonFile ) ) {
			$dbStructure = [];
			$json = json_decode( file_get_contents( $jsonFile ), true );
			foreach ( $json as $dbTable ) {
				$name = $dbTable['name'];
				foreach ( $dbTable['columns'] as $column ) {
					$dbStructure[$name][] = $column['name'];
				}
			}
			$this->dbStructure = $dbStructure;
		} else {
			$this->dbStructure = [];
		}
	}

	/**
	 * @param string $tableName
	 * @return bool
	 */
	public function tableExists( string $tableName ): bool {
		return array_key_exists( $tableName, $this->dbStructure );
	}

	/**
	 * @param string $tableName
	 *
	 * @return array
	 */
	public function returnTableColumns( string $tableName ): array {
		return $this->dbStructure[$tableName] ?? [];
	}

	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return bool
	 */
	public function columnExists( string $tableName, string $columnName ): bool {
		if ( $this->tableExists( $tableName ) ) {
//			echo "<pre>";
//			var_dump( $tableName, $this->dbStructure[$tableName] );
//			echo "</pre>";
			return in_array( $columnName, $this->dbStructure[$tableName] );
		}
		return false;
	}

}
