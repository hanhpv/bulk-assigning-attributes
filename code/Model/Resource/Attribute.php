<?php

class Dtn_Eav_Model_Resource_Attribute extends Mage_Catalog_Model_Resource_Attribute
{
    public function saveInSetsIncluding(Mage_Core_Model_Abstract $object)
    {
        $attributeId = (int) $object->getId();
        if ($attributeId) {
            $newSets = explode('&', $object->getSets());
            $oldSets = array();
            $collection = $this->getAttributeInSets($object);
            foreach ($collection as $item) {
                $oldSets[] = $item->getId();
            }
            $insert = array_diff($newSets, $oldSets);
            $delete = array_diff($oldSets, $newSets);
            if (!empty($insert)) {
                foreach ($insert as $id) {
                    $set = Mage::getModel('eav/entity_attribute_set')->load($id);
                    if ($set->getId() && $defaultGroupId = $set->getDefaultGroupId()) {
                        $object->setAttributeSetId($set->getId())->setAttributeGroupId($defaultGroupId);
                        $this->saveInSetIncluding($object);
                    }
                }
            }
            if (!empty($delete)) {
                foreach ($delete as $id) {
                    $set = $collection->getItemById($id);
                    if ($set) {
                        $this->deleteEntity($set);
                    }
                }
            }
        }
    }

    /**
     * get the attribute set collection that an attribute belongs to
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
     */
    public function getAttributeInSets($attribute)
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->addFieldToFilter('main_table.entity_type_id', $attribute->getEntityTypeId());
        $collection->getSelect()
            ->join(array('entity_attribute' => $this->getTable('eav/entity_attribute')), 'main_table.attribute_set_id = entity_attribute.attribute_set_id', array('entity_attribute_id'))
            ->where('entity_attribute.attribute_id = ?', $attribute->getId());

        return $collection;
    }

    /**
     * Delete entity
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Catalog_Model_Resource_Attribute
     */
    public function deleteEntity(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('eav/entity_attribute'))
            ->where('entity_attribute_id = ?', (int)$object->getEntityAttributeId());
        $result = $this->_getReadAdapter()->fetchRow($select);

        if ($result) {
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $result['attribute_id']);

            if (!$attribute->getData('is_user_defined')) {
                Mage::throwException(Mage::helper('eav')->__("Attribute '%s' is a system attribute and must be included in all attribute sets.", $attribute->getAttributeCode()));
            }

            if ($this->isUsedBySuperProducts($attribute, $result['attribute_set_id'])) {
                Mage::throwException(Mage::helper('eav')->__("Attribute '%s' used in configurable products and can not be removed from '%s' attribute set", $attribute->getAttributeCode(), $object->getAttributeSetName()));
            }
        }

        $condition = array('entity_attribute_id = ?' => $object->getEntityAttributeId());
        $this->_getWriteAdapter()->delete($this->getTable('entity_attribute'), $condition);

        return $this;
    }
}