<?php
class Ameex_EasyMultiwebsite_Block_Adminhtml_EasyMultiwebsite extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	public function __construct()
		{
			$this->addColumn('foldername', array(
				'label' => Mage::helper('easymultiwebsite')->__('Folder Name'),
				'style' => 'width:120px',
			));
			$this->addColumn('websitename', array(
				'label' => Mage::helper('easymultiwebsite')->__('Website Name'),
				'style' => 'width:120px',	
			));
            $this->addColumn('storename', array(
				'label' => Mage::helper('easymultiwebsite')->__('Store Name'),
				'style' => 'width:120px',
			));
            $this->addColumn('storeviewname', array(
				'label' => Mage::helper('easymultiwebsite')->__('Storeview Name'),
				'style' => 'width:120px',
			));
			$this->_addAfter = false;
			$this->_addButtonLabel = Mage::helper('easymultiwebsite')->__('Add New');
			parent::__construct();
			$this->setTemplate('easymultiwebsite/array_delete.phtml');
		}
}
