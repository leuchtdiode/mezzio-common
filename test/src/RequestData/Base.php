<?php
namespace CommonTest\RequestData;

use CommonTest\Base as CommonBase;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Base extends CommonBase
{
	abstract protected function getField();

	abstract protected function getDataClass();

	/**
	 * @param $value
	 * @param $hasErrors
	 * @param $expectedValue
	 */
	#[DataProvider(methodName: 'myDataSet')]
	public function test_values($value, $hasErrors, $expectedValue)
	{
		$dataClass = $this->getDataClass();

		$data = new $dataClass($this->getApplicationServiceLocator());

		$requestData = [
			$this->getField() => $value,
		];

		$values = $data
			->setRequest($this->getRequestData($requestData))
			->getValues();

		$this->assertEquals($hasErrors, $values->hasErrors());
		$this->assertEquals($expectedValue,
			$values->get($this->getField())
				->getValue());
	}

	protected function getRequestData($data): ServerRequestInterface
	{
		return new ServerRequest(
			queryParams: $data
		);
	}

	/**
	 * @return MockObject|ContainerInterface
	 */
	protected function getContainerMock()
	{
		return $this
			->getMockBuilder(ContainerInterface::class)
			->getMock();
	}
}
