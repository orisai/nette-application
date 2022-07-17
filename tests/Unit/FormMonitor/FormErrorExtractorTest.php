<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\FormMonitor;

use Nette\Forms\Form;
use OriNette\Application\FormMonitor\FormErrorExtractor;
use PHPUnit\Framework\TestCase;

final class FormErrorExtractorTest extends TestCase
{

	public function test(): void
	{
		$form = $this->prepareForm();
		$extractor = new FormErrorExtractor();

		self::assertSame(
			[
				'_0' => 'form 1',
				'_1' => 'form 2',
				'a_0' => 'input a 1',
				'a_1' => 'input a 2',
				'b_0' => 'input b 1',
				'container-c_0' => 'input c 1',
				'container-subcontainer-d_0' => 'input d 1',
			],
			$extractor->getErrors($form),
		);
	}

	private function prepareForm(): Form
	{
		$form = new Form();
		$form->addError('form 1');
		$form->addError('form 2');

		$inputA = $form->addText('a');
		$inputA->addError('input a 1');
		$inputA->addError('input a 2');

		$inputB = $form->addText('b');
		$inputB->addError('input b 1');

		$container = $form->addContainer('container');

		$inputC = $container->addText('c');
		$inputC->addError('input c 1');

		$subcontainer = $container->addContainer('subcontainer');

		$inputD = $subcontainer->addText('d');
		$inputD->addError('input d 1');

		return $form;
	}

}
