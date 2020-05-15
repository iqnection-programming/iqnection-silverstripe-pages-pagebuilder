<?php

namespace IQnection\PageBuilder\Section;

use SilverStripe\Forms;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\FieldType;

class GetHelpFormSection extends PageBuilderSection
{
	private static $table_name = 'GetHelpFormSection';
	private static $type_title = 'Get Help Form';
	
	private static $db = [
		'Headline' => 'Varchar(255)',
		'Content' => 'HTMLText'
	];
	
	private static $has_one = [
		'Image' => Image::class,
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		if ($contactHelpPage = $this->HelpContactFormPage())
		{
			$fields->addFieldToTab('Root.Form', Forms\LiteralField::create('form-note','<p>The contact for is managed on "'.$contactHelpPage->Title.'". <a href="'.$contactHelpPage->CMSEditLink().'" target="_blank">Click here</a> to view/edit form controls and submissions</p>'));
		}
		return $fields;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if ( ($this->Image()->Exists()) && (!$this->Image()->isPublished()) )
		{
			$this->Image()->publishSingle();
		}
	}
	
	protected $_helpContactFormPage;
	public function HelpContactFormPage()
	{
		if (is_null($this->_helpContactFormPage))
		{
			$this->_helpContactFormPage = \ContactHelpFormPage::get()->First();
		}
		return $this->_helpContactFormPage;
	}
	
	protected $_helpContactFormPageController;
	public function HelpContactFormPageController()
	{
		if (is_null($this->_helpContactFormPageController))
		{
			$this->_helpContactFormPageController = false;
			if ($page = $this->HelpContactFormPage())
			{
				$this->_helpContactFormPageController = Injector::inst()->create($page->getControllerName(), $page);
				$this->_helpContactFormPageController->setRequest(Controller::curr()->getRequest());
			}
		}
		return $this->_helpContactFormPageController;
	}
	
	public function HelpContactForm()
	{
		if ($controller = $this->HelpContactFormPageController())
		{
			if (Controller::curr() instanceof \SilverStripe\CMS\Controllers\CMSMain)
			{
				return FieldType\DBField::create_field(FieldType\DBHTMLText::class,'<div style="padding:30% 0;text-align:center;">FORM GOES HERE</div>');
			}
			$form = $controller->RenderForm();
			$form->setHTMLID('help-contact-form');
			return $form;
		}
	}
}