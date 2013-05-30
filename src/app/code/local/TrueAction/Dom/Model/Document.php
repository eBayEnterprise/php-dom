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

	public function createElement($name, $value=null)
	{
		$el = parent::createElement($name);
		if (is_string($value)) {
			$this->appendChild($el);
			$el->appendChild(new DOMCdataSection($value));
		}
		return $el;
	}
}