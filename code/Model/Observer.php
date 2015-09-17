<?php

class Dtn_Eav_Model_Observer
{
    public function catalogAttributeSaveAfter($event)
    {
        $attribute = $event->getAttribute();
        if ($attribute && $attribute->getId() && $attribute->hasData('sets')) {
            Mage::getModel('dtn_eav/resource_attribute')->saveInSetsIncluding($attribute);
        }
    }
}