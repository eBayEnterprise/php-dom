<?php
class TrueAction_Dom_Test_DocumentTest extends EcomDev_PHPUnit_Test_Case
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
}
