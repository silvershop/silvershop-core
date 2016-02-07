<?php

class ZoneSelectField extends DropdownField
{
    public function getSource()
    {
        $zones = DataObject::get("Zone");
        if ($zones && $zones->exists()) {
            return array("" => $this->emptyString) + $zones->map('ID', 'Name');
        }
        return array();
    }
}
