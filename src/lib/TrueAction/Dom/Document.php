<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Element.php';

class TrueAction_Dom_Document extends DOMDocument
{
	private static $multiRootNodeExceptionMsg = 'The specified path would cause adding a sibling to the root element.';

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
	 * @param string $nsUri The ns attribute uri for the element
	 * @return TrueAction_Dom_Document This document
	 */
	public function addElement($name, $val = null, $nsUri = '')
	{
		if ($this->documentElement) {
			throw new DOMException(self::$multiRootNodeExceptionMsg);
		}
		$el = $this->appendChild(new TrueAction_Dom_Element($name, '', $nsUri));
		if (!is_null($val)) {
			$el->appendChild(TrueAction_Dom_Helper::coerceValue($val));
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
		$el = new TrueAction_Dom_Element($name, '', $nsUri);
		if (!is_null($val)) {
			// Append the new element in order to append its child.
			$fragment = $this->createDocumentFragment();
			$fragment->appendChild($el);
			$el->appendChild(TrueAction_Dom_Helper::coerceValue($val));
			$fragment->removeChild($el);
		}
		return $el;
	}

	/**
	 * Create a new DOMElement at the location specified by the given path. This
	 * method will create any elements that do not exist leading up to the path.
	 * The last element specified by the path will always result in a new
	 * DOMElement, creating a new sibling if a node at the last part of the
	 * path already exists. If a value is given, it will be set as the value of
	 * the last element created.
	 *
	 * The "$path" value supports a very limited subset of XPath syntax. The path
	 * must unambiguously describe a single destination node. Attribute name/value
	 * pairs can be specified using standard XPath syntax.
	 *
	 * @example
	 * Given the following path:
	 * CustomAttributes/Attribute[@name="Description"][@xml:lang="en-us"]
	 * This DOM structure would be created and added starting at the given context node
	 * <CustomAttributes><Attribute name="Description" xml:lang="en-us" /></CustomAttributes>
	 *
	 * @param string         $path        Path pointing to the node to be created.
	 * @param string|DOMNode $value       Value the created node should be set to
	 * @param DOMNode        $contextNode Nodes added/created relative to this DOMNode, defaults to the document.
	 * @param string         $nsUri       If given, any nodes created will have a namespaceURI set to this value
	 * @return TrueAction_Dom_Element The last element specified in the path
	 */
	public function setNode($path, $value=null, DOMNode $contextNode=null, $nsUri='')
	{
		if (!$path) {
			if ($value && $contextNode) {
				$contextNode->appendChild(TrueAction_Dom_Helper::coerceValue($value));
			}
			return $contextNode;
		}

		$parts = explode('/', $path, 2);
		$current = array_shift($parts);
		$rest = array_shift($parts);

		// When current is empty, the path began with a '/'. Set the context node
		// to the document ($this) and move on to the rest of the path
		if (!$current) {
			return $this->setNode($rest, $value, $this, $nsUri);
		}

		$contextNode = $contextNode ?: $this;
		$xpath = new DOMXPath($this);
		$nextNode = $xpath->query($current, $contextNode)->item(0);
		// if the next node doesn't exist, or we're at the end of the path,
		// create a new node from and add append it.
		if (!$nextNode || !$rest) {
			$nextNode = $this->_addNodeForPath($current, $contextNode, $nsUri);
		}
		return $this->setNode($rest, $value, $nextNode, $nsUri);
	}
	/**
	 * Given a single piece of a supported XPath and a context DOMNode, create a
	 * new DOMElement from the XPath, returning the newly created node.
	 * @param string  $pathSection A single node section in a supported path. @see self::setNode for details on supported paths
	 * @param DOMNode $parentNode  Node the created node should be appended to
	 * @param string  $nsUriA      Namespace URI of the created node
	 * @return DOMElement          The element created
	 * @throws DOMException If adding the node would result in a DOMDocument with multiple root nodes
	 */
	protected function _addNodeForPath($pathSection, DOMNode $parentNode, $nsUri='')
	{
		if ($parentNode === $this && $this->documentElement) {
			throw new DOMException(self::$multiRootNodeExceptionMsg);
		}
		list($nodeName, $attributes) = $this->_parsePathSection($pathSection);
		$nextNode = $this->createElement($nodeName, null, $nsUri);
		$parentNode->appendChild($nextNode);
		$nextNode->addAttributes($attributes);
		return $nextNode;
	}

	/**
	 * Break a section of supported XPath into an array containing the node name
	 * and an array of attributes
	 * @param  string $pathSection
	 * @return array
	 */
	protected function _parsePathSection($pathSection)
	{
		$pattern = '/([^\[]+)(?:\[@([^=]+)="([^"]+)")/';
		$matches = array();
		preg_match_all($pattern, $pathSection, $matches);
		return array(
			$matches[1] ? $matches[1][0] : $pathSection,
			$matches[2] && $matches[3] ?
				array_combine($matches[2], $matches[3]) :
				array()
		);
	}
}
