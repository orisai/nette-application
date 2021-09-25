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
use function lcfirst;
use function str_contains;
use function str_replace;

/**
 * @internal
 */
final class ApplicationMap
{

	/** @var array<int|string, class-string<IPresenter>> */
	private array $presenterNames;

	private Cache $cache;

	private Container $container;

	private PresenterFactory $presenterFactory;

	private LinkGeneratingPresenter $presenter;

	/**
	 * @param array<int|string, class-string<IPresenter>> $presenterNames
	 */
	public function __construct(
		array $presenterNames,
		Storage $storage,
		Container $container,
		PresenterFactory $presenterFactory,
		LinkGeneratingPresenter $presenter
	)
	{
		$this->presenterNames = $presenterNames;
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

		$metas = [];
		foreach ($this->presenterNames as $presenterClass) {
			$meta = $metas[$presenterClass] ?? null;
			if ($meta === null) {
				$metas[$presenterClass] = $meta = new PresenterMeta($presenterClass);
			}

			$presenterName = $this->presenterFactory->getPresenterName($presenterClass);

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
					$this->generateLink($meta, $action, ":$presenterName:$action");

					continue;
				}
			}

			if (str_contains($methodName, $renderMethodBaseName)) {
				$action = str_replace($renderMethodBaseName, '', $methodName);
				$renderMethodName = $presenterClass::formatRenderMethod($action);
				$action = lcfirst($action);

				if ($methodName === $renderMethodName) {
					$this->generateLink($meta, $action, ":$presenterName:$action");
				}
			}
		}

		if (!$meta->hasAction('default') && !$meta->hasAction('')) {
			$this->generateLink($meta, 'default', ":$presenterName:default", false);
		}
	}

	private function handleIPresenter(string $presenterName, PresenterMeta $meta): void
	{
		$this->generateLink($meta, '__invoke', ":$presenterName:");
	}

	private function generateLink(
		PresenterMeta $meta,
		string $action,
		string $destination,
		bool $addWithoutLink = true
	): void
	{
		try {
			$link = $this->presenter->link($destination);
		} catch (InvalidLinkException $exception) {
			$link = null;
		}

		if ($link !== null || $addWithoutLink) {
			$meta->addActionUrlPair($action, $link);
		}
	}

}
