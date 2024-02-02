<?php

use WikiApiary\data\Structure;
use WikiApiary\data\Utils;
use WikiApiary\TagHooks;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

class W8yAPI extends ApiBase {


	/**
	 * @var Structure
	 */
	private Structure $structure;

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

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function convertParams( array $params ): array {
		if ( isset( $params['extension-name'] ) ) {
			$params['Extension name'] = $params['extension-name'];
			unset( $params['extension-name'] );
		}
		if ( isset( $params['output'] ) ) {
			$params['format'] = $params['output'];
			unset( $params['output'] );
		}
		if ( isset( $params['pageid'] ) ) {
			$params['pageId'] = $params['pageid'];
			unset( $params['pageid'] );
		}
		if ( isset( $params['what'] ) ) {
			$params['action'] = $params['what'];
			unset( $params['what'] );
		}
		return $params;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$this->structure = new Structure();
		$params = $this->convertParams( $params );
		$action = $params['action'];
		$output = '';
		if ( !$action || $action === null ) {
			$this->returnFailure( $this->msg( 'w8y-api-error-unknown-what-parameter' ) );
		} else {
			switch ( $action ) {
				case "query":
				case "wiki":
				case "stats":
				case "extension":
				$result = TagHooks::handleIt( $params );
				if ( $result['status'] === 'error' ) {
					$this->returnFailure( $result['data'] );
				}
				$output = $result['data'];
					break;
				default:
					$this->returnFailure( $this->msg( 'w8y-api-error-unknown-what-parameter' ) );
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
				ParamValidator::PARAM_DEFAULT => 'json',
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
			'action=wikiapiary&what=wiki&pageid=4' => 'apihelp-w8y-example-1'
		];
	}

}