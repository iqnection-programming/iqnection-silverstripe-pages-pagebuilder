<?php

namespace IQnection\PageBuilder;

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use Symbiote\GridFieldExtensions;
use SilverStripe\Core\ClassInfo;
use IQnection\PageBuilder\Section\PageBuilderSection;

class PageBuilder extends \Page
{
	private static $table_name = 'PageBuilder';
	private static $icon_class = "font-icon-p-alt-2";
	
	private static $has_many = [
		'PageBuilderSections' => PageBuilderSection::class
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName([
			'Content',
			'MinisiteLayout',
			'Sidebar',
			'Blog Feed'
		]);
		$fields->addFieldToTab('Root.Main', Forms\GridField\GridField::create(
			'PageBuilderSections',
			'Sections',
			$this->PageBuilderSections(),
			Forms\GridField\GridFieldConfig_RecordEditor::create(100)
				->addComponent(new GridFieldSortableRows('SortOrder'))
				->addComponent($GridFieldAddNewMultiClass = new GridFieldExtensions\GridFieldAddNewMultiClass())
				->removeComponentsByType(Forms\GridField\GridFieldAddNewButton::class)
			)
		);
		$sectionTypes = [];
		foreach(ClassInfo::subclassesFor(PageBuilderSection::class) as $subClass)
		{
			if ($subClass != PageBuilderSection::class)
			{
				$sectionTypes[$subClass] = $subClass::Config()->get('type_title');
			}
		}
		$GridFieldAddNewMultiClass->setTitle('Add Section')
			->setClasses($sectionTypes);
		
		return $fields;
	}
}