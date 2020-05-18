<?php


namespace IQnection\PageBuilder;

use SilverStripe\View\Requirements;

class PageBuilderController extends \PageController
{
	private static $allowed_actions = [
		'pageBuilderExport' => 'ADMIN'
	];
	
	public function init()
	{
		parent::init();
		$customCss = $this->getCustomCSS();
		$css = null;
		if (count($customCss['Large']))
		{
			$largeCss = [];
			foreach($customCss['Large'] as $element => $styles)
			{
				$largeCss[] = $element." {\n\t".implode(";\n\t",$styles).";\n}";
			}
			$css .= implode("\n",$largeCss)."\n";
		}
		if (count($customCss['Medium']))
		{
			$mediumCss = [];
			foreach($customCss['Medium'] as $element => $styles)
			{
				$mediumCss[] = $element." {\n\t\t".implode(";\n\t\t",$styles).";\n\t}";
			}
			$css .= "@media(max-width:800px) {\n\t".implode("\n\t",$mediumCss)."\n}\n";
		}
		if (count($customCss['Small']))
		{
			$smallCss = [];
			foreach($customCss['Small'] as $element => $styles)
			{
				$smallCss[] = $element." {\n\t\t".implode(";\n\t\t",$styles).";\n\t}";
			}
			$css .= "@media(max-width:500px) {\n\t".implode("\n\t",$smallCss)."\n}\n";
		}
		if ($css)
		{
			Requirements::customCSS($css);
		}
	}
	
	public function pageBuilderExport()
	{
		$data = $this->_pageBuilderExport();
		header('Content-Type: application/json');
		print json_encode($data, JSON_PRETTY_PRINT);
		die();
	}
}