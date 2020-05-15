<?php

namespace IQnection\PageBuilder\Section;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Assets\Image;
use SilverStripe\View\SSViewer;

class PageBuilderSection extends DataObject
{
	private static $table_name = 'PageBuilderSection';
	private static $singular_name = 'Page Section';
	private static $plural_name = 'Page Sections';
	
	private static $type_title = 'Unknown Section';
	
	private static $db = [
		'SortOrder' => 'Int',
		'BackgroundColor' => "Enum('White,Blue,Grey','White')",
	];
	
	private static $has_one = [
		'Page' => SiteTree::class,
		'BackgroundImage' => Image::class,
	];
	
	private static $summary_fields = [
		'CMSPreview' => 'View'
	];
	
	private static $defaults = [
		'BackgroundColor' => 'White',
	];
	
	private static $owns = [
		'BackgroundImage'
	];
	
	private static $default_sort = 'SortOrder ASC';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['SortOrder','PageID']);
		
		$fields->addFieldToTab('Root.Style', Forms\DropdownField::Create('BackgroundColor','Background Color')
			->setSource($this->dbObject('BackgroundColor')->enumValues()));
		$fields->addFieldToTab('Root.Style', Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImage','Background Image')
			->setAllowedExtensions(['jpg','jpeg','png'])
			->setDescription('Recommended Size: 2000px by 1000px')
		);
		
		if ($this->Exists())
		{
			$fields->addFieldToTab('Root.Preview', Forms\LiteralField::create('_preview', $this->CMSPreview()));
		}
		return $fields;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ( ($this->BackgroundImage()->Exists()) && (!$this->BackgroundImage()->isPublished()) )
		{
			$this->BackgroundImage()->publishSingle();
		}
	}
	
	public function CSSClasses($asString = true)
	{
		$classes = ['page-builder-section'];
		$classes[] = 'bg-'.strtolower($this->BackgroundColor);
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		if ($this->BackgroundImage()->Exists())
		{
			$classes[] = 'bg-image';
		}
		$this->invokeWithExtensions('updateCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function getTitle()
	{
		return static::Config()->get('type_title');
	}
	
	public function CMSPreview()
	{
		return $this->renderWith(['IQnection/PageBuilder/PageBuilderSection_CMSPreview']);
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
			if ($ancestor == 'PageBuilderSection')
			{
				break;
			}
		}
		return $this->renderWith($templates);
	}
}