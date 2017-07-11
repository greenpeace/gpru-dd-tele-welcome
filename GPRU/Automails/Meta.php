<?php
namespace GPRU\Automails;

use GPRU\DB\AutomailsMetaCollection;

class Meta
{
    private $prefix;

    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    public function get($key)
    {
        $meta = AutomailsMetaCollection::getOne("AutomailsMeta.value", array(
                "where" => "AutomailsMeta.key = :key",
                "params" => array(':key' => $this->prefix.'_'.$key)
            )
        );
        if (!isset($meta)) {
            return null;
        }
        return $meta->value;
    }
}
