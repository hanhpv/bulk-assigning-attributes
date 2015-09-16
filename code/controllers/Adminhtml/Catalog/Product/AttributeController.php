<?php

require_once 'Mage/Adminhtml/controllers/Catalog/Product/AttributeController.php';

class Dtn_Eav_Adminhtml_Catalog_Product_AttributeController extends Mage_Adminhtml_Catalog_Product_AttributeController
{
    public function attributeSetsGridAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->load($id);
        Mage::register('entity_attribute', $model);

        $this->loadLayout('');
        $this->getLayout()->getBlock('attribute.edit.tab.sets')
            ->setEntityTypeId($this->_entityTypeId)
            ->setSelectedAttributeSets($this->getRequest()->getPost('in_sets'));
        $this->renderLayout();
    }
}
