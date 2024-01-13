<?php

namespace WikiApiary\Scribunto;

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
			$arguments = [];
		}
		$action = Utils::getOptionSetting( 'action', true, $arguments );
		$id = Utils::getOptionSetting(
			$arguments,
			'id'
		);

		$title = Utils::getOptionSetting(
			$arguments,
			'title'
		);

		if ( $id === false ) {
			$id = 0;
		} else {
			$id = intval( $id );
		}

		if ( $title === false ) {
			$title = '';
		}

		$limit = Utils::getOptionSetting(
			$arguments,
			'limit'
		);
		if ( $limit === false ) {
			$limit = 10;
		}
		$unique  = Utils::getOptionSetting(
			$arguments,
			'unique',
			false
		);
		$selectionMaker = new SelectionMaker();

		$startDate = Utils::getOptionSetting(
			$arguments,
			'startDate'
		);

		$endDate = Utils::getOptionSetting(
			$arguments,
			'endDate'
		);

		$dates = $selectionMaker->setDatesArray( $startDate, $endDate );
		$dates = $selectionMaker->checkDates( $dates );
		$ret = '';
		if ( $id !== 0 || ( WSStatsHooks::getConfigSetting( 'countSpecialPages' ) !== false && $title !== '' ) ) {
			$type = WSStatsHooks::getOptionSetting( $arguments,
				'type' );
			$data = WSStatsHooks::getViewsPerPage( $id,
				$dates,
				$type,
				$unique,
				$title );
			if ( $data !== null ) {
				$ret = $data;
			}
		}
		return [ $ret ];
	}

	/**
	 * This mirrors the functionality of the #wsstats parser function and makes it available
	 * in Lua. This function will return a table.
	 * @param ?array $arguments
	 *
	 * @return array
	 */
	public function stats(
		?array $arguments
	): array {
		if ( $arguments === null ) {
			$arguments = [];
		}
		$id = WSStatsHooks::getOptionSetting(
			$arguments,
			'id'
		);

		$title = WSStatsHooks::getOptionSetting(
			$arguments,
			'title'
		);

		if ( $id === false ) {
			$id = 0;
		} else {
			$id = intval( $id );
		}

		if ( $title === false ) {
			$title = '';
		}

		$limit = WSStatsHooks::getOptionSetting(
			$arguments,
			'limit'
		);
		if ( $limit === false ) {
			$limit = 10;
		}
		$format = 'lua';

		$unique  = WSStatsHooks::getOptionSetting(
			$arguments,
			'unique',
			false
		);
		$selectionMaker = new SelectionMaker();

		$startDate = WSStatsHooks::getOptionSetting(
			$arguments,
			'startDate'
		);

		$endDate = WSStatsHooks::getOptionSetting(
			$arguments,
			'endDate'
		);

		$dates = $selectionMaker->setDatesArray( $startDate,
			$endDate );
		$dates = $selectionMaker->checkDates( $dates );
		$data = WSStatsHooks::getMostViewedPages(
			$dates,
			$format,
			$unique,
			'',
			$limit,
			$id,
			$title
		);
		return [ $this->convertToLuaTable( $data ) ];
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
