<?php
class TrueAction_Eb2c_Core_Test_Model_Dom_DocumentTest extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @test
	 */
	public function testUsage()
	{
		$doc = new TrueAction_Eb2c_Core_Model_Dom_Document();
		$root = $doc->appendChild(
			$doc->createElement('testroot')
		);
		$child = $root->createChild(
			'testchild',
			'testval',
			array('ref'=>'1', 'foo'=>'baz', '_1234'=>'biz', 'id'=>'234')
		);
		$child->setIdAttribute('id', true);
		$this->assertSame(1, count($doc->getElementsByTagName('testroot')));
		$this->assertSame(1, count($doc->getElementsByTagName('testchild')));
		$this->assertSame(
			$child,
			$doc->getElementById('234')
		);
	}
}
