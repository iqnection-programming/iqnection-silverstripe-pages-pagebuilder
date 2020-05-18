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
	
	private static $owns = [
		'PageBuilderSections'
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
		$fields->addFieldToTab('Root.Main', $PageBuilderSections_gf = Forms\GridField\GridField::create(
			'PageBuilderSections',
			'Sections',
			$this->PageBuilderSections(),
			$PageBuilderSections_config = Forms\GridField\GridFieldConfig_RecordEditor::create(100)
				->addComponent(new GridFieldSortableRows('SortOrder'))
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
		if (count($sectionTypes) > 1)
		{
			$PageBuilderSections_config->addComponent($GridFieldAddNewMultiClass = new GridFieldExtensions\GridFieldAddNewMultiClass());
			$PageBuilderSections_config->removeComponentsByType(Forms\GridField\GridFieldAddNewButton::class);
			$GridFieldAddNewMultiClass->setTitle('Add Section')
				->setClasses($sectionTypes);
		}
		else
		{
			$PageBuilderSections_gf->setModelClass(key($sectionTypes));
		}
		
		$fields->addFieldToTab('Root.Developer.PageBuilder', Forms\LiteralField::create('_exportPageBuilder','<a href="'.$this->Link('_pageBuilderExport').'" target="_blank">Export Page Builder Data</a>'));
		
		return $fields;
	}
	
	public function _pageBuilderExport()
	{
		$data = [];
		foreach($this->PageBuilderSections() as $section)
		{
			$sectionData = $section->export_forPageBuilder();
			$data[] = $sectionData;
		}
		return $data;
	}
	
	/**
	 * Collects custom CSS from each section
	 * Expects each section to provide format:
	 * [
	 *      Large => [
	 *          {element selector} => [
	 *              'style: value',
	 *              'another-style: value'
	 *          ]
	 *      ],
	 *      Medium => [
	 *          {element selector} => [
	 *              'style: value',
	 *              'another-style: value'
	 *          ]
	 *      ],
	 *      Small => [
	 *          {element selector} => [
	 *              'style: value',
	 *              'another-style: value'
	 *          ]
	 *      ]
	 * ]
	 *
	 * @returns array
	 */
	public function getCustomCSS()
	{
		$customCss = [
			'Large' => [],
			'Medium' => [],
			'Small' => []
		];
		foreach($this->PageBuilderSections() as $section)
		{
			$sectionCustomCss = $section->getCustomCss();
			$customCss['Large'] = array_merge($customCss['Large'], $sectionCustomCss['Large']);
			$customCss['Medium'] = array_merge($customCss['Medium'], $sectionCustomCss['Medium']);
			$customCss['Small'] = array_merge($customCss['Small'], $sectionCustomCss['Small']);
		}
		return $customCss;
	}
}













