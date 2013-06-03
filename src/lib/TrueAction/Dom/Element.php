<?php
class TrueAction_Dom_Element extends DOMElement {

	/**
	 * Create and append a child to this element.
	 *
	 * @param string $name The name of the element to create.
	 * @param string|DOMNode $val The child node to append to the created element.
	 * @param array $attrs Array of attribute names and values to append to the created element.
	 * @example $ex1 = $tde->createChild('foo', 'bar', array('fizzy'=>'wizzy')) -> "<foo fizzy='wizzy'>bar</foo>"
	 * @example $tde->createChild('xyzzy', $ex1) -> "<xyzzy><foo fizzy='wizzy'>bar</foo></xyzzy>"
	 * @return TrueAction_Dom_Element the created TrueAction_Dom_Element
	 */
	public function createChild($name, $val = null, array $attrs = null)
	{
		$el = $this->appendChild(new TrueAction_Dom_Element($name));
		if (!is_null($attrs)) {
			foreach ($attrs as $attrName => $attrVal) {
				$el->setAttribute($attrName, $attrVal);
			}
		}
		if (is_string($val)) {
			$val = new DOMCdataSection($val);
		}
		$el->appendChild($val);
		return $el;
	}

	/**
	 * Add an attribute as an id.
	 *
	 * @param string $name The name of the attribute
	 * @param string $val The value of the attribute
	 * @param boolean $isId if true, the attribute is an id (even if its name isn't "id").
	 * @param TrueAction_Dom_Element
	 * @return DOMAttr
	 */
	public function setAttribute($name, $val = null, $isId = false)
	{
		$attr = parent::setAttribute($name, $val);
		$this->setIdAttribute($name, $isId);
		return $attr;
	}

	/**
	 * Same as setAttribute except returns $this object for chaining.
	 *
	 * @see self::setAttribute
	 * @return TrueAction_Dom_Element
	 */
	public function addAttribute($name, $val = null, $isId = false)
	{
		$this->setAttribute($name, $val, $isId);
		return $this;
	}
}
