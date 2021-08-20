<?php declare(strict_types = 1);

namespace Extension\ComponentInspector;

use Nette\Application\UI\Control;
use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template as LatteTemplate;
use Nette\Bridges\ApplicationLatte\TemplateFactory as LatteTemplateFactory;

final class InspectorTemplateFactory implements TemplateFactory
{

	private LatteTemplateFactory $templateFactory;

	public function __construct(LatteTemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}

	public function createTemplate(?Control $control = null): LatteTemplate
	{
		return $this->templateFactory->createTemplate($control, InspectorTemplate::class);
	}

}
