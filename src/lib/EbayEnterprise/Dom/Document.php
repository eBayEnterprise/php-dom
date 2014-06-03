<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Element.php';

class EbayEnterprise_Dom_Document extends DOMDocument
{
	private static $_multiRootNodeExceptionMsg = 'The specified path would cause adding a sibling to the root element.';

	public function __construct($version=null, $encoding=null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass(
			'DOMElement',
			'EbayEnterprise_Dom_Element'
		);
	}

	/**
	 * Create and attach a EbayEnterprise_Dom_Element node with the
	 * specified name and value.
	 *
	 * @param string $name The node name for the element to be created
	 * @param string|DOMNode $val A CDATA string or node to be appended
	 *        to the created node
	 * @param string $nsUri The ns attribute uri for the element
	 * @return EbayEnterprise_Dom_Document This document
	 */
	public function addElement($name, $val=null, $nsUri='')
	{
		if ($this->documentElement) {
			throw new DOMException(self::$_multiRootNodeExceptionMsg);
		}
		$el = $this->appendChild(new EbayEnterprise_Dom_Element($name, '', $nsUri));
		if (!is_null($val)) {
			$el->appendChild(EbayEnterprise_Dom_Helper::coerceValue($val));
		}
		return $this;
	}

	/**
	 * Same as addElement, except returns
	 * the created element without attaching it.
	 *
	 * @see self::addElement
	 * @return EbayEnterprise_Dom_Element The created node.
	 */
	public function createElement($name, $val=null, $nsUri='')
	{
		$el = new EbayEnterprise_Dom_Element($name, '', $nsUri);
		if (!is_null($val)) {
			// Append the new element in order to append its child.
			$fragment = $this->createDocumentFragment();
			$fragment->appendChild($el);
			$el->appendChild(EbayEnterprise_Dom_Helper::coerceValue($val));
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
	 * @return EbayEnterprise_Dom_Element The last element specified in the path
	 */
	public function setNode($path, $value=null, DOMNode $contextNode=null, $nsUri='')
	{
		if (!$path) {
			if ($value && $contextNode) {
				$contextNode->appendChild(EbayEnterprise_Dom_Helper::coerceValue($value));
			}
			return $contextNode;
		}
		$splitPath = $this->_splitPath($path);

		// When current is empty, the path began with a '/'. Set the context node
		// to the document ($this) and move on to the rest of the path
		// otherwise build new nodes
		return !$splitPath['current'] ?
			$this->setNode($splitPath['rest'], $value, $this, $nsUri) :
			$this->_setNewNode($path, $value, $contextNode, $nsUri, $splitPath);
	}
	/**
	 * Split on any '/' character that isn't wrapped in double quotes
	 * e.g. '/' not proceeded by an even number of '"' characters
	 * Regex have some caveats for "simplicity" sake: doesn't understand
	 * single quotes or escaped quote characters. If we need much more complex
	 * matching that this, something more robust and less cryptic should be
	 * implemented to actually parse the XPath expression.
	 * @param string $path
	 * @return array
	 * @example
	 * <code>
	 * <?php
	 * $path = 'CustomAttributes/Attribute[@name="Description"][@xml:lang="en-us"]';
	 * $this->_splitPath($path);
	 * array(
	 *   'current' => 'CustomAttributes',
	 *   'rest' => 'Attribute[@name="Description"][@xml:lang="en-us"]'
	 * )
	 * ?>
	 * </code>
	 */
	protected function _splitPath($path)
	{
		$parts = preg_split('#[\/](?=([^"]*"[^"]*")*[^"]*$)#', $path, 2);
		return array('current' => array_shift($parts), 'rest' => array_shift($parts));
	}
	/**
	 * set new nodes base on the passed in path string end symbol.
	 * @param string $path
	 * @param string|DOMNode $value
	 * @param DOMNode $contextNode
	 * @param string $nsUri
	 * @param array $splitPath
	 * @return EbayEnterprise_Dom_Element
	 */
	protected function _setNewNode($path, $value=null, DOMNode $contextNode=null, $nsUri='', array $splitPath)
	{
		$contextNode = $contextNode ?: $this;
		$xpath = new DOMXPath($this);
		$nextNode = $xpath->query($splitPath['current'], $contextNode)->item(0);

		// If the path given ends with a '/', then add to an already-existing node.
		// If the node doesn't exist, create it.
		// If the path does not end in '/', multiple nodes with the same name are created.
		$reuseNode = (substr($path, -1) === '/');

		// if the next node doesn't exist, or we're at the end of the path,
		// create a new node from and add append it.
		if (!$nextNode || (!$reuseNode && !$splitPath['rest'])) {
			$nextNode = $this->_addNodeForPath($splitPath['current'], $contextNode, $nsUri);
		}
		return $this->setNode($splitPath['rest'], $value, $nextNode, $nsUri);
	}
	/**
	 * Given a single piece of a supported XPath and a context DOMNode, create a
	 * new DOMElement from the XPath, returning the newly created node.
	 * @param string  $pathSection A single node section in a supported path.
	 * @see self::setNode for details on supported paths
	 * @param DOMNode $parentNode  Node the created node should be appended to
	 * @param string  $nsUriA      Namespace URI of the created node
	 * @return DOMElement          The element created
	 * @throws DOMException If adding the node would result in a DOMDocument with multiple root nodes
	 */
	protected function _addNodeForPath($pathSection, DOMNode $parentNode, $nsUri='')
	{
		if ($parentNode === $this && $this->documentElement) {
			throw new DOMException(self::$_multiRootNodeExceptionMsg);
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
			$matches[2] && $matches[3] ? array_combine($matches[2], $matches[3]) : array()
		);
	}
}
