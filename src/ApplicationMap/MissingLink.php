<?php declare(strict_types = 1);

namespace OriNette\Application\ApplicationMap;

/**
 * @internal
 */
final class MissingLink
{

	private bool $routeMissing;

	public function __construct(bool $routeMissing = false)
	{
		$this->routeMissing = $routeMissing;
	}

	public function isRouteMissing(): bool
	{
		return $this->routeMissing;
	}

}
