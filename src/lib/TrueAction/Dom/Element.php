<?php
class TrueAction_Dom_Element extends DOMElement {
	/**
	 * create a child node and return the parent.
	 * @param string         $name
	 * @param string|DOMNode $val
	 * @param array          $attrs
	 * @return TrueAction_Dom_Element
	 */
	public function addChild($name, $val = null, array $attrs = null, $nsUri = '')
	{
		$this->createChild($name, $val, $attrs, $nsUri);
		return $this;
	}

	/**
	 * Create and append a child to this element.
	 *
	 * @param string $name The name of the element to create.
	 * @param string|DOMNode $val The child node to append to the created element.
	 * @param array $attrs Array of attribute names and values to append to the created element.
	 * @param string $nsUri The ns attribute uri for the element
	 * @example $ex1 = $tde->createChild('foo', 'bar', array('fizzy'=>'wizzy')) -> "<foo fizzy='wizzy'>bar</foo>"
	 * @example $tde->createChild('xyzzy', $ex1) -> "<xyzzy><foo fizzy='wizzy'>bar</foo></xyzzy>"
	 * @return TrueAction_Dom_Element the created TrueAction_Dom_Element
	 */
	public function createChild($name, $val = null, array $attrs = null, $nsUri = '')
	{
		$el = $this->appendChild(new TrueAction_Dom_Element($name, '', $nsUri));
		$el->addAttributes($attrs);
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
		}
		return $el;
	}

	/**
	 * create all nodes along a given path relative to the current node.
	 * any nodes along the path that do not exist will be created.
	 * NOTE: for any element along the path except the leaf (last) element,
	 *  only the first matching node will be traversed if the element matches multiple siblings.
	 * @param string         $path
	 * @param string|DOMNode $value
	 * @param array          $attrs
	 * @param TrueAction_Dom_Element
	 */
	public function setNode($path, $val = null, array $attrs = null)
	{
		$node = $this;
		$pathArray = explode('/', $path);
		$end = count($pathArray) - 1;
		$xpath = new DOMXPath($this->ownerDocument);
		foreach ($pathArray as $index => $nodeName) {
			if (!$nodeName) { // skip blank/null elements
				continue;
			}
			if ($index === $end) {
				$node = $node->createChild($nodeName, $val, $attrs);
			} else {
				$nodeList = $xpath->query($nodeName, $node);
				if ($nodeList->length > 0) {
					$node = $nodeList->item(0);
				} else {
					$node = $node->createChild($nodeName);
				}
			}
		}
		return $node;
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
	 * @see self::setAttribute
	 * @return TrueAction_Dom_Element
	 */
	public function addAttribute($name, $val = null, $isId = false)
	{
		$this->setAttribute($name, $val, $isId);
		return $this;
	}

	/**
	 * add attributes extracted from the specified array
	 * @param array $attrs
	 * @return TrueAction_Dom_Element
	 */
	public function addAttributes(array $attrs = null)
	{
		if ($attrs) {
			foreach ($attrs as $name => $value) {
				parent::setAttribute($name, $value);
			}
		}
		return $this;
	}
}
