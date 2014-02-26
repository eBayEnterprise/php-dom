<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Document.php';

class TrueAction_Dom_Test_DocumentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testUsage()
	{
		$doc = new TrueAction_Dom_Document();
		$root = $doc->appendChild(
			$doc->createElement('testroot')
		);
		$this->assertSame(
			'TrueAction_Dom_Element',
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
	 * @test
	 */
	public function testAddElementWithNs()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-8');
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
	 * @test
	 */
	public function testAddElementException()
	{
		$this->setExpectedException('DOMException', 'The specified path would cause adding a sibling to the root element.');
		$dom = new TrueAction_Dom_Document();
		$dom->addElement('root');
		$dom->addElement('secondRoot');
	}
	/**
	 * @test
	 */
	public function testCreateElementWithNs()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-8');
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
		$doc = new TrueAction_Dom_Document();
		$node = $doc->setNode('/');
		$this->assertSame($doc, $node);
		$node = $doc->setNode('');
		$this->assertNull($node);
		$node = $doc->setNode('foo/bar');
		$this->assertSame($node, $doc->firstChild->firstChild);
		$this->assertSame('<foo><bar></bar></foo>', $doc->C14N());
		$node2 = $doc->setNode('foo/bar');
		$this->assertSame($node2, $doc->firstChild->firstChild->nextSibling);
		$this->assertSame('<foo><bar></bar><bar></bar></foo>', $doc->C14N());
		$this->assertNotSame($node, $node2);
	}

	public function testSetNodeAbsolutePath()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->loadXML('<foo><bar></bar></foo>');
		$doc->setNode('/foo/bar/baz');
		$this->assertSame('<foo><bar><baz></baz></bar></foo>', $doc->C14N());
	}

	public function testSetNodeAbsolutePathWithContext()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->loadXML('<foo><bar></bar></foo>');
		$doc->setNode('/foo/bar/baz', null, $doc->documentElement->firstChild);
		$this->assertSame('<foo><bar><baz></baz></bar></foo>', $doc->C14N());
	}

	public function testSetNodeTrailingSlash()
	{
		$doc  = new TrueAction_Dom_Document();
		$node = $doc->setNode('foo/');
		$this->assertSame($node, $doc->firstChild);
	}

	/**
	 * @expectedException DOMException
	 */
	public function testSetNodeException()
	{
		$doc = new TrueAction_Dom_Document();
		$node = $doc->setNode('bar');
		$node = $doc->setNode('bar');
	}

	/**
	 * @expectedException DOMException
	 */
	public function testSetNodeException2()
	{
		$doc = new TrueAction_Dom_Document();
		$node = $doc->setNode('bar/foo');
		$node = $doc->setNode('biz/foo');
	}

	/**
	 * Attributes in the path should result in attributes being added to the
	 * node.
	 * @test
	 */
	public function testSetNodeCreateAttributes()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo[@name="bar"][@type="baz"]');
		$this->assertSame('<foo name="bar" type="baz"></foo>', $doc->C14N());
	}

	/**
	 * Ensure the value of the last node created is set to the given value
	 * @test
	 */
	public function testSettingNodeValue()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo', 'bar');
		$this->assertSame('<foo>bar</foo>', $doc->C14N());
	}
	/**
	 * When using a context node, the created nodes should be relative to
	 * that node
	 * @test
	 */
	public function testUsingContextNode()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->loadXML('<root><foo></foo></root>');
		$doc->setNode('bar', null, $doc->documentElement->firstChild);

		$this->assertSame(
			'<root><foo><bar></bar></foo></root>',
			$doc->C14N()
		);
	}

	/**
	 * Should be able to set the value of the created node to a DOMNode.
	 * @test
	 */
	public function testValueOfDomNode()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo', $doc->createElement('bar'));
		$this->assertSame(
			'<foo><bar></bar></foo>',
			$doc->C14N()
		);
	}
	/**
	 * Ensure the namespace uri of the created element is set
	 * @test
	 */
	public function testUsingNsUri()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo', null, null, 'http://ns.uri');
		$this->assertSame('http://ns.uri', $doc->documentElement->namespaceURI);
	}
	/**
	 * The namespace uri should only be added to nodes that are created from
	 * the setNode call.
	 * @test
	 */
	public function testUsingNsUriMultipleNodes()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->loadXML('<foo/>');
		$doc->setNode('foo/bar', null, null, 'http://ns.uri');
		$this->assertNotSame('http://ns.uri', $doc->documentElement->namespaceURI);
		$this->assertSame('http://ns.uri', $doc->documentElement->firstChild->namespaceURI);
	}
	/**
	 * All nodes created by setNode should get the namespace uri set.
	 * @test
	 */
	public function testUsingNsUriMultipleCreatedNodes()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo/bar', null, null, 'http://ns.uri');
		$this->assertSame('http://ns.uri', $doc->documentElement->namespaceURI);
		$this->assertSame('http://ns.uri', $doc->documentElement->firstChild->namespaceURI);
	}
	/**
	 * Break a single part of a supported XPath down into an array containing the
	 * node name and an array of attributes to add to the node.
	 * @test
	 */
	public function testParsePathSection()
	{
		$dom = new TrueAction_Dom_Document();
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
	 * @test
	 */
	public function testParsePathSectionNodeNameOnly()
	{
		$dom = new TrueAction_Dom_Document();
		$reflectionMethod = new ReflectionMethod($dom, '_parsePathSection');
		$reflectionMethod->setAccessible(true);

		$this->assertSame(
			array('nodeName', array()),
			$reflectionMethod->invoke($dom, 'nodeName')
		);
	}
	/**
	 * Testing UTF 16 double bytes contents on Dom xml, using addElement
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testUtf16WithAddElement()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-16');
		$data = "\xFE\xFF\x00\x3C\x00\x66\x00\x6F\x00\x6F\x00\x2F\x00\x3E";
		$doc->addElement('root', $data, 'http://api.gsicommerce.com/schema/checkout/1.0');
		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}

	/**
	 * Testing UTF 16 double bytes contents on Dom xml, using loadXML
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testUtf16WithLoadXml()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-16');
		$data = '<root xmlns="http://api.gsicommerce.com/schema/checkout/1.0">' .
			"\xFE\xFF\x00\x3C\x00\x66\x00\x6F\x00\x6F\x00\x2F\x00\x3E" .
			'</root>';
		$doc->loadXML($data);

		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}

	/**
	 * Testing multi-byte UTF-8 contents in Dom elements, using  addElement
	 *
	 * @test
	 */
	public function testMultiByteUtf8AddElement()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-16');
		$data = mb_convert_encoding('This is a multi-byte UTF-8 test', "UTF-8");
		$doc->addElement('root', $data, 'http://api.gsicommerce.com/schema/checkout/1.0');
		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}

	/**
	 * Testing multi-byte UTF-8 contents in Dom elements, using load
	 *
	 * @test
	 */
	public function testMultiByteUtf8WithLoadXml()
	{
		$doc = new TrueAction_Dom_Document('1.0', 'UTF-8');
		$data = '<root xmlns="http://api.gsicommerce.com/schema/checkout/1.0">' .
			mb_convert_encoding('This is a multi-byte UTF-8 test', "UTF-8") .
			'</root>';
		$doc->loadXML($data);

		$this->assertNotEmpty(
			$doc->saveXML()
		);
	}
}
