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

class EbayEnterprise_Dom_Test_ElementTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->doc = new EbayEnterprise_Dom_Document();
		$this->root = $this->doc->appendChild(
			$this->doc->createElement('testroot')
		);
	}

	/**
	 */
	public function testCreateChild()
	{
		$child = $this->root->createChild(
			'testchild',
			'testval',
			array('ref' => '1', 'foo' => 'baz', '_1234' => 'biz', 'id' => '234')
		);
		$this->assertSame('testchild', $child->nodeName);
		$this->assertSame('testval', $child->nodeValue);
		$this->assertSame('1', $child->getAttribute('ref'));
		$this->assertSame(1, count($this->doc->getElementsByTagName('testroot')));
		$this->assertSame(1, count($this->doc->getElementsByTagName('testchild')));
	}

	/**
	 */
	public function testAddChild()
	{
		$node = $this->root->addChild(
			'testchild',
			'testval',
			array('ref' => '1', 'foo' => 'baz', '_1234' => 'biz', 'id' => '234')
		);
		$this->assertSame($this->root, $node);
	}

	/**
	 */
	public function testCreateChildSpecialChars()
	{
		$childA = $this->root->createChild(
			'child1',
			'test<val'
		);
		$this->assertSame('test<val', $childA->textContent);
		$childB = $this->root->createChild(
			'child2',
			'test&val'
		);
		$this->assertSame('test&val', $childB->textContent);
	}

	/**
	 */
	public function testCreateChildDomNode()
	{
		$child = $this->root->createChild('foooo', new DOMText('this is a test'));
		$this->assertSame('foooo', $child->nodeName);
		$this->assertSame('this is a test', $child->textContent);
	}

	/**
	 */
	public function testCreateChildNoOptionals()
	{
		$child = $this->root->createChild('foooo');
		$this->assertSame('foooo', $child->nodeName);
	}

	/**
	 */
	public function testSetAttribute()
	{
		$attr = $this->root->setAttribute('foo', '234', true);
		$this->assertTrue($this->root->getAttributeNode('foo')->isId());
		$this->assertSame($this->root, $this->doc->getElementById('234'));
		$this->assertSame('DOMAttr', get_class($attr));
	}

	/**
	 */
	public function testAddAttribute()
	{
		$el = $this->root->addAttribute('foo', '234', true);
		$this->assertTrue($this->root->getAttributeNode('foo')->isId());
		$this->assertSame($this->root, $this->doc->getElementById('234'));
		$this->assertSame($this->root, $el);
	}

	/**
	 */
	public function testCreateChildWithNsAttribute()
	{
		$this->root->createChild(
			'childElement',
			'test element addChild method',
			array('ref' => '1'),
			'http://api.gsicommerce.com/schema/checkout/1.0'
		);
		$expected = '<?xml version=""?>
<testroot><childElement xmlns="http://api.gsicommerce.com/schema/checkout/1.0" ref="1"><![CDATA[test element addChild method]]></childElement></testroot>';
		$this->assertSame(
			$expected,
			trim($this->doc->saveXML())
		);
	}

	/**
	 * Method should pass through to the owner document's setNode, using itself
	 * as the context node.
	 */
	public function testSetNode()
	{
		$doc = $this->getMockBuilder('EbayEnterprise_Dom_Document')
			->setMethods(array('setNode'))
			->getMock();

		$doc->addElement('testNode');
		$node = $doc->documentElement;

		$createdNode = $this->getMockBuilder('EbayEnterprise_Dom_Element')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$path = 'Some/Path';
		$val = 'Value';
		$nsUri = 'http://ns.uri';

		$doc->expects($this->once())
			->method('setNode')
			->with(
				$this->identicalTo($path),
				$this->identicalTo($val),
				$this->identicalTo($node),
				$this->identicalTo($nsUri)
			)
			->will($this->returnValue($createdNode));

		$this->assertSame($createdNode, $node->setNode($path, $val, $nsUri));
	}

	/**
	 */
	public function testAddAttributes()
	{
		$node = $this->root->addAttributes(
			array('ref' => '1', 'foo' => 'baz', '_1234' => 'biz', 'id' => '234')
		);
		$this->assertSame($this->root, $node);
		$this->assertSame('1', $this->root->getAttribute('ref'));
		$this->assertSame('baz', $this->root->getAttribute('foo'));
		$this->assertSame('biz', $this->root->getAttribute('_1234'));
		$this->assertSame('234', $this->root->getAttribute('id'));
	}

	/**
	 */
	public function testDefaultNs()
	{
		$nsUri = 'http://api.gsicommerce.com/schema/checkout/1.0';
		$p   = $this->root->createChild('p', '', null, $nsUri);
		$pc  = $p->createChild('pc');
		$pc->cascadeDefaultNamespace = false;
		$pcc = $pc->createChild('pcc');
		$this->assertSame($p->namespaceURI, $pc->namespaceURI);
		$this->assertNotSame($pc->namespaceURI, $pcc->namespaceURI);
	}
}
