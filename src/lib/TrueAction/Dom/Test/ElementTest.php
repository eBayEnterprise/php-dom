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
	public function testSetAttribute()
	{
		$this->root->setAttribute('foo', '234', true);
		$this->assertTrue($this->root->getAttributeNode('foo')->isId());
		$this->assertSame($this->root, $this->doc->getElementById('234'));
	}
}
