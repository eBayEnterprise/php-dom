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
	 * @param string $nsUri The ns attribute uri for the element
	 * @return TrueAction_Dom_Document This document
	 */
	public function addElement($name, $val = null, $nsUri = '')
	{
		$el = $this->appendChild(new TrueAction_Dom_Element($name, '', $nsUri));
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
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
		$el = $this->appendChild(new TrueAction_Dom_Element($name, '', $nsUri));
		if (!is_null($val)) {
			$el->appendChild(is_string($val) ? new DOMCdataSection($val) : $val);
			// Then remove the new element because we didn't really want
			// to attach it.
			$this->removeChild($el);
		}

		return $el;
	}

	/**
	 * create all nodes along a given path relative to the document.
	 * NOTE:
	 * The path '/' will return null and no changes will be made.
	 * The first element in the path will be considered the root.
	 * any nodes along the path that do not exist will be created.
	 * for any element along the path except the leaf (last) element,
	 *  only the first matching node will be traversed if the element matches multiple siblings.
	 * @param string         $path
	 * @param string|DOMNode $value
	 * @param array          $attrs
	 * @return TrueAction_Dom_Element
	 */
	public function setNode($path, $val = null, array $attrs = array()) {
		$path        = ltrim($path, '/');
		$root        = substr($path, 0, strpos($path, '/'));
		$trimmedPath = trim($path, '/');
		$node        = null;
		$xpath       = new DOMXPath($this);
		if ($root) {
			$nodeList = $xpath->query($root);
			if ($root === $trimmedPath) {
				$node = $this->createElement($path, $val);
				$node->addAttributes($attrs);
			} else {
				$nodeList = $xpath->query($root, $this);
				if ($nodeList->length === 0) {
					$startNode = $this->createElement($root);
				} else {
					$startNode = $nodeList->item(0);
				}
				$subPath = substr($path, strpos($path, '/') + 1);
				$node = $startNode->setNode($subPath, $val, $attrs);
			}
		}
		return $node;
	}
}
