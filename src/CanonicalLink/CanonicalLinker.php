<?php declare(strict_types = 1);

namespace OriNette\Application\CanonicalLink;

use Nette\Application\UI\Component;
use Nette\Application\UI\Presenter;
use function array_flip;
use function array_map;
use function array_merge;
use function assert;
use function is_string;

final class CanonicalLinker
{

	/** @var array<int, string> */
	private array $extraParams;

	/**
	 * @param array<int, string> $extraParams
	 */
	public function __construct(array $extraParams = [])
	{
		$this->extraParams = $extraParams;
	}

	public function linkForPresenter(Presenter $presenter): string
	{
		return $presenter->link('//this', $this->getNonCanonicalParams($presenter));
	}

	/**
	 * @return array<string, null>
	 */
	public function getNonCanonicalParams(Presenter $presenter): array
	{
		return array_map(static fn ($val) => null, array_flip($this->extraParams))
			+ $this->getAllPersistentParams($presenter);
	}

	/**
	 * @return array<string, null>
	 */
	private function getAllPersistentParams(Component $component): array
	{
		$paramsByComponent = [];

		$path = $component instanceof Presenter
			? null
			: $component->lookupPath(Presenter::class);

		foreach ($component::getReflection()->getPersistentParams() as $name => $meta) {
			assert(is_string($name));
			$fullName = $path !== null
				? "$path-$name"
				: $name;

			$paramsByComponent[][$fullName] = null;
		}

		foreach ($component->getComponents() as $subcomponent) {
			if (!$subcomponent instanceof Component) {
				continue;
			}

			$paramsByComponent[] = $this->getAllPersistentParams($subcomponent);
		}

		return array_merge(...$paramsByComponent);
	}

}
