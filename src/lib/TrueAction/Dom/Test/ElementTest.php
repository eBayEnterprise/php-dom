<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Document.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Element.php';

class TrueAction_Dom_Test_ElementTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->doc = new TrueAction_Dom_Document();
		$this->root = $this->doc->appendChild(
			$this->doc->createElement('testroot')
		);
	}

	/**
	 * @test
	 */
	public function testCreateChild()
	{
		$child = $this->root->createChild(
			'testchild',
			'testval',
			array('ref'=>'1', 'foo'=>'baz', '_1234'=>'biz', 'id'=>'234')
		);
		$this->assertSame('testchild', $child->nodeName);
		$this->assertSame('testval', $child->nodeValue);
		$this->assertSame('1', $child->getAttribute('ref'));
		$this->assertSame(1, count($this->doc->getElementsByTagName('testroot')));
		$this->assertSame(1, count($this->doc->getElementsByTagName('testchild')));
	}

	/**
	 * @test
	 */
	public function testAddChild()
	{
		$node = $this->root->addChild(
			'testchild',
			'testval',
			array('ref'=>'1', 'foo'=>'baz', '_1234'=>'biz', 'id'=>'234')
		);
		$this->assertSame($this->root, $node);
	}


	/**
	 * @test
	 */
	public function testCreateChildSpecialChars()
	{
		$child1 = $this->root->createChild(
			'child1',
			'test<val'
		);
		$this->assertSame('test<val', $child1->textContent);
		$child2 = $this->root->createChild(
			'child2',
			'test&val'
		);
		$this->assertSame('test&val', $child2->textContent);
	}

	/**
	 * @test
	 */
	public function testCreateChildDomNode()
	{
		$child = $this->root->createChild('foooo', new DOMText('this is a test'));
		$this->assertSame('foooo', $child->nodeName);
		$this->assertSame('this is a test', $child->textContent);
	}


	/**
	 * @test
	 */
	public function testCreateChildNoOptionals()
	{
		$child = $this->root->createChild('foooo');
		$this->assertSame('foooo', $child->nodeName);
	}

	/**
	 * @test
	 */
	public function testSetAttribute()
	{
		$attr = $this->root->setAttribute('foo', '234', true);
		$this->assertTrue($this->root->getAttributeNode('foo')->isId());
		$this->assertSame($this->root, $this->doc->getElementById('234'));
		$this->assertSame('DOMAttr', get_class($attr));
	}

	/**
	 * @test
	 */
	public function testAddAttribute()
	{
		$el = $this->root->addAttribute('foo', '234', true);
		$this->assertTrue($this->root->getAttributeNode('foo')->isId());
		$this->assertSame($this->root, $this->doc->getElementById('234'));
		$this->assertSame($this->root, $el);
	}

	/**
	 * @test
	 */
	public function testCreateChildWithNsAttribute()
	{
		$this->root->createChild(
			'childElement',
			'test element addChild method',
			array('ref'=>'1'),
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
	 * @test
	 */
	public function testSetNode()
	{
		$node = $this->root->setNode('foo');
		$this->assertSame($this->root->firstChild, $node);
		$node = $this->root->setNode('foo/bar/baz');
		$this->assertSame($node, $this->root->firstChild->firstChild->firstChild);
		$this->root->createChild('foo');
		$this->assertSame('foo', $this->root->firstChild->nextSibling->nodeName);
		$node = $this->root->setNode('foo/bar/biz');
		$this->assertSame($node, $this->root->firstChild->firstChild->firstChild->nextSibling);
		$this->assertSame('biz', $this->root->firstChild->firstChild->firstChild->nextSibling->nodeName);
		$node = $this->root->setNode('foo/bar/biz', 'bizval', array('a' => 'a val'));
		$this->assertSame(
			'biz',
			$this->root->firstChild->firstChild->firstChild->nextSibling->nextSibling->nodeName
		);
		$this->assertNotSame(
			$this->root->firstChild->firstChild->firstChild->nextSibling,
			$this->root->firstChild->firstChild->firstChild->nextSibling->nextSibling
		);
		$this->assertSame(
			'bizval',
			$this->root->firstChild->firstChild->firstChild->nextSibling->nextSibling->textContent
		);
		$this->assertSame(
			'a val',
			$this->root->firstChild->firstChild->firstChild->nextSibling->nextSibling->getAttribute('a')
		);
		$node = $this->root->setNode('foo3/');
		$this->assertSame($node, $this->root->firstChild->nextSibling->nextSibling);
	}

	/**
	 * @test
	 */
	public function testSetNodeOverwrite()
	{
		$node = $this->root->setNode('foo');
		$foo  = $this->root->setNode('foo', 'someval', null, '', true);
		$this->assertSame($this->root->firstChild->textContent, 'someval');

		$node = $this->root->setNode('foo/bar', '', null, '', true);
		$this->assertSame($foo, $this->root->firstChild);
		$this->assertSame($node, $this->root->firstChild->firstChild->nextSibling);

		$bar  = $this->root->setNode('foo/bar', 'somesecondval', null, '', true);
		$this->assertSame($bar, $this->root->firstChild->firstChild->nextSibling);
		$this->assertNotSame($node, $bar);
	}

	/**
	 * @test
	 */
	public function testAddAttributes()
	{
		$node = $this->root->addAttributes(
			array('ref'=>'1', 'foo'=>'baz', '_1234'=>'biz', 'id'=>'234')
		);
		$this->assertSame($this->root, $node);
		$this->assertSame('1', $this->root->getAttribute('ref'));
		$this->assertSame('baz', $this->root->getAttribute('foo'));
		$this->assertSame('biz', $this->root->getAttribute('_1234'));
		$this->assertSame('234', $this->root->getAttribute('id'));
	}

	public function testValueCoersion()
	{
		$this->root->createChild('number', 4);
		$this->root->createChild('object', array());
		$this->root->createChild('node', new DOMElement('thenode'));
	}

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
