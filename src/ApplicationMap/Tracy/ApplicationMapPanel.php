<?php declare(strict_types = 1);

namespace OriNette\Application\ApplicationMap\Tracy;

use OriNette\Application\ApplicationMap\ApplicationMap;
use Tracy\Helpers;
use Tracy\IBarPanel;

/**
 * @internal
 */
final class ApplicationMapPanel implements IBarPanel
{

	private ApplicationMap $map;

	public function __construct(ApplicationMap $map)
	{
		$this->map = $map;
	}

	public function getTab(): string
	{
		return Helpers::capture(static function (): void {
			require __DIR__ . '/ApplicationMap.tab.phtml';
		});
	}

	public function getPanel(): string
	{
		return Helpers::capture(function (): void {
			// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
			$map = $this->map;
			// phpcs:enable

			require __DIR__ . '/ApplicationMap.panel.phtml';
		});
	}

}
