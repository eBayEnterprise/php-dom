<?php
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
		$this->assertNull($node);
		$node = $doc->setNode('');
		$this->assertNull($node);
		$node = $doc->setNode('foo/bar');
		$this->assertSame($node, $doc->firstChild->firstChild);
		$node2 = $doc->setNode('foo/bar');
		$this->assertSame($node2, $doc->firstChild->firstChild->nextSibling);
		$this->assertNotSame($node, $node2);
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

	public function testSetNodeOverwrite()
	{
		$doc = new TrueAction_Dom_Document();
		$doc->setNode('foo', 'oldfoo');
		$node = $doc->setNode('foo', 'newfoo', '', true);
		$this->assertSame($node, $doc->firstChild);
		$this->assertSame('newfoo', $node->textContent);
	}
}
