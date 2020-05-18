<?php

namespace IQnection\PageBuilder\Extension;

use SilverStripe\ORM\DataExtension;

class DataObjectExtension extends DataExtension
{
	public function export_forPageBuilder($callingComponent = null)
	{
		$data = $this->owner->toMap();
		$this->owner->invokeWithExtensions('updateExport_forPageBuilder', $data);
		return $data;
	}
	
	public function updateExport_forPageBuilder(&$data)
	{
		
	}
}