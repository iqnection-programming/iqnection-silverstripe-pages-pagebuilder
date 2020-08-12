<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType;

class ContentContent extends Block
{
	private static $table_name = 'ContentBuilderContentContent';
	private static $type_title = 'Two Column Content';
	private static $db = [
		'LeftContent' => 'HTMLText',
		'RightContent' => 'HTMLText',
		'Split' => "Varchar(20)",
	];
	
	private static $defaults = [
		'Split' => '50/50'
	];
	
	private static $split_options = [
		'30/70',
		'40/60',
		'50/50',
		'60/40',
		'70/30'
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('LeftContent','Left Content')->addExtraClass('stacked') );
		$fields->addFieldToTab('Root.Main', Forms\HTMLEditor\HTMLEditorField::create('RightContent','Right Content')->addExtraClass('stacked') );
		foreach($this->Config()->get('split_options', Config::UNINHERITED) as $splitKey => $splitValue)
		{
			$splitColumns = [];
			foreach(explode('/',$splitValue) as $columnWidth)
			{
				$splitColumns[] = '<div style="width:'.$columnWidth.'%">'.$columnWidth.'</div>';
			}
			$columnSplits[$splitValue] = FieldType\DBField::create_field(FieldType\DBHTMLVarchar::class, '<div class="content-block-column-split-preview">'.implode('',$splitColumns).'</div>');
		}
		$fields->addFieldToTab('Root.Style', Forms\OptionsetField::create('Split','Content Split')
			->addExtraClass('horizontal')
			->setSource($columnSplits));
		return $fields;
	}
	
	public function getDescription()
	{
		return "Left: ".$this->dbObject('LeftContent')->Summary()."\nRight: ".$this->dbObject('RightContent')->Summary();
	}
	
	public function updateCSSClasses(&$classes = [])
	{
		$classes[] = 'split-'.preg_replace('/\//','-',$this->Split);
		return $classes;
	}
}