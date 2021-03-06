<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use IQnection\PageBuilder\Section\ContentBuilderSection;
use SilverStripe\Assets\Shortcodes\FileLink;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Flushable;


class Block extends DataObject implements Flushable
{
	private static $table_name = 'ContentBuilderBlock';
	private static $singular_name = 'Block';
	private static $plural_name = 'Blocks';

	private static $anchor_regex = '/\\<[^\\>]*(?:name|id)[\\s=]*[\\"\']([^\\"\']*)[\\"\']/im';

	private static $type_title = 'Unknown Block Type';
	
	private static $db = [
		'SortOrder' => 'Int',
		'AdditionalCssClasses' => 'Varchar(255)',
		'Width' => 'Varchar(255)',
	];
	
	private static $has_one = [
		'ContentBuilderSection' => ContentBuilderSection::class,
	];
	
	private static $summary_fields = [
		'Title' => 'Type',
		'Description' => 'Description'
	];
	
	private static $default_sort = 'SortOrder ASC';
	
	/**
	 * Set values in site yml config
	 * expects array of key: value pairs
	 * where key is the title to display in the dropdown
	 * and value is the css class declared in your stylesheet
	 */
	private static $widths = [
		'Content Width' => 'page-width',
		'Full Width' => 'window-width'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['SortOrder','ContentBuilderSectionID']);
		$fields->addFieldToTab('Root.Style', Forms\TextField::create('AdditionalCssClasses','Additional CSS Classes') );
		
		if ( ($widths = $this->Config()->get('widths')) && (count($widths)) )
		{
			$fields->addFieldToTab('Root.Style', 
				Forms\DropdownField::Create('Width','Width')
					->setSource(array_flip($this->Config()->get('widths')))
					->setEmptyString('Default')
			);
		}
		return $fields;
	}

	public static function flush()
	{
		$cache = Injector::inst()->get(CacheInterface::class . '.iqPageBuilder');
		$cache->clear();
	}

	public function getAnchorsInContent()
	{
		return [];
	}
	
	public function getBetterButtonsActions()
	{
		$actions = parent::getBetterButtonsActions();
		$actions->removeByName(['action_doSaveAndAdd']);
		return $actions;
	}
	
	protected function cleanCssClassName($cssClass)
	{
		return preg_replace('/[^a-z0-9\-_]/','-',strtolower($cssClass));
	}
	
	public function SectionCSSClasses($asString = true)
	{
		$classes = explode(',',preg_replace('/\s/',',',$this->AdditionalCssClasses));
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this)))).'-container';
		if ($this->Width)
		{
			$classes[] = $this->cleanCssClassName($this->Width);
		}
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
		$customCSS = [
			'Large' => [],
			'Medium' => [],
			'Small' => []
		];
		$this->invokeWithExtensions('updateCustomCSS', $customCSS);
		return $customCSS;
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
		$cache = Injector::inst()->get(CacheInterface::class . '.iqPageBuilder');
		$cache->delete($this->getCacheName());
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

	public function getCacheName()
	{
		$cacheKey = md5(serialize([
			'PageBuilder',
			$this->getClassName(),
			$this->ID,
			$this->LastEdited
		]));

		return $cacheKey;
	}

	public function Render()
	{
		$cache = Injector::inst()->get(CacheInterface::class . '.iqPageBuilder');
		$cacheKey = $this->getCacheName();
		if ($rendered = $cache->get($cacheKey))
		{
			return $rendered;
		}

		$templates = [];
		foreach(array_reverse(ClassInfo::ancestry($this)) as $ancestor)
		{
			$templates[] = preg_replace('/\\\\/','/',$ancestor);
			if ($ancestor == Block::class)
			{
				break;
			}
		}
		$rendered = $this->renderWith($templates);
		$cache->set($cacheKey, $rendered);
		return $rendered;
	}
}