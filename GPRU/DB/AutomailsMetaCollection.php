<?php
namespace GPRU\DB;

class AutomailsMetaCollection extends BaseCollection
{
    public function getTableName()
    {
        return 'automails_meta';
    }
    public function getModelClassName()
    {
        return 'AutomailsMeta';
    }
    public function getPrimaryKey()
    {
        return 'key';
    }
}
