<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;

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
		300,
		400,
		500,
		600,
		700,
		800
	];
	
	private static $styles = [
		'Normal',
		'Italic'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
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
				->setSource(array_combine($this->Config()->get('weights'),$this->Config()->get('weights')))
				->setEmptyString('Default'),
			Forms\DropdownField::Create('Style','Style')
				->setSource(array_combine($this->Config()->get('styles'),$this->Config()->get('styles')))
				->setEmptyString('Default')
			]);
		return $fields;
	}
	
	public function updateCSSClasses(&$cssClasses)
	{
		if ($this->Color)
		{
			$cssClasses[] = 'text-'.strtolower($this->Color);
		}
		if ($this->Alignment)
		{
			$cssClasses[] = 'text-'.strtolower($this->Alignment);
		}
		if ($this->TextTransform)
		{
			$cssClasses[] = 'text-'.strtolower($this->TextTransform);
		}
		if ($this->Weight)
		{
			$cssClasses[] = 'text-'.strtolower($this->Weight);
		}
		if ($this->Style)
		{
			$cssClasses[] = 'text-'.strtolower($this->Style);
		}
	}
}










