<?php declare(strict_types = 1);

namespace OriNette\Application\Mapping;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\Utils\Strings;
use Orisai\Exceptions\Logic\InvalidArgument;
use ReflectionClass;
use function array_pop;
use function array_search;
use function array_unshift;
use function class_exists;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function strpos;
use function substr;
use function substr_count;
use function trim;
use function uksort;

final class DefaultPresenterFactory implements PresenterFactory
{

	/** @var callable(class-string): IPresenter */
	private $factory;

	/** @var array<string, class-string<IPresenter>> */
	private array $presenterClassCache = [];

	/** @var array<string, array<string>> module => split mask */
	private array $moduleMapping = [
		'' => ['', '*Module\\', '*Presenter'],
		'Nette' => ['NetteModule\\', '*\\', '*Presenter'],
	];

	/** @var array<string, class-string<IPresenter>> presenter name => class */
	private array $presenterMapping = [];

	/**
	 * @param callable(class-string): IPresenter $factory
	 */
	public function __construct(callable $factory)
	{
		$this->factory = $factory;
	}

	public function createPresenter(string $name): IPresenter
	{
		return ($this->factory)($this->getPresenterClass($name));
	}

	/**
	 * @param array<string, string|array<string>> $mapping
	 */
	public function setMapping(array $mapping): void
	{
		foreach ($mapping as $name => $mask) {
			if (is_string($mask) && strpos($mask, '*') === false) {
				$this->setPresenterMapping($name, $mask);
			} else {
				$this->setModuleMapping(rtrim($name, '*'), $mask);
			}
		}
	}

	public function setPresenterMapping(string $presenter, string $class): void
	{
		$presenter = trim($presenter, ':');

		if (!class_exists($class)) {
			throw InvalidArgument::create()
				->withMessage(
					"Cannot set presenter mapping for class '$class' because class was not found.",
				);
		}

		$reflection = new ReflectionClass($class);

		if (!$reflection->implementsInterface(IPresenter::class)) {
			$presenterClass = IPresenter::class;

			throw InvalidArgument::create()
				->withMessage(
					"Cannot set presenter mapping for class '$class' because class is not $presenterClass implementor.",
				);
		}

		if ($reflection->isAbstract()) {
			throw InvalidArgument::create()
				->withMessage(
					"Cannot set presenter mapping for class '$class' because class is abstract.",
				);
		}

		$this->presenterMapping[$presenter] = $reflection->getName();
	}

	/**
	 * @param string|array<string> $mask
	 */
	public function setModuleMapping(string $module, $mask): void
	{
		$module = trim($module, ':');
		if (is_array($mask) && count($mask) === 3) {
			$this->moduleMapping[$module] = [$mask[0] !== '' ? $mask[0] . '\\' : '', $mask[1] . '\\', $mask[2]];
		} elseif (is_string($mask)) {
			$m = Strings::match($mask, '#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#');
			if ($m === null) {
				throw InvalidArgument::create()
					->withMessage("Invalid mapping mask '$mask' for module '$module'.");
			}

			$this->moduleMapping[$module] = [$m[1], $m[2] ?? '*Module\\', $m[3]];
		} else {
			throw InvalidArgument::create()
				->withMessage("Invalid mapping mask for module '$module'.");
		}

		uksort(
			$this->moduleMapping,
			static fn (string $a, string $b): int => [substr_count($b, ':'), $b] <=> [substr_count($a, ':'), $a],
		);
	}

	public function getPresenterClass(string &$name): string
	{
		if (str_starts_with($name, ':')) {
			$name = substr($name, 1);
		}

		if (isset($this->presenterClassCache[$name])) {
			return $this->presenterClassCache[$name];
		}

		// Mapping for given name is overridden
		if (isset($this->presenterMapping[$name])) {
			return $this->presenterClassCache[$name] = $this->presenterMapping[$name];
		}

		if (class_exists($name)) {
			$class = $name;
		} else {
			if (Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#D') === null) {
				throw new InvalidPresenterException(
					"Presenter name must be alphanumeric string or class, '$name' is invalid.",
				);
			}

			$class = $this->formatMappedPresenterClass($name);

			if (!class_exists($class)) {
				throw new InvalidPresenterException(
					"Cannot load presenter '$name', class '$class' was not found.",
				);
			}
		}

		$reflection = new ReflectionClass($class);

		if (!$reflection->implementsInterface(IPresenter::class)) {
			$presenterClass = IPresenter::class;

			throw new InvalidPresenterException(
				"Cannot load presenter '$name', class '$class' is not $presenterClass implementor.",
			);
		}

		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException(
				"Cannot load presenter '$name', class '$class' is abstract.",
			);
		}

		return $this->presenterClassCache[$name] = $reflection->getName();
	}

	private function formatMappedPresenterClass(string $name): string
	{
		$parts = explode(':', $name);
		$presenterName = array_pop($parts);
		$modules = [];
		while (!isset($this->moduleMapping[implode(':', $parts)])) {
			array_unshift($modules, (string) array_pop($parts));
		}

		$mapping = $this->moduleMapping[implode(':', $parts)];

		$class = $mapping[0];
		foreach ($modules as $module) {
			$class .= str_replace('*', $module, $mapping[1]);
		}

		$class .= str_replace('*', $presenterName, $mapping[2]);

		return $class;
	}

	public function getPresenterName(string $class): string
	{
		$class = (new ReflectionClass($class))->getName();

		// Mapping for given class is overridden
		$presenter = array_search($class, $this->presenterMapping, true);
		if ($presenter !== false) {
			return $presenter;
		}

		$name = $this->formatMappedPresenterName($class);

		// No mapping, class has to be returned
		if ($name === null) {
			return $class;
		}

		// Name cannot be mapped to class, class has to be returned
		try {
			$mappedClass = $this->getPresenterClass($name);
		} catch (InvalidPresenterException $exception) {
			return $class;
		}

		// Mapping name to class returns the same class, we can use it for mapping
		if ($class === $mappedClass) {
			return $class;
		}

		return $name;
	}

	/**
	 * @param class-string<IPresenter> $class
	 */
	private function formatMappedPresenterName(string $class): ?string
	{
		foreach ($this->moduleMapping as $module => $mapping) {
			$mapping = str_replace(['\\', '*'], ['\\\\', '(\w+)'], $mapping);
			$matches = Strings::match($class, "#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i");
			if ($matches !== null) {
				return ($module === '' ? '' : $module . ':') . Strings::replace(
					$matches[1],
					"#$mapping[1]#iA",
					'$1:',
				) . $matches[3];
			}
		}

		return null;
	}

}
