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

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Document.php';

class EbayEnterprise_Dom_Test_DocumentTest extends PHPUnit_Framework_TestCase
{
	/**
	 */
	public function testUsage()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$root = $doc->appendChild(
			$doc->createElement('testroot')
		);
		$this->assertSame(
			'EbayEnterprise_Dom_Element',
			get_class($root)
		);
		$this->assertTrue(
			$doc->hasChildNodes()
		);
		$this->assertSame(
			'testroot',
			$doc->firstChild->nodeName
		);
	}

	/**
	 */
	public function testAddElementWithNs()
	{
		$doc = new EbayEnterprise_Dom_Document('1.0', 'UTF-8');
		$expected = '<?xml version="1.0" encoding="UTF-8"?>
<root xmlns="http://api.gsicommerce.com/schema/checkout/1.0"><![CDATA[test with addElement method]]></root>';

		$doc->addElement('root', 'test with addElement method', 'http://api.gsicommerce.com/schema/checkout/1.0');
		$this->assertSame(
			$expected,
			trim($doc->saveXML())
		);
	}
	/**
	 * Attemtping to add multiple root nodes should throw an exception.
	 */
	public function testAddElementException()
	{
		$this->setExpectedException('DOMException', 'The specified path would cause adding a sibling to the root element.');
		$dom = new EbayEnterprise_Dom_Document();
		$dom->addElement('root');
		$dom->addElement('secondRoot');
	}
	/**
	 */
	public function testCreateElementWithNs()
	{
		$doc = new EbayEnterprise_Dom_Document('1.0', 'UTF-8');
		$expected = '<?xml version="1.0" encoding="UTF-8"?>
<root xmlns="http://api.gsicommerce.com/schema/checkout/1.0"><![CDATA[test with CreateElement method]]></root>';

		$doc->appendChild(
			$doc->createElement('root', 'test with CreateElement method', 'http://api.gsicommerce.com/schema/checkout/1.0')
		);
		$this->assertSame(
			$expected,
			trim($doc->saveXML())
		);
	}

	public function testSetNode()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$node = $doc->setNode('/');
		$this->assertSame($doc, $node);
		$node = $doc->setNode('');
		$this->assertNull($node);
		$node = $doc->setNode('foo/bar');
		$this->assertSame($node, $doc->firstChild->firstChild);
		$this->assertSame('<foo><bar></bar></foo>', $doc->C14N());
		$nodeB = $doc->setNode('foo/bar');
		$this->assertSame($nodeB, $doc->firstChild->firstChild->nextSibling);
		$this->assertSame('<foo><bar></bar><bar></bar></foo>', $doc->C14N());
		$this->assertNotSame($node, $nodeB);
	}

	public function testSetNodeAbsolutePath()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->loadXML('<foo><bar></bar></foo>');
		$doc->setNode('/foo/bar/baz');
		$this->assertSame('<foo><bar><baz></baz></bar></foo>', $doc->C14N());
	}

	public function testSetNodeAbsolutePathWithContext()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->loadXML('<foo><bar></bar></foo>');
		$doc->setNode('/foo/bar/baz', null, $doc->documentElement->firstChild);
		$this->assertSame('<foo><bar><baz></baz></bar></foo>', $doc->C14N());
	}

	public function testSetNodeTrailingSlash()
	{
		$doc  = new EbayEnterprise_Dom_Document();
		$node = $doc->setNode('foo/');
		$this->assertSame($node, $doc->firstChild);

		/* These should be the same - trail / means 'reuse existing': */
		$node = $doc->setNode('foo/');
		$this->assertSame($node, $doc->firstChild);
	}

	/**
	 * @expectedException DOMException
	 */
	public function testSetNodeException()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$node = $doc->setNode('bar');
		$node = $doc->setNode('bar');
	}

	/**
	 * @expectedException DOMException
	 */
	public function testSetNodeException2()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$node = $doc->setNode('bar/foo');
		$node = $doc->setNode('biz/foo');
	}

	/**
	 * Attributes in the path should result in attributes being added to the
	 * node. Also ensures that quoted '/'s are not used to split the XPath
	 */
	public function testSetNodeCreateAttributes()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->setNode('foo[@name="bar"][@href="http://example.com/"]/baz');
		$this->assertSame('<foo href="http://example.com/" name="bar"><baz></baz></foo>', $doc->C14N());
	}

	/**
	 * Ensure the value of the last node created is set to the given value
	 */
	public function testSettingNodeValue()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->setNode('foo', 'bar');
		$this->assertSame('<foo>bar</foo>', $doc->C14N());
	}
	/**
	 * When using a context node, the created nodes should be relative to
	 * that node
	 */
	public function testUsingContextNode()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->loadXML('<root><foo></foo></root>');
		$doc->setNode('bar', null, $doc->documentElement->firstChild);

		$this->assertSame(
			'<root><foo><bar></bar></foo></root>',
			$doc->C14N()
		);
	}

	/**
	 * Should be able to set the value of the created node to a DOMNode.
	 */
	public function testValueOfDomNode()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->setNode('foo', $doc->createElement('bar'));
		$this->assertSame(
			'<foo><bar></bar></foo>',
			$doc->C14N()
		);
	}
	/**
	 * Ensure the namespace uri of the created element is set
	 */
	public function testUsingNsUri()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->setNode('foo', null, null, 'http://ns.uri');
		$this->assertSame('http://ns.uri', $doc->documentElement->namespaceURI);
	}
	/**
	 * The namespace uri should only be added to nodes that are created from
	 * the setNode call.
	 */
	public function testUsingNsUriMultipleNodes()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->loadXML('<foo/>');
		$doc->setNode('foo/bar', null, null, 'http://ns.uri');
		$this->assertNotSame('http://ns.uri', $doc->documentElement->namespaceURI);
		$this->assertSame('http://ns.uri', $doc->documentElement->firstChild->namespaceURI);
	}
	/**
	 * All nodes created by setNode should get the namespace uri set.
	 */
	public function testUsingNsUriMultipleCreatedNodes()
	{
		$doc = new EbayEnterprise_Dom_Document();
		$doc->setNode('foo/bar', null, null, 'http://ns.uri');
		$this->assertSame('http://ns.uri', $doc->documentElement->namespaceURI);
		$this->assertSame('http://ns.uri', $doc->documentElement->firstChild->namespaceURI);
	}
	/**
	 * Break a single part of a supported XPath down into an array containing the
	 * node name and an array of attributes to add to the node.
	 */
	public function testParsePathSection()
	{
		$dom = new EbayEnterprise_Dom_Document();
		$reflectionMethod = new ReflectionMethod($dom, '_parsePathSection');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(
			array('nodeName', array('attrOne' => 'one', 'attrTwo' => 'two')),
			$reflectionMethod->invoke($dom, 'nodeName[@attrOne="one"][@attrTwo="two"]')
		);
	}
	/**
	 * When the path section only consists of a node name, should return an
	 * empty array for attributes.
	 */
	public function testParsePathSectionNodeNameOnly()
	{
		$dom = new EbayEnterprise_Dom_Document();
		$reflectionMethod = new ReflectionMethod($dom, '_parsePathSection');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(
			array('nodeName', array()),
			$reflectionMethod->invoke($dom, 'nodeName')
		);
	}

	/**
	 * Testing multi-byte UTF-8 contents in Dom elements, using  addElement
	 *
	 */
	public function testMultiByteUtf8AddElement()
	{
		$doc = new EbayEnterprise_Dom_Document('1.0', 'UTF-16');
		$data = mb_convert_encoding('This is a multi-byte UTF-8 test', 'UTF-8');
		$doc->addElement('root', $data, 'http://api.gsicommerce.com/schema/checkout/1.0');
		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}

	/**
	 * Testing multi-byte UTF-8 contents in Dom elements, using load
	 *
	 */
	public function testMultiByteUtf8WithLoadXml()
	{
		$doc = new EbayEnterprise_Dom_Document('1.0', 'UTF-8');
		$data = '<root xmlns="http://api.gsicommerce.com/schema/checkout/1.0">' .
			mb_convert_encoding('This is a multi-byte UTF-8 test', 'UTF-8') .
			'</root>';
		$doc->loadXML($data);

		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}
}
