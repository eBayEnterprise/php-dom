<?php
class TrueAction_Eb2c_Core_Model_Dom_Document extends DOMDocument
{
	public function __construct($version = null, $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass(
			'DOMElement',
			'TrueAction_Eb2c_Core_Model_Dom_Element'
		);
	}
}