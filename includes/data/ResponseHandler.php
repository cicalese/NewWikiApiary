<?php
/**
 * Created by  : Open CSP.
 * Project     : WikiApiary
 * Filename    : responseHandler.php
 * Description :
 * Date        : 2-1-2024
 * Time        : 13:08
 */

namespace WikiApiary\data;

class ResponseHandler {

	/**
	 * @var array
	 */
	private static array $responses;

	/**
	 * @param string $message
	 * @return void
	 */
	public static function addMessage( string $message ): void {
		self::$responses[] = $message;
	}

	/**
	 * @return string
	 */
	public static function getMessages(): string {
		if ( !empty( self::$responses ) ) {
			return implode( PHP_EOL, self::$responses );
		} else {
			return "";
		}
	}
}
