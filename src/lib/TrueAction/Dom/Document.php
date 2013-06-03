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
	 * Create and attach a TrueAction_Dom_Element node with the
	 * specified name and value.
	 *
	 * @param string $name The node name for the element to be created
	 * @param string|DOMNode $val A CDATA string or node to be appended
	 *        to the created node
	 * @return TrueAction_Dom_Document This document
	 */
	public function addElement($name, $val = null)
	{
		$this->appendChild(new TrueAction_Dom_Element($name))
			->appendChild(is_string($val) ? new DOMCdataSection($val) : new DOMText($val));
		return $this;
	}

	/**
	 * Same as addElement, except returns
	 * the created element without attaching it.
	 *
	 * @see self::addElement
	 * @return TrueAction_Dom_Element The created node.
	 */
	public function createElement($name, $val = null)
	{
		// Append the new element in order to append its child.
		$el = $this->appendChild(new TrueAction_Dom_Element($name));
		$el->appendChild(is_string($val) ? new DOMCdataSection($val) : new DOMText($val));
		// Then remove the new element because we didn't really want
		// to attach it.
		return $this->removeChild($el);
	}
}
