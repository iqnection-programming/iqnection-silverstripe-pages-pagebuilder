<?php


namespace IQnection\PageBuilder\Section;

use SilverStripe\Forms;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use Symbiote\GridFieldExtensions;
use SilverStripe\Core\ClassInfo;
use IQnection\PageBuilder\ContentBuilder\Block;
use IQnection\PageBuilder\Section\PageBuilderSection;

class ContentBuilderSection extends PageBuilderSection
{
	private static $table_name = 'ContentBuilderSection';
	private static $type_title = 'Content Builder';
	
	private static $has_many = [
		'ContentBuilderBlocks' => Block\Block::class,
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['ContentBuilderBlocks']);
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
		return $fields;
	}
}