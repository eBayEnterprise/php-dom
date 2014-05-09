<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Helper.php';

class EbayEnterprise_Dom_Element extends DOMElement
{
	public $cascadeDefaultNamespace = true;

	/**
	 * create a child node and return the parent.
	 * @param string         $name
	 * @param string|DOMNode $val
	 * @param array          $attrs
	 * @return EbayEnterprise_Dom_Element
	 */
	public function addChild($name, $val=null, array $attrs=null, $nsUri=null)
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
	 * @return EbayEnterprise_Dom_Element the created EbayEnterprise_Dom_Element
	 */
	public function createChild($name, $val=null, array $attrs=null, $nsUri=null)
	{
		$nsUri = ($this->cascadeDefaultNamespace && is_null($nsUri)) ?
			$this->namespaceURI : $nsUri;
		$el = $this->appendChild(new EbayEnterprise_Dom_Element($name, '', $nsUri));
		$el->addAttributes($attrs);
		if (!is_null($val)) {
			$el->appendChild(EbayEnterprise_Dom_Helper::coerceValue($val));
		}
		return $el;
	}

	/**
	 * Create a new DOMElement at the path specified. The path should be relative
	 * to the DOMElement this is called on.
	 * @see EbayEnterprise_Dom_Document::setNode
	 * @param string         $path
	 * @param string|DOMNode $value
	 * @param string         $nsUri
	 */
	public function setNode($path, $val=null, $nsUri='')
	{
		return $this->ownerDocument->setNode($path, $val, $this, $nsUri);
	}

	/**
	 * Add an attribute as an id.
	 *
	 * @param string $name The name of the attribute
	 * @param string $val The value of the attribute
	 * @param boolean $isId if true, the attribute is an id (even if its name isn't "id").
	 * @param EbayEnterprise_Dom_Element
	 * @return DOMAttr
	 */
	public function setAttribute($name, $val=null, $isId=false)
	{
		$attr = parent::setAttribute($name, $val);
		$this->setIdAttribute($name, $isId);
		return $attr;
	}

	/**
	 * Same as setAttribute except returns $this object for chaining.
	 * @see self::setAttribute
	 * @return EbayEnterprise_Dom_Element
	 */
	public function addAttribute($name, $val=null, $isId=false)
	{
		$this->setAttribute($name, $val, $isId);
		return $this;
	}

	/**
	 * add attributes extracted from the specified array
	 * @param array $attrs
	 * @return EbayEnterprise_Dom_Element
	 */
	public function addAttributes(array $attrs=null)
	{
		if ($attrs) {
			foreach ($attrs as $name => $value) {
				parent::setAttribute($name, $value);
			}
		}
		return $this;
	}
}
