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
		'BackgroundColor' => 'Varchar(20)',
		'AdditionalCssClasses' => 'Varchar(255)',
	];
	
	private static $has_one = [
		'Page' => SiteTree::class,
		'BackgroundImageLarge' => Image::class,
		'BackgroundImageMedium' => Image::class,
		'BackgroundImageSmall' => Image::class,
	];
	
	private static $summary_fields = [
		'Title' => 'Type',
		'Description' => 'Description',
		'BackgroundPreview' => 'Background'
	];
	
	private static $casting = [
		'Description' => 'HTMLFragment'
	];
	
	private static $owns = [
		'BackgroundImageLarge',
		'BackgroundImageMedium',
		'BackgroundImageSmall',
	];
	
	private static $background_colors = [
		'White',
		'Blue',
		'Grey'
	];
	
	private static $default_sort = 'SortOrder ASC';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName([
			'SortOrder',
			'PageID',
			'BackgroundImageLarge',
			'BackgroundImageMedium',
			'BackgroundImageSmall'
		]);
		
		$fields->addFieldsToTab('Root.Style', [
			Forms\DropdownField::Create('BackgroundColor','Background Color')
				->setSource(array_combine($this->Config()->get('background_colors'),$this->Config()->get('background_colors')))
				->setEmptyString('Default or Image'),
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageLarge','Background Image Desktop<br>( >= 801px)')
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 2000px wide'),
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageMedium','Background Image Tablet<br>(501px - 800px)')
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 1024px wide'),
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageSmall','Background Image Mobile<br>( =< 500px)')
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 500px wide'),
			Forms\TextField::create('AdditionalCssClasses','Additional CSS Classes') 
		]);
		
		if ($this->Exists())
		{
			$fields->addFieldToTab('Root.Preview', Forms\LiteralField::create('_preview', $this->CMSPreview()));
		}
		return $fields;
	}
	
	public function BackgroundPreview()
	{
		if ($this->BackgroundImageLarge()->Exists())
		{
			return $this->BackgroundImageLarge()->Fit(300,300);
		}
		return $this->BackgroundColor ? $this->BackgroundColor : '(none)';
	}
	
	public function getBetterButtonsActions()
	{
		$actions = parent::getBetterButtonsActions();
		if (!$this->Exists())
		{
			$actions->removeByName(['action_doSaveAndQuit','action_doSaveAndAdd']);
			$actions->fieldByName('action_save')->setTitle('Continue');
		}
		return $actions;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ( ($this->BackgroundImageLarge()->Exists()) && (!$this->BackgroundImageLarge()->isPublished()) )
		{
			$this->BackgroundImageLarge()->publishSingle();
		}
		if ( ($this->BackgroundImageMedium()->Exists()) && (!$this->BackgroundImageMedium()->isPublished()) )
		{
			$this->BackgroundImageMedium()->publishSingle();
		}
		if ( ($this->BackgroundImageSmall()->Exists()) && (!$this->BackgroundImageSmall()->isPublished()) )
		{
			$this->BackgroundImageSmall()->publishSingle();
		}
	}
	
	public function ElementHTMLID()
	{
		return 'pageBuilder-section-'.$this->ID;
	}
	
	public function CSSClasses($asString = true)
	{
		$classes = explode(',',preg_replace('/\s/',',',$this->AdditionalCssClasses));
		$classes[] = 'page-builder-section';
		$classes[] = 'bg-'.strtolower($this->BackgroundColor);
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		if ( ($this->BackgroundImageLarge()->Exists()) || ($this->BackgroundImageMedium()->Exists()) || ($this->BackgroundImageSmall()->Exists()) )
		{
			$classes[] = 'bg-image';
		}
		$this->invokeWithExtensions('updateCSSClasses',$classes);
		return ($asString) ? implode(' ',$classes) : $classes;
	}
	
	public function getCustomCSS()
	{
		$css = [
			'Large' => [],
			'Medium' => [],
			'Small' => []
		];
		$selector = '#'.$this->ElementHTMLID();
		if ($this->BackgroundImageLarge()->Exists())
		{
			$css['Large'][$selector][] = "background-image:url('".$this->BackgroundImageLarge()->getURL()."')";
		}
		if ($this->BackgroundImageMedium()->Exists())
		{
			$css['Medium'][$selector][] = "background-image:url('".$this->BackgroundImageMedium()->getURL()."')";
		}
		if ($this->BackgroundImageSmall()->Exists())
		{
			$css['Small'][$selector][] = "background-image:url('".$this->BackgroundImageSmall()->getURL()."')";
		}
		return $css;
	}
	
	public function getTitle()
	{
		return static::Config()->get('type_title');
	}
	
	public function getDescription()
	{
		return null;
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