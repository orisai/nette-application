<?php declare(strict_types = 1);

namespace OriNette\Application\Mapping;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use function array_keys;
use function array_map;
use function assert;
use function count;
use function implode;
use function sprintf;

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

		$exact = array_keys(
			array_map(
				fn (string $name): string => $this->container->getServiceType($name),
				$services,
			),
			$class,
			true,
		);

		if (count($exact) === 1) {
			return $services[$exact[0]];
		}

		throw new InvalidPresenterException(sprintf(
			'Multiple services of type "%s" found: %s.',
			$class,
			implode(', ', $services),
		));
	}

	/**
	 * @param class-string<IPresenter> $class
	 */
	public function __invoke(string $class): IPresenter
	{
		$services = $this->container->findByType($class);

		if ($services === []) {
			throw new InvalidPresenterException(sprintf(
				'Presenter "%s" is not registered as a service.',
				$class,
			));
		}

		$service = $this->container->createService(
			$this->getServiceName($services, $class),
		);
		assert($service instanceof IPresenter);

		return $service;
	}

}
