<?php
/**
 * Created by  : Wikibase Solutions B.V.
 * Project     : dev1-03
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

	/**
	 *
	 */
	public function __construct() {
		if ( file_exists( __DIR__ . '/../sql/tables.json' ) ) {
			$dbStructure = [];
			$json = json_decode( file_get_contents( __DIR__ . '/../sql/tables.json' ), true );
			foreach ( $json as $dbTable ) {
				$name = $dbTable['name'];
				foreach ( $dbTable['columns'] as $column ) {
					$dbStructure[$name][] = $column['name'];
				}
			}
			$this->dbStructure = $dbStructure;
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
	 * @param string $columnName
	 * @return bool
	 */
	public function columnExists( string $tableName, string $columnName ): bool {
		if ( $this->tableExists( $tableName ) ) {
			return in_array( $columnName, $this->dbStructure[$tableName] );
		}
		return false;
	}

}
