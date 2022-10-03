<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Doubles;

use Nette\Application\UI\Presenter;
use OriNette\Application\Presenter\ShortDefaultActionName;

final class ShortDefaultActionNamePresenter extends Presenter
{

	use ShortDefaultActionName;

}
