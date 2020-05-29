<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;

class ContentContentContent extends ContentContent
{
	private static $table_name = 'ContentBuilderContentContentContent';
	private static $type_title = 'Three Column Content';
	private static $db = [
		'CenterContent' => 'HTMLText',
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->insertAfter('LeftContent', Forms\HTMLEditor\HTMLEditorField::create('CenterContent','Center Content')->addExtraClass('stacked') );
		return $fields;
	}
	
	public function getDescription()
	{
		return "Left: ".$this->dbObject('LeftContent')->Summary()
			."\nCenter: ".$this->dbObject('CenterContent')->Summary()
			."\nRight: ".$this->dbObject('RightContent')->Summary();
	}
}