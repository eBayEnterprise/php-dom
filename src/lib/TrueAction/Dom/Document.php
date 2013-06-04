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
	public function addElement($name, $val = null, $nsUri = '')
	{
		$el = $this->appendChild(new TrueAction_Dom_Element($name));
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
		}

		// adding ns attribute to root document
		if (trim($nsUri) !== '') {
			$this->createAttributeNS($nsUri, $name);
		}

		return $this;
	}

	/**
	 * Same as addElement, except returns
	 * the created element without attaching it.
	 *
	 * @see self::addElement
	 * @return TrueAction_Dom_Element The created node.
	 */
	public function createElement($name, $val = null, $nsUri = '')
	{
		// Append the new element in order to append its child.
		$el = $this->appendChild(new TrueAction_Dom_Element($name));
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
			// Then remove the new element because we didn't really want
			// to attach it.
			$this->removeChild($el);
		}

		// adding ns attribute to root document
		if (trim($nsUri) !== '') {
			$this->createAttributeNS($nsUri, $name);
		}

		return $el;
	}
}
