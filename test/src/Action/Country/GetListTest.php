<?php
namespace CommonTest\Action\Country;

use Exception;
use CommonTest\Base;

class GetListTest extends Base
{
	/**
	 * @throws Exception
	 */
	public function test_correct_output()
	{
		$response = $this->dispatch('/common/country');

		$this->assertEquals(200, $response->getStatusCode());

		$this->assertJsonStringEqualsJsonFile(
			__DIR__ . '/get-list-response.json',
			$response
				->getBody()
				->getContents()
		);
	}
}