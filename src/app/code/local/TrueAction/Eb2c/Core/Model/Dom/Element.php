<?php
class TrueAction_Eb2c_Core_Model_Dom_Element extends DOMElement {
	/**
	 * Create and append a child to this element.
	 *
	 * @param string $name The name of the element to create.
	 * @param string|DOMNode $val The child node to append to the created element.
	 * @param array $attrs Array of attribute names and values to append to the created element.
	 * @example $ex1 = $tde->createChild('foo', 'bar', array('fizzy'=>'wizzy')) -> "<foo fizzy='wizzy'>bar</foo>"
	 * @example $tde->createChild('xyzzy', $ex1) -> "<xyzzy><foo fizzy='wizzy'>bar</foo></xyzzy>"
	 */
	public function createChild($name, $val, array $attrs = null) {
		$el = $this->appendChild(new TrueAction_Eb2c_Core_Model_Dom_Element($name, $val));
		if (!is_null($attrs)) {
			foreach($attrs as $attrName => $attrVal) {
				$el->setAttribute($attrName, $attrVal);
			}
		}
		return $el;
	}
}