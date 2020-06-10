<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;
use SilverStripe\View\Requirements;

class HorizontalBar extends Block
{
	private static $table_name = 'ContentBuilderBar';
	private static $type_title = 'Horizontal Bar';
	private static $db = [
		'Color' => 'Varchar(20)',
		'Height' => 'Varchar(10)',
	];
	
	private static $defaults = [
		'Height' => '1px',
	];
	
	/**
	 * Set values in site yml config
	 * expects array of key: value pairs
	 * where key is the title to display in the dropdown
	 * and value is the css class declared in your stylesheet
	 */
	private static $colors = [];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		if (count($this->Config()->get('colors')))
		{
			$fields->addFieldToTab('Root.Main', 
				Forms\DropdownField::Create('Color','Color')
					->setSource(array_flip($this->Config()->get('colors')))
					->setEmptyString('Default')
			);
		}
		else
		{
			$fields->addFieldToTab('Root.Main', Forms\TextField::create('Color','Color')
				->setInputType('color'));
		}
		$fields->addFieldToTab('Root.Main', Forms\TextField::create('Height','Height')
			->setDescription('(eg. 24px, 4vw, 1em)')
		);
		return $fields;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ( ($this->Height) && (!preg_match('/\d+[a-zA-Z]{2,3}/',$this->Height)) )
		{
			$this->Height .= 'px';
		}
	}
	
	public function updateCSSClasses(&$cssClasses)
	{
		if ( ($this->Height) && ($this->Color) && (in_array($this->Color, $this->Config()->get('colors'))) )
		{
			$cssClasses[] = $this->cleanCssClassName($this->Color);
		}
	}
	
	public function updateCustomCSS(&$customCss)
	{
		if ($this->Height)
		{
			$customCss['Large']['#'.$this->ElementHTMLID().' > .horizontal-bar'][] = 'height:'.$this->Height;
			if ( ($this->Color) && (!in_array($this->Color, $this->Config()->get('colors'))) )
			{
				$customCss['Large']['#'.$this->ElementHTMLID().' > .horizontal-bar'][] = 'background-color:'.$this->Color;
			}
		}
	}
	
	public function getDescription()
	{
		return 'Horizontal Bar';
	}
}










