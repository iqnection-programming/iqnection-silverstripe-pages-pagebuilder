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
	private static $singular_name = 'Panel';
	private static $plural_name = 'Panels';
	
	private static $type_title = 'Unknown Section';
	private static $background_image_class = 'bg-image';
	
	private static $db = [
		'SortOrder' => 'Int',
		'BackgroundColor' => 'Varchar(20)',
		'AdditionalCssClasses' => 'Varchar(255)',
		'Borders' => 'Text',
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
	
	/**
	 * Set values in site yml config
	 * expects array of key: value pairs
	 * where key is the title to display in the dropdown
	 * and value is the css class declared in your stylesheet
	 */
	private static $background_colors = [];
	
	/**
	 * Set values in site yml config
	 * expects array of key: value pairs
	 * where key is the title to display in the dropdown
	 * and value is the css color hex value
	 */
	private static $border_colors = [];
	
	private static $default_sort = 'SortOrder ASC';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName([
			'SortOrder',
			'PageID',
			'BackgroundImageLarge',
			'BackgroundImageMedium',
			'BackgroundImageSmall',
			'Borders',
			'EnableBackgroundColor'
		]);
		$fields->addFieldToTab('Root.Style', Forms\TextField::create('AdditionalCssClasses','Additional CSS Classes'));
		
		if ( ($background_colors = $this->Config()->get('background_colors')) && (count($background_colors)) )
		{
			$fields->addFieldToTab('Root.Style', 
				Forms\DropdownField::Create('BackgroundColor','Background Color')
					->setSource(array_flip($this->Config()->get('background_colors')))
					->setEmptyString('Default')
			);
		}
		$fields->addFieldsToTab('Root.Style', [
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageLarge',"Background Image")
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 2000px wide'),
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageMedium',"Background Image Tablet\n( =< 800px)")
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 1024px wide'),
			Injector::inst()->create(Forms\FileHandleField::class,'BackgroundImageSmall',"Background Image Mobile\n( =< 500px)")
				->setAllowedExtensions(['jpg','jpeg','png'])
				->setDescription('Recommended Size: 500px wide')
		]);
		
		if ( ($border_colors = $this->Config()->get('border_colors')) && (count($border_colors)) )
		{
			$fields->addFieldsToTab('Root.Style', [
				Forms\FieldGroup::create('Top Border',[
					Forms\DropdownField::create('_Borders[Top][Color]','Color')
						->setSource(array_flip($this->Config()->get('border_colors')))
						->setEmptyString('Default')
						->setValue(isset($selectedBorders['Top']['Color']) ? $selectedBorders['Top']['Color'] : null),
					Forms\NumericField::create('_Borders[Top][Size]','Size (px)')
						->setAttribute('placeholder','0')
						->setValue(isset($selectedBorders['Top']['Size']) ? $selectedBorders['Top']['Size'] : null)
				]),
				Forms\FieldGroup::create('Bottom Border',[
					Forms\DropdownField::create('_Borders[Bottom][Color]','Color')
						->setSource(array_flip($this->Config()->get('border_colors')))
						->setEmptyString('Default')
						->setValue(isset($selectedBorders['Bottom']['Color']) ? $selectedBorders['Bottom']['Color'] : null),
					Forms\NumericField::create('_Borders[Bottom][Size]','Size (px)')
						->setAttribute('placeholder','0')
						->setValue(isset($selectedBorders['Bottom']['Size']) ? $selectedBorders['Bottom']['Size'] : null)
				])
			]);
		}

		return $fields;
	}

	public function getAnchorsInContent()
	{
		return [];
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
		if (isset($_REQUEST['_Borders']))
		{
			$this->Borders = json_encode($_REQUEST['_Borders']);
		}
	}
	
	public function ElementHTMLID()
	{
		return 'pageBuilder-section-'.$this->ID;
	}
	
	protected function cleanCssClassName($cssClass)
	{
		return preg_replace('/[^a-z0-9\-_]/','-',strtolower($cssClass));
	}
	
	public function CSSClasses($asString = true)
	{
		$classes = explode(',',preg_replace('/\s/',',',$this->AdditionalCssClasses));
		$classes[] = 'page-builder-section';
		if ($this->BackgroundColor)
		{
			$classes[] = $this->cleanCssClassName($this->BackgroundColor);
		}
		$classes[] = strtolower(preg_replace('/^\-|\-$/','',preg_replace('/([A-Z])/','-$1',ClassInfo::shortName($this))));
		if ( ($this->BackgroundImageLarge()->Exists()) || ($this->BackgroundImageMedium()->Exists()) || ($this->BackgroundImageSmall()->Exists()) )
		{
			$classes[] = $this->Config()->get('background_image_class');
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
		if ($borders = json_decode($this->Borders,1))
		{
			foreach(['Top','Bottom'] as $position)
			{
				if ( (isset($borders[$position]['Size'])) && ($borders[$position]['Size']) )
				{
					$css['Large'][$selector][] = 'border-'.strtolower($position).':'.intval($borders[$position]['Size']).'px solid '.$borders[$position]['Color'];
				}
			}
		}
		$this->invokeWithExtensions('updateCustomCSS',$css);
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