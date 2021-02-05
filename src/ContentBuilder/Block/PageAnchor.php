<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;
use SilverStripe\View\Requirements;

class PageAnchor extends Block
{
	private static $table_name = 'PageAnchor';
	private static $type_title = 'Page Anchor';
	private static $db = [
		'Name' => 'Varchar(50)',
	];

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName([
			'AdditionalCssClasses',
			'Width',
			'Style'
		]);
		$fields->dataFieldByName('Name')->setDescription('For internal purposes. Will be converted into anchor name');
		return $fields;
	}

	public function getAnchorsInContent()
	{
		$anchors = parent::getAnchorsInContent();

		$anchors[] = $this->AnchorName();

		return $anchors;
	}

	public function AnchorName()
	{
		return trim(preg_replace('/[^a-zA-Z0-9\-_]/','-',$this->Name), ' -_');
	}

	public function getDescription()
	{
		return 'Anchor: '.$this->Name;
	}
}










