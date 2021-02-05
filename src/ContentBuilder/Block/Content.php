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
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('Content','Content')->addExtraClass('stacked') );
		return $fields;
	}
	
	public function getDescription()
	{
		return $this->dbObject('Content')->setProcessShortcodes(false)->Summary();
	}

	public function getAnchorsInContent()
	{
		$anchors = parent::getAnchorsInContent();

		$parseSuccess = preg_match_all(
            $this->Config()->get('anchor_regex'),
            $this->Content,
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
}