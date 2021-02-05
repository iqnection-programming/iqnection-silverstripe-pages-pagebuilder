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
		'Color' => 'Varchar(20)',
		'Alignment' => 'Varchar(20)',
		'Transform' => 'Varchar(20)',
		'Weight' => 'Varchar(20)',
		'Style' => 'Varchar(20)',
		'Size' => 'Text',
		'Anchor' => 'Varchar(50)'
	];
	
	private static $defaults = [
		'Type' => 'H2',
	];
	
	/**
	 * Set values in site yml config
	 * expects array of key: value pairs
	 * where key is the title to display in the dropdown
	 * and value is the css class declared in your stylesheet
	 */
	private static $colors = [];
	
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
		400 => 'Regular', // 400
		700 => 'Bold', // 700
	];
	
	private static $styles = [
		'Normal',
		'Italic'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName([
			'Size',
			'EnableColor'
		]);
		$fontSizes = $this->getFontSizes();
		if (count($this->Config()->get('colors')))
		{
			$fields->addFieldToTab('Root.Style', 
				Forms\DropdownField::Create('Color','Color')
					->setSource(array_flip($this->Config()->get('colors')))
					->setEmptyString('Default')
			);
		}
		$weights = $this->Config()->get('weights');
		ksort($weights);
		
		$fields->addFieldsToTab('Root.Style', [
			Forms\DropdownField::Create('Alignment','Alignment')
				->setSource(array_combine($this->Config()->get('alignments'),$this->Config()->get('alignments')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Transform','Transform')
				->setSource(array_combine($this->Config()->get('transforms'),$this->Config()->get('transforms')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Weight','Weight')
				->setSource(array_combine($weights,$weights))
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

	public function getAnchorsInContent()
	{
		$anchors = parent::getAnchorsInContent();

		if ($this->Anchor)
		{
			$anchors[] = $this->Anchor;
		}

		return $anchors;
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
			$cssClasses[] = $this->cleanCssClassName($this->Color);
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
	
	public function updateCustomCSS(&$customCss)
	{
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
	}
	
	public function getDescription()
	{
		return 'Headline: '.strip_tags($this->Headline);
	}
}










