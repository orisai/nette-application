<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Doubles;

use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\VoidResponse;

final class IPresenterImpl1 implements IPresenter
{

	public function run(Request $request): Response
	{
		return new VoidResponse();
	}

}
