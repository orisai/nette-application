<?php declare(strict_types = 1);

namespace OriNette\Application\Mapping;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use function array_search;
use function assert;
use function count;
use function implode;

final class StrictPresenterFactoryCallback
{

	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param array<string> $services
	 */
	private function getServiceName(array $services, string $class): string
	{
		if (count($services) === 1) {
			return $services[0];
		}

		$exact = [];
		foreach ($services as $service) {
			$serviceType = $this->container->getServiceType($service);
			if ($serviceType === $class) {
				$exact[] = $service;
			}
		}

		if (count($exact) === 1) {
			$i = array_search($exact[0], $services, true);

			return $services[$i];
		}

		$servicesInline = implode(', ', $exact !== [] ? $exact : $services);

		throw new InvalidPresenterException(
			"Multiple services of type '$class' found: $servicesInline.",
		);
	}

	/**
	 * @param class-string<IPresenter> $class
	 */
	public function __invoke(string $class): IPresenter
	{
		$services = $this->container->findByType($class);

		if ($services === []) {
			throw new InvalidPresenterException(
				"Presenter '$class' is not registered as a service.",
			);
		}

		$service = $this->container->createService(
			$this->getServiceName($services, $class),
		);
		assert($service instanceof IPresenter);

		return $service;
	}

}
