<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;

class Content extends Block
{
	private static $table_name = 'ContentBuilderContent';
	private static $type_title = 'Full Width Content';
	private static $db = [
		'Content' => 'HTMLText',
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('Content','Content') );
		return $fields;
	}
}