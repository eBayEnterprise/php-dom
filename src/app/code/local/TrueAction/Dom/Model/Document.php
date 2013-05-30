<?php
class TrueAction_Dom_Model_Document extends DOMDocument
{
	public function __construct($version = null, $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass(
			'DOMElement',
			'TrueAction_Dom_Model_Element'
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