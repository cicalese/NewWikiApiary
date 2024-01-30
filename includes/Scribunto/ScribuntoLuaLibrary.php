<?php

namespace WikiApiary\Scribunto;

use WikiApiary\data\query\Extensions;
use WikiApiary\data\query\Stats;
use WikiApiary\data\query\Wiki;
use WikiApiary\data\Utils;

/**
 * Register the Lua library.
 */
class ScribuntoLuaLibrary extends \Scribunto_LuaLibraryBase {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$interfaceFuncs = [
			'w8y' => [ $this, 'w8y' ]
		];

		$this->getEngine()->registerInterface( __DIR__ . '/' . 'mw.w8y.lua', $interfaceFuncs, [] );
	}

	/**
	 * This mirrors the functionality of the #w8y parser function and makes it available
	 * in Lua. This function will return a table.
	 * @param ?array $arguments
	 *
	 * @return array
	 */
	public function w8y( ?array $arguments ): array {
		if ( $arguments === null ) {
			return [];
		}

		$action = Utils::getOptionSetting( 'action', true, $arguments );
		if ( $action === null ) {
			return [];
		}
		switch ( $action ) {
			case "extension":
				$eName = Utils::getOptionSetting( 'Extension name', true, $arguments );
				if ( $eName === null ) {
					return [];
				}
				$query = new Extensions();
				$result = $query->doQuery( $eName, 'lua' );
				return [ $this->convertToLuaTable( $result ) ];
			case "wiki":
				$id = Utils::getOptionSetting( 'id', true, $arguments );
				if ( $id === null ) {
					return [];
				}
				$query = new Wiki();
				$result = $query->doQuery( intval( $id ), 'lua' );
				return [ $this->convertToLuaTable( $result ) ];
			case "stats":
				$type = Utils::getOptionSetting( 'for', true, $arguments );
				if ( $type === null ) {
					return [];
				}
				$limit = Utils::getOptionSetting( 'limit', true, $arguments );
				if ( $limit === null ) {
					$limit = 10;
				}
				$where = Utils::getOptionSetting( 'where', true, $arguments );
				if ( $where === null ) {
					$where = '';
				}
				$query = new Stats();
				$result = $query->doQuery( $type, $limit, 'lua' );
				return [ $this->convertToLuaTable( $result ) ];
			default:
				return [];
		}
	}

	/**
	 * @param mixed $array
	 * @return mixed
	 */
	private function convertToLuaTable( mixed $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				$array[$key] = $this->convertToLuaTable( $value );
			}

			array_unshift( $array, '' );
			unset( $array[0] );
		}

		return $array;
	}
}
