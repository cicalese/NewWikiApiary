<?php
/**
 * Created by  : Wikibase Solutions B.V.
 * Project     : WikiApiary
 * Filename    : Query.php
 * Description :
 * Date        : 2-1-2024
 * Time        : 09:44
 */

namespace WikiApiary\data\query;

use WikiApiary\data\Structure;

class Query {

	/**
	 * @var Structure
	 */
	private Structure $structure;

	public function __construct() {
		$this->structure = new Structure();
	}


}
