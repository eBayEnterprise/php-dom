<?php
class TrueAction_Dom_Test_ElementTest extends EcomDev_PHPUnit_Test_Case
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
	public function testCreateChildren()
	{
		$childArgs = array(
			array('test_node', 'test_value', array('ref' => 'ref value')),
			array('test_node2', 'test_value2'),
			array('test_node3')
		);
		$node = $this->root->createChildren($childArgs);
		$this->assertSame($this->root, $node);
		$node = $this->root->firstChild;
		$this->assertSame('test_node', $node->nodeName);
		$this->assertSame('test_value', $node->textContent);
		$this->assertSame('ref value', $node->getAttribute('ref'));
		$node = $node->nextSibling;
		$this->assertSame('test_node2', $node->nodeName);
		$this->assertSame('test_value2', $node->textContent);
		$this->assertFalse($node->hasAttributes());
		$node = $node->nextSibling;
		$this->assertSame('test_node3', $node->nodeName);
		$this->assertSame('', $node->textContent);
		$this->assertFalse($node->hasAttributes());
	}
}
