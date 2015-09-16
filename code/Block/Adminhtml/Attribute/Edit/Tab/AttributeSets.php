<?php

class Dtn_Eav_Block_Adminhtml_Attribute_Edit_Tab_AttributeSets extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeSetsGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('attribute_set_id');
        $this->setDefaultDir('DESC');
        if ($this->_getCurrentAttribute()->getId()) {
            $this->setDefaultFilter(array('in_sets' => 1));
        }
        $this->setEmptyText('No Items Found');
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getCurrentAttribute()
    {
        return Mage::registry('entity_attribute');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->addFieldToFilter('main_table.entity_type_id', $this->getEntityTypeId());

        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_sets', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_sets',
            'values' => $this->_getSelectedAttributeSets(),
            'align' => 'center',
            'index' => 'attribute_set_id',
            'filter_condition_callback' => array($this, '_filterInSetsCondition')
        ));

        $this->addColumn('attribute_set_name', array(
            'header' => Mage::helper('catalog')->__('Set Name'),
            'index' => 'attribute_set_name'
        ));

        return parent::_prepareColumns();
    }

    protected function _getSelectedAttributeSets()
    {
        $selected = $this->getSelectedAttributeSets();
        if (!is_array($selected)) {
            $selected = $this->getAttributeInSets();
        }
        return $selected;
    }

    public function getAttributeInSets()
    {
        $attribute = $this->_getCurrentAttribute();
        $resource = Mage::getSingleton('core/resource');

        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->addFieldToFilter('main_table.entity_type_id', $this->getEntityTypeId());
        $collection->getSelect()
            ->join(array('entity_attribute' => $resource->getTableName('eav/entity_attribute')), 'main_table.attribute_set_id = entity_attribute.attribute_set_id', array())
            ->where('entity_attribute.attribute_id = ?', $attribute->getId());

        $attributeSets = array();
        foreach ($collection as $item) {
            $attributeSets[] = $item->getId();
        }

        return $attributeSets;
    }

    /**
     * @param Varien_Data_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     */
    protected function _filterInSetsCondition($collection, $column)
    {
        $selected = $this->_getSelectedAttributeSets();
        if (empty($selected))
            $selected = '0';
        $value = $column->getFilter()->getValue();
        if ($value) {
            $collection->addFieldToFilter('main_table.attribute_set_id', array('in' => $selected));
        } else {
            $collection->addFieldToFilter('main_table.attribute_set_id', array('nin' => $selected));
        }
    }
}