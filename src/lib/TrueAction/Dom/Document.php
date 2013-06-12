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
	 * create all nodes along a given path relative to the document. if overwrite is
	 * specified, the new node will replace the old one.
	 * NOTE:
	 * The path '/' will return null and no changes will be made.
	 * The first element in the path will be considered the root node.
	 * any nodes along the path that do not exist will be created.
	 * for any element along the path except the leaf (last) element, only the first
	 * matching node will be traversed if the element matches multiple siblings.
	 * likewise, when overwrite is true, only the first matching node will be replaced
	 * if the final element matches multiple siblings.
	 * @param string         $path
	 * @param string|DOMNode $value
	 * @param array          $attrs
	 * @return TrueAction_Dom_Element
	 */
	public function setNode($path, $val = null, $nsUri = '', $overwrite = false)
	{
		$node        = null;
		$xpath       = new DOMXPath($this);
		$path        = trim($path, '/');
		$root        = strpos($path, '/') ? // this only ever return false or N > 0
			substr($path, 0, strpos($path, '/')) : $path;
		if ($root) {
			// is the path to the root node?
			$targetIsRoot = ($root === $path);
			if ($targetIsRoot) {
				if ($this->hasChildNodes()) {
					// is the first element in the path the root node?
					$startNode = ($root === $this->firstChild->nodeName) ?
						$this->firstChild : null;
					if (!$startNode || ($startNode && !$overwrite)) {
						// any starting point other than the root is an error.
						// a path to the root when overwrite is false is also an error since it would
						// create a sibling to the root.
						throw new DOMException(
							'The specified path would cause adding a sibling to the root element.'
						);
					}
					$this->removeChild($this->firstChild);
				}
				$node = $this->addElement($root, $val, $nsUri)->firstChild;
			} else {
				if ($this->hasChildNodes()) {
					if ($root === $this->firstChild->nodeName) {
						$startNode = $this->firstChild;
					} else {
						throw new DOMException(
							'The specified path would cause adding a sibling to the root element.'
						);
					}
				} else {
					$startNode = $this->addElement($root)->firstChild;
				}
				$subPath = substr($path, strpos($path, '/') + 1);
				$node = $startNode->setNode($subPath, $val, null, $nsUri, $overwrite);
			}
		}
		return $node;
	}
}
