<?php
namespace Common\Action\Country;

use Common\Action\BaseJsonAction;
use Common\Action\JsonResponse;
use Common\Country\Provider as CountryProvider;
use Common\Hydration\ObjectToArrayHydrator;
use Exception;
use Psr\Http\Message\ResponseInterface;

class GetList extends BaseJsonAction
{
	private CountryProvider $countryProvider;

	public function __construct(CountryProvider $countryProvider)
	{
		$this->countryProvider = $countryProvider;
	}

	/**
	 * @throws Exception
	 */
	public function executeAction(): ResponseInterface
	{
		return JsonResponse::is()
			->successful()
			->data(
				ObjectToArrayHydrator::hydrate(
					$this->countryProvider->all()
				)
			)
			->dispatch();
	}
}
