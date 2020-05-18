<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;
use SilverStripe\View\Requirements;

class Headline extends Block
{
	private static $table_name = 'ContentBuilderHeadline';
	private static $type_title = 'Headline';
	private static $db = [
		'Headline' => 'Varchar(255)',
		'Type' => "Enum('H1,H2,H3,H4,H5,H6','H2')",
		'Color' => 'Varchar(20)',//"Enum('Default,Blue,Light Blue,Grey,Black,White','Default')",
		'Alignment' => 'Varchar(20)',//"Enum('Default,Left,Center,Right','Default')",
		'Transform' => 'Varchar(20)',//"Enum('Default,Uppercase,Lowercase','Default')",
		'Weight' => 'Varchar(20)',//"Enum('Default,Lite,Regular,Medium,Semi-Bold,Bold,Extrabold','Default')",
		'Style' => 'Varchar(20)',//"Enum('Default,Normal,Italic','Default')",
		'Size' => 'Text',
	];
	
	private static $defaults = [
		'Type' => 'H2',
	];
	
	private static $colors = [
		'Blue',
		'Light Blue',
		'Grey',
		'Black',
		'White',
		'Orange'
	];
	
	private static $alignments = [
		'Left',
		'Center',
		'Right',
		'Justify'
	];
	
	private static $transforms = [
		'Uppercase',
		'Lowercase'
	];
	
	private static $weights = [
		'Lite', // 300
		'Regular', // 400
		'Medium', // 500
		'Semi-Bold', // 600
		'Bold', // 700
		'Extrabold' // 800
	];
	
	private static $styles = [
		'Normal',
		'Italic'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fontSizes = $this->getFontSizes();
		$fields->addFieldsToTab('Root.Style', [
			Forms\DropdownField::Create('Color','Color')
				->setSource(array_combine($this->Config()->get('colors'),$this->Config()->get('colors')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Alignment','Alignment')
				->setSource(array_combine($this->Config()->get('alignments'),$this->Config()->get('alignments')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Transform','Transform')
				->setSource(array_combine($this->Config()->get('transforms'),$this->Config()->get('transforms')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Weight','Weight')
				->setSource($this->Config()->get('weights'))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Style','Style')
				->setSource(array_combine($this->Config()->get('styles'),$this->Config()->get('styles')))
				->setEmptyString('Default'),
			Forms\FieldGroup::create('Font Size', [
				Forms\TextField::create('_Size[Large]','Desktop ( >= 801px)')->setValue($fontSizes['Large']),
				Forms\TextField::create('_Size[Medium]','Tablet (501px - 800px)')->setValue($fontSizes['Medium']),
				Forms\TextField::create('_Size[Small]','Mobile ( =< 500px)')->setValue($fontSizes['Small']),
			])->setDescription('(eg. 24px, 4vw, 1em)')
		]);
		return $fields;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if (isset($_REQUEST['_Size']))
		{
			$sizes = [
				'Large' => $_REQUEST['_Size']['Large'],
				'Medium' => $_REQUEST['_Size']['Medium'],
				'Small' => $_REQUEST['_Size']['Small']
			];
			$this->Size = json_encode($sizes);
		}
	}
	
	public function getFontSizes()
	{
		$setSizes = json_decode($this->Size,1);
		$sizes = [
			'Large' => isset($setSizes['Large']) ? $setSizes['Large'] : null,
			'Medium' => isset($setSizes['Medium']) ? $setSizes['Medium'] : null,
			'Small' => isset($setSizes['Small']) ? $setSizes['Small'] : null
		];
		return $sizes;
	}
	
	public function updateCSSClasses(&$cssClasses)
	{
		if ($this->Color)
		{
			$cssClasses[] = $this->cleanCssClassName('text-'.$this->Color);
		}
		if ($this->Alignment)
		{
			$cssClasses[] = $this->cleanCssClassName('text-'.$this->Alignment);
		}
		if ($this->Transform)
		{
			$cssClasses[] = $this->cleanCssClassName('text-'.$this->Transform);
		}
		if ($this->Weight)
		{
			$cssClasses[] = $this->cleanCssClassName('text-'.$this->Weight);
		}
		if ($this->Style)
		{
			$cssClasses[] = $this->cleanCssClassName('text-'.$this->Style);
		}
	}
	
	public function getCustomCSS()
	{
		$customCss = parent::getCustomCSS();
		$fontSizes = $this->getFontSizes();
		if ($fontSizes['Large'])
		{
			$customCss['Large']['#'.$this->ElementHTMLID().' > .headline'][] = 'font-size:'.$fontSizes['Large'];
		}
		if ($fontSizes['Medium'])
		{
			$customCss['Medium']['#'.$this->ElementHTMLID().' > .headline'][] = 'font-size:'.$fontSizes['Medium'];
		}
		if ($fontSizes['Small'])
		{
			$customCss['Small']['#'.$this->ElementHTMLID().' > .headline'][] = 'font-size:'.$fontSizes['Small'];
		}
		return $customCss;
	}
	
	public function getDescription()
	{
		return 'Headline: '.strip_tags($this->Headline);
	}
}










