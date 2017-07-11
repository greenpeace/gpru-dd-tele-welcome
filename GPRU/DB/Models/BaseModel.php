<?php
namespace GPRU\DB\Models;

class BaseModel
{
    public function __construct($row, $fields = null, $field_prefix = null)
    {
        if (isset($fields)) {
            foreach ($fields as $field => $db_field) {
                $row_field = $field_prefix.'_'.$field;
                $this->$field = $row[$row_field];
            }
        }
    }

    public function asArray()
    {
        $arr = get_object_vars($this);
        foreach ($arr as $key => $value) {
            if (is_object($value)) {
                $child_arr = $value->asArray();
                foreach ($child_arr as $child_key => $child_value) {
                    $arr["$key.$child_key"] = $child_value;
                }
                unset($arr[$key]);
            }
        }
        return $arr;
    }
}
