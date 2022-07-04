<?php declare(strict_types = 1);

namespace OriNette\Application\Mapping\DI;

use Nette\Application\IPresenterFactory;
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\Mapping\DefaultPresenterFactory;
use OriNette\Application\Mapping\PresenterFactory;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use stdClass;
use function assert;

/**
 * @property-read stdClass $config
 */
final class PresenterFactoryExtension extends CompilerExtension
{

	private ServiceDefinition $presenterConstructorDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'presenterConstructor' => Expect::anyOf('strict', null)->default(null),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if ($config->presenterConstructor === 'strict') {
			$this->presenterConstructorDefinition = $builder->addDefinition($this->prefix('presenterConstructor'))
				->setFactory(StrictPresenterFactoryCallback::class)
				->setAutowired(false);
		}
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$presenterFactoryName = $builder->getByType(IPresenterFactory::class);

		if ($presenterFactoryName === null) {
			$factoryClass = IPresenterFactory::class;
			$extensionClass = ApplicationExtension::class;
			$message = Message::create()
				->withContext("Registration of custom $factoryClass failed.")
				->withProblem("Required extension $extensionClass is not registered.")
				->withSolution('Register required extension.');

			throw InvalidState::create()
				->withMessage($message);
		}

		$presenterFactoryDefinition = $builder->getDefinition($presenterFactoryName);
		assert($presenterFactoryDefinition instanceof ServiceDefinition);

		$originalConstructor = $presenterFactoryDefinition->getFactory()->arguments[0];

		$presenterFactoryDefinition->setFactory(DefaultPresenterFactory::class)
			->setType(PresenterFactory::class)
			->setArgument(
				'factory',
				$config->presenterConstructor === 'strict'
					? $this->presenterConstructorDefinition
					: $originalConstructor,
			);
	}

}
