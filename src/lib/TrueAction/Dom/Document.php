<?php
class TrueAction_Dom_Document extends DOMDocument
{
	public function __construct($version = null, $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass(
			'DOMElement',
			'TrueAction_Dom_Element'
		);
	}

	/**
	 * Create a TrueAction_Dom_Element node with the specified name and value.
	 *
	 * @param string $name The node name for the element to be created.
	 * @param string|DOMNode $val A CDATA string or node to be appended to the created node.
	 * @return TrueAction_Dom_Element The created node.
	 */
	public function createElement($name, $val=null)
	{
		$el = parent::createElement($name);
		if (!is_null($val)) {
			if (is_string($val)) {
				$val = new DOMCdataSection($val);
			}
			// Attach the created node to the DOMDocument to make it writable
			// so we can append the $val node to it.
			$child = $this->appendChild($el);
			$child->appendChild($val);
			$this->removeChild($el);
		}
		// Then detach it because we only wanted it writable, not attached.
		return $el;
	}
}
