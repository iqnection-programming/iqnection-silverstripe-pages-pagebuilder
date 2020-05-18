<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;

class ContentContent extends Block
{
	private static $table_name = 'ContentBuilderContentContent';
	private static $type_title = 'Two Column Content';
	private static $db = [
		'LeftContent' => 'HTMLText',
		'RightContent' => 'HTMLText',
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('LeftContent','Left Content')->addExtraClass('stacked') );
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('RightContent','Right Content')->addExtraClass('stacked') );
		return $fields;
	}
	
	public function getDescription()
	{
		return "Left: ".$this->dbObject('LeftContent')->Summary()."\nRight: ".$this->dbObject('RightContent')->Summary();
	}
}