<?php
class TrueAction_Eb2c_Core_Model_TrueDomDocument extends DOMDocument
{
	public function __construct(string $version = null, string $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass(
			'DOMElement',
			'TrueAction_Eb2c_Core_Model_TrueDomElement'
		);
	}
}