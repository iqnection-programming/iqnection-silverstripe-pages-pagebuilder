<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use IQnection\PageBuilder\Section\ContentBuilderSection;

class Block extends DataObject
{
	private static $table_name = 'ContentBuilderBlock';
	private static $singular_name = 'Block';
	private static $plural_name = 'Blocks';
	
	private static $type_title = 'Unknown Block Type';
	
	private static $db = [
		'SortOrder' => 'Int',
	];
	
	private static $has_one = [
		'ContentBuilderSection' => ContentBuilderSection::class,
	];
	
	private static $summary_fields = [
		'CMSPreview' => 'View'
	];
	
	private static $default_sort = 'SortOrder ASC';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['SortOrder','ContentBuilderSectionID']);
		
		if ($this->Exists())
		{
			$fields->addFieldToTab('Root.Preview', Forms\LiteralField::create('_preview', $this->CMSPreview()));
		}
		return $fields;
	}
	
	public function SectionCSSClasses($asString = true)
	{
		$classes = [];
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		$this->invokeWithExtensions('updateSectionCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function CSSClasses($asString = true)
	{
		$classes = ['content-builder-block'];
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		$this->invokeWithExtensions('updateCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function getTitle()
	{
		return static::Config()->get('type_title');
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