<?php
/**
* Override Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs
*/
class Dtn_Eav_Block_Adminhtml_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs
{
	
	protected function _beforeToHtml()
    {
        $this->addTab('attribute_sets', array(
            'label'     => Mage::helper('catalog')->__('Attribute Sets'),
            'title'     => Mage::helper('catalog')->__('Attribute Sets'),
            'class'     => 'ajax',
			'url'       => $this->getUrl('*/*/attributeSets', array('_current' => true)),
            'after'		=> 'labels'
        ));

        return parent::_beforeToHtml();
    }
}