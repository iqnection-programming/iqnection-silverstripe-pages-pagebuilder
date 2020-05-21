<?php


namespace IQnection\PageBuilder\Section;

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use Symbiote\GridFieldExtensions;
use SilverStripe\Core\ClassInfo;
use IQnection\PageBuilder\ContentBuilder\Block;
use IQnection\PageBuilder\Section\PageBuilderSection;
use SilverStripe\ORM\FieldType;

class ContentBuilderSection extends PageBuilderSection
{
	private static $table_name = 'ContentBuilderSection';
	private static $type_title = 'Content Builder';
	
	private static $has_many = [
		'ContentBuilderBlocks' => Block\Block::class,
	];
	
	private static $owns = [
		'ContentBuilderBlocks'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['ContentBuilderBlocks']);
		if ($this->Exists())
		{
			if (!class_exists('GridFieldExtensions\\GridFieldAddNewMultiClass'))
			{
				$fields->addFieldToTab('Root.Main', HeaderField::create('_error','This module requires the class GridFieldExtensions\\GridFieldAddNewMultiClass') );
				return $fields;
			}
			$fields->addFieldToTab('Root.Main', Forms\GridField\GridField::create(
				'ContentBuilderBlocks',
				'Blocks',
				$this->ContentBuilderBlocks(),
				Forms\GridField\GridFieldConfig_RecordEditor::create(100)
					->addComponent(new GridFieldSortableRows('SortOrder'))
					->addComponent($GridFieldAddNewMultiClass = new GridFieldExtensions\GridFieldAddNewMultiClass())
					->removeComponentsByType(Forms\GridField\GridFieldAddNewButton::class)
			));
			$sectionTypes = [];
			foreach(ClassInfo::subclassesFor(Block\Block::class) as $subClass)
			{
				if ($subClass != Block\Block::class)
				{
					$sectionTypes[$subClass] = $subClass::Config()->get('type_title');
				}
			}
			$GridFieldAddNewMultiClass->setTitle('Add Block')
				->setClasses($sectionTypes);
		}
		else
		{
			$fields->addFieldToTab('Root.Main', Forms\HeaderField::create('_note','You must save this section before adding blocks') );
		}
		return $fields;
	}
	
	public function getDescription()
	{
		$html = '<strong>Content Blocks:</strong><ul><li>';
		$children = [];
		foreach($this->ContentBuilderBlocks() as $child)
		{
			$children[] = $child->getDescription();
		}
		$html .= implode('</li><li>',$children).'</li></ul>';
		return FieldType\DBField::create_field(FieldType\DBHTMLText::class,$html);
	}
	
	public function updateExport_forPageBuilder(&$data)
	{
		$data['ContentBuilderBlocks'] = [];
		foreach($this->ContentBuilderBlocks() as $component)
		{
			$data['ContentBuilderBlocks'][] = $component->export_forPageBuilder();
		}
	}
	
	public function getCustomCSS()
	{
		$customCss = parent::getCustomCSS();
		foreach($this->ContentBuilderBlocks() as $block)
		{
			$blockCss = $block->getCustomCSS();
			$customCss['Large'] = array_merge($customCss['Large'], $blockCss['Large']);
			$customCss['Medium'] = array_merge($customCss['Medium'], $blockCss['Medium']);
			$customCss['Small'] = array_merge($customCss['Small'], $blockCss['Small']);
		}
		return $customCss;
	}
}