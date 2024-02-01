<?php

use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

class W8yAPI extends ApiBase {

	/**
	 * @param mixed $failure
	 *
	 * @return void
	 */
	private function returnFailure( $failure ) {
		$ret            = [];
		$ret['message'] = $failure;
		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			[ 'error' => $ret ]
		);
	}

	/**
	 * @param mixed $code
	 * @param mixed $result
	 *
	 * @return array
	 */
	private function createResult( $code, $result ): array {
		$ret           = [];
		$ret['status'] = $code;
		$ret['data']   = $result;

		return $ret;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$action = $params['what'];
		$output = '';
		if ( !$action || $action === null ) {
			$this->returnFailure( $this->msg( 'w8y-api-error-unknown-what-parameter' ) );
		} else {
			switch ( $action ) {
				case "query":
					$output = "gelukt!";
					/*
					if ( !empty( $params['data'] ) ) {
						$ai = new \WSai\QueryAi();
						$output = $ai->ask( $params['data'] );
						if ( $output === false ) {
							$this->returnFailure( wfMessage( 'wsai-error-general', "cannot contact Open API" )->params() );
							break;
						}
					} else {
						$this->returnFailure( wfMessage( 'wsai-api-error-unknown-2nd-parameter' ) );
						break;
					}*/
					break;
				case "wiki":
					$output = "gelukt!";
					break;
				case "stats":
					$output = "gelukt!";
					break;
				case "extension":
					$output = "gelukt!";
					break;
				default :
					$this->returnFailure( $this->msg( 'w8y-api-error-unknown-what-parameter' )  );
					break;
			}
		}

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			[ 'result' => $output ]
		);

		return true;
	}

	public function needsToken() {
		return false;
	}

	public function isWriteMode() {
		return false;
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'what'            => [
				ParamValidator::PARAM_TYPE => [ 'query', 'stats', 'extension', 'wiki' ],
				ParamValidator::PARAM_REQUIRED => true
			],
			'return' => [
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'from' => [
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'where' => [
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 10,
				ParamValidator::PARAM_TYPE     => 'limit',
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => ApiBase::LIMIT_BIG1,
				IntegerDef::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			],
			'output' => [
				ParamValidator::PARAM_TYPE => [ 'csv', 'table', 'json' ],
				ParamValidator::PARAM_REQUIRED => false
			],
			'pageid' => [
				ParamValidator::PARAM_TYPE     => 'integer',
				ParamValidator::PARAM_REQUIRED => false
			],
			'for' => [
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'extension-name' => [
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'type' => [
				ParamValidator::PARAM_ISMULTI => false,
				ParamValidator::PARAM_TYPE => [ 'version', 'documentation', 'usedby' ],
				ParamValidator::PARAM_REQUIRED => false
			]
		];
	}

	/**
	 * @return array
	 */
	protected function getExamplesMessages(): array {
		return [
			'action=wikiapiary&what=wiki&pageId=4' => 'apihelp-w8y-example-1'
		];
	}

}