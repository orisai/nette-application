<?php declare(strict_types = 1);

namespace OriNette\Application\ApplicationMap;

use Nette\Application\IPresenter;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;
use OriNette\Application\Mapping\PresenterFactory;
use ReflectionClass;
use ReflectionMethod;
use function get_class;
use function is_a;
use function is_string;
use function lcfirst;
use function sort;
use function str_contains;
use function str_replace;

/**
 * @internal
 */
final class ApplicationMap
{

	/** @var array<int|string, class-string<IPresenter>> */
	private array $presenterClasses;

	private Cache $cache;

	private Container $container;

	private PresenterFactory $presenterFactory;

	private LinkGeneratingPresenter $presenter;

	/**
	 * @param array<int|string, class-string<IPresenter>> $presenterClasses
	 */
	public function __construct(
		array $presenterClasses,
		Storage $storage,
		Container $container,
		PresenterFactory $presenterFactory,
		LinkGeneratingPresenter $presenter
	)
	{
		$this->presenterClasses = $presenterClasses;
		$this->cache = new Cache($storage, 'orisai.application.map');
		$this->container = $container;
		$this->presenterFactory = $presenterFactory;
		$this->presenter = $presenter;
		$presenter->invalidLinkMode = $presenter::INVALID_LINK_EXCEPTION;
	}

	/**
	 * @return array<PresenterMeta>
	 */
	public function getAll(): array
	{
		$cacheKey = get_class($this->container);

		/** @var array<PresenterMeta>|null $metas */
		$metas = $this->cache->load($cacheKey);

		if ($metas !== null) {
			return $metas;
		}

		sort($this->presenterClasses);
		$metas = [];
		foreach ($this->presenterClasses as $presenterClass) {
			$meta = $metas[$presenterClass] ?? null;
			if ($meta === null) {
				$metas[$presenterClass] = $meta = new PresenterMeta($presenterClass);
			}

			$presenterName = $this->presenterFactory->getPresenterName($presenterClass);

			if ($presenterName !== $presenterClass) {
				$meta->setMappedName($presenterName);
			}

			if (is_a($presenterClass, Presenter::class, true)) {
				$this->handleUiPresenter($presenterClass, $presenterName, $meta);
			} else {
				$this->handleIPresenter($presenterName, $meta);
			}
		}

		$this->cache->save($cacheKey, $metas, [
			$this->cache::FILES => [
				(new ReflectionClass($this->container))->getFileName(),
			],
			$this->cache::EXPIRE => '1 week',
		]);

		return $metas;
	}

	/**
	 * @param class-string<Presenter> $presenterClass
	 */
	private function handleUiPresenter(string $presenterClass, string $presenterName, PresenterMeta $meta): void
	{
		$actionMethodBaseName = $presenterClass::formatActionMethod('');
		$renderMethodBaseName = $presenterClass::formatRenderMethod('');

		foreach ((new ReflectionClass($presenterClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $methodReflection) {
			$methodName = $methodReflection->getShortName();

			if (str_contains($methodName, $actionMethodBaseName)) {
				$action = str_replace($actionMethodBaseName, '', $methodName);
				$actionMethodName = $presenterClass::formatActionMethod($action);
				$action = lcfirst($action);

				if ($methodName === $actionMethodName) {
					$this->generateLink($meta, $action, ":$presenterName:$action", $methodReflection);

					continue;
				}
			}

			if (str_contains($methodName, $renderMethodBaseName)) {
				$action = str_replace($renderMethodBaseName, '', $methodName);
				if (!$meta->hasAction($action)) {
					$renderMethodName = $presenterClass::formatRenderMethod($action);
					$action = lcfirst($action);

					if ($methodName === $renderMethodName) {
						$this->generateLink($meta, $action, ":$presenterName:$action", $methodReflection);
					}
				}
			}
		}

		if (!$meta->hasAction('default') && !$meta->hasAction('')) {
			$this->generateLink($meta, 'default', ":$presenterName:default", null, false);
		}
	}

	private function handleIPresenter(string $presenterName, PresenterMeta $meta): void
	{
		$this->generateLink($meta, '__invoke', ":$presenterName:", null);
	}

	private function generateLink(
		PresenterMeta $meta,
		string $action,
		string $destination,
		?ReflectionMethod $methodReflection,
		bool $addWithoutLink = true
	): void
	{
		try {
			$link = $this->presenter->link($destination);
		} catch (InvalidLinkException $exception) {
			$link = $methodReflection !== null && $methodReflection->getNumberOfRequiredParameters() === 0
				? new MissingLink(true)
				: new MissingLink();
		}

		if (is_string($link) || $addWithoutLink) {
			$meta->addActionLinkPair($action, $link);
		}
	}

}
