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
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
		}
		return $el;
	}

	/**
	 * create child nodes using the specified createChild arguments from the array.
	 * @param  array  $childArgs
	 * @return TrueAction_Dom_Element
	 */
	public function createChildren(array $childArgs)
	{
		foreach ($childArgs as $args) {
			$numArgs = count($args);
			if ($numArgs > 0) {
				$attrs = ($numArgs > 2) ? $args[2] : null;
				$val   = ($numArgs > 1) ? $args[1] : null;
				$name  = $args[0];
				$this->createChild($name, $val, $attrs);
			}
		}
		return $this;
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
