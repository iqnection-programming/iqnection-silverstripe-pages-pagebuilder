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
	
	private static $defaults = [
		'Split' => '33/33/33'
	];
	
	private static $split_options = [
		'25/25/50',
		'25/50/25',
		'50/25/25',
		'20/60/20',
		'33/33/33'
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->insertAfter('LeftContent', Forms\HTMLEditor\HTMLEditorField::create('CenterContent','Center Content')->addExtraClass('stacked') );
		return $fields;
	}

	public function getAnchorsInContent()
	{
		$anchors = parent::getAnchorsInContent();

		$parseSuccess = preg_match_all(
            $this->Config()->get('anchor_regex'),
            $this->CenterContent,
            $matches
        );

        if (!$parseSuccess) {
            return [];
        }

        $blockanchors = array_values(array_unique(array_filter(
            $matches[1]
        )));

		return array_merge($blockanchors, $anchors);
	}

	public function getDescription()
	{
		return "Left: ".$this->dbObject('LeftContent')->setProcessShortcodes(false)->Summary()
			."\nCenter: ".$this->dbObject('CenterContent')->setProcessShortcodes(false)->Summary()
			."\nRight: ".$this->dbObject('RightContent')->setProcessShortcodes(false)->Summary();
	}
}