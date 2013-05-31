<?php
class TrueAction_Dom_Model_Element extends DOMElement {

	/**
	 * Create and append a child to this element.
	 *
	 * @param string $name The name of the element to create.
	 * @param string|DOMNode $val The child node to append to the created element.
	 * @param array $attrs Array of attribute names and values to append to the created element.
	 * @example $ex1 = $tde->createChild('foo', 'bar', array('fizzy'=>'wizzy')) -> "<foo fizzy='wizzy'>bar</foo>"
	 * @example $tde->createChild('xyzzy', $ex1) -> "<xyzzy><foo fizzy='wizzy'>bar</foo></xyzzy>"
	 */
	public function createChild($name, $val = null, array $attrs = null)
	{
		$el = $this->appendChild(new TrueAction_Dom_Model_Element($name));
		if (!is_null($attrs)) {
			foreach($attrs as $attrName => $attrVal) {
				$el->setAttribute($attrName, $attrVal);
			}
		}
	    if (is_string($val)) {
			$el->appendChild(new DOMCdataSection($val));
	    } else {
	      	$el->appendChild($val);
	    }
		return $el;
	}

	/**
	 * adds an attibute as an id and returns $this to allow chaining.
	 * @param string $name
	 * @param string $val
	 * @param TrueAction_Dom_Model_Element
	 */
	public function addIdAttribute($name, $val = null)
	{
		if (!$this->hasAttribute($name)) {
			$this->setAttribute($name, $val);
		}
		$this->setIdAttribute($name, true);
		return $this;
	}
}