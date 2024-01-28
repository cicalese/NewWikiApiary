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
	public const DBTABLE_EXTENSIONS = 'w8y_extension_data';
	public const DBTABLE_EXTENSIONS_LINK = 'w8y_extension_links';
	public const DBTABLE_SKINS = 'w8y_skin_data';
	public const DBTABLE_SKINS_LINK = 'w8y_skin_links';
	public const DBTABLE_SCRAPE = 'w8y_scrape_records';
	public const WIKI_PAGEID = 'w8y_wi_page_id';
	public const WIKI_DEFUNCT = 'w8y_wi_is_defunct';
	public const WIKI_LAST_SR_RCRD = 'w8y_wi_last_sr_id';
	public const SR_ID = 'w8y_sr_sr_id';
	public const SCRAPE_VR_ID = 'w8y_sr_vr_id';
	public const EXTENSION_ID = 'w8y_ed_ed_id';
	public const EXTENSION_NAME = 'w8y_ed_name';
	public const EXTENSION_LINK_VID = 'w8y_el_vr_id';
	public const EXTENSION_LINK_ID = 'w8y_el_ed_id';
	public const SKIN_LINK_VID = 'w8y_sl_vr_id';
	public const SKIN_LINK_ID = 'w8y_sl_sd_id';
	public const SKIN_ID = 'w8y_sd_sd_id';
	public const SKIN_NAME = 'w8y_sd_name';
	public const SCRAPE_MEDIAWIKI_VERSION = 'w8y_sr_mw_version';

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
	 * @param string $key
	 * Find an i18n key, else return original
	 *
	 * @return string
	 */
	public static function w8yMessage( string $key ): string {
		$msg = wfMessage( $key )->parse();
		if ( str_starts_with( $msg,	'⧼' ) ) {
			return $key;
		}
		return $msg;
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
