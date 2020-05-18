<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use IQnection\PageBuilder\Section\ContentBuilderSection;
use SilverStripe\Assets\Shortcodes\FileLink;

class Block extends DataObject
{
	private static $table_name = 'ContentBuilderBlock';
	private static $singular_name = 'Block';
	private static $plural_name = 'Blocks';
	
	private static $type_title = 'Unknown Block Type';
	
	private static $db = [
		'SortOrder' => 'Int',
		'AdditionalCssClasses' => 'Varchar(255)',
	];
	
	private static $has_one = [
		'ContentBuilderSection' => ContentBuilderSection::class,
	];
	
	private static $summary_fields = [
		'Title' => 'Type',
		'Description' => 'Description'
	];
	
	private static $default_sort = 'SortOrder ASC';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['SortOrder','ContentBuilderSectionID']);
		$fields->addFieldToTab('Root.Style', Forms\TextField::create('AdditionalCssClasses','Additional CSS Classes') );
		
		if ($this->Exists())
		{
			$fields->addFieldToTab('Root.Preview', Forms\LiteralField::create('_preview', $this->CMSPreview()));
		}
		return $fields;
	}
	
	protected function cleanCssClassName($cssClass)
	{
		return preg_replace('/[^a-z0-9\-_]/','-',strtolower($cssClass));
	}
	
	public function SectionCSSClasses($asString = true)
	{
		$classes = explode(',',preg_replace('/\s/',',',$this->AdditionalCssClasses));
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this)))).'-container';
		$this->invokeWithExtensions('updateSectionCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function ElementHTMLID()
	{
		return 'contentBuilder-block-'.$this->ID;
	}
	
	public function CSSClasses($asString = true)
	{
		$classes = [];
		$classes[] = 'content-builder-block-element';
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		$this->invokeWithExtensions('updateCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function getCustomCSS()
	{
		return [
			'Large' => [],
			'Medium' => [],
			'Small' => []
		];
	}
	
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		foreach(FileLink::get()->Filter(['ParentID' => $this->ID, 'ParentClass' => $this->getClassName()]) as $fileLink)
		{
			if (!$fileLink->Linked()->isPublished())
			{
				$fileLink->Linked()->publishSingle();
			}
		}
	}
	
	public function getTitle()
	{
		return static::Config()->get('type_title');
	}
	
	public function getDescription()
	{
		return $this->getTitle();
	}
	
	public function CMSPreview()
	{
		return $this->Render();
	}
		
	public function forTemplate()
	{
		return $this->Render();
	}
	
	public function Render()
	{
		$templates = [];
		foreach(array_reverse(ClassInfo::ancestry($this)) as $ancestor)
		{
			$templates[] = preg_replace('/\\\\/','/',$ancestor);
			if ($ancestor == Block::class)
			{
				break;
			}
		}
		return $this->renderWith($templates);
	}
}