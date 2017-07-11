<?php
namespace GPRU\DB;

class RelationManager
{
    private $base_relations;
    private $base_collection;
    private $used_table_aliases = array();
    private $cache = array();

    public function __construct(BaseCollection $base_collection, $base_relations)
    {
        $this->base_collection = $base_collection;
        $this->base_relations = $base_relations;
    }

    public function get($full_relation_name)
    {
        if (isset($this->cache[$full_relation_name])) {
            return $this->cache[$full_relation_name];
        }

        list($parent_relation_name, $relation_name) = $this->splitFullRelationName($full_relation_name);
        $parent_relation = null;
        $relation_opts = null;
        $collection;
        if (isset($parent_relation_name)) {
            $parent_relation = $this->get($parent_relation_name);
            if (!isset($parent_relation)) {
                throw new \ErrorException("Relation $parent_relation_name does not exists");
            }
            if (!$parent_relation->hasChild($relation_name)) {
                throw new \ErrorException("Relation $parent_relation_name doesn't have relation $relation_name");
            }
            $collection = $parent_relation->getChildCollection($relation_name);
            $relation_opts = $parent_relation->getChildRelationOpts($relation_name);
        } else {
            if ($relation_name != $this->base_collection->getModelClassName()) {
                throw new \ErrorException("Base relation must be ".$this->base_collection->getModelClassName().", not $relation_name");
            }
            $collection = $this->base_collection;
        }

        $table_name = $collection->getTableName();
        $table_alias = $this->getTableAlias($table_name);

        $this->cache[$full_relation_name] = new Relation($full_relation_name, $relation_name, $collection, $parent_relation, $table_name, $table_alias, $relation_opts);
        return $this->cache[$full_relation_name];
    }

    private function getTableAlias($table_name)
    {
        $table_alias = $table_name;
        $i = 1;
        while (isset($this->used_table_aliases[$table_alias])) {
            $table_alias = $table_name.$i;
            ++$i;
        }
        $this->used_table_aliases[$table_alias] = 1;

        return $table_alias;
    }

    private function splitFullRelationName($full_relation_name)
    {
        $last_dot_index = strrpos($full_relation_name, '.');
        $parent_relation_name = null;
        $relation_name;
        if ($last_dot_index !== false) {
            $parent_relation_name = substr($full_relation_name, 0, $last_dot_index);
            $relation_name = substr($full_relation_name, $last_dot_index + 1);
        } else {
            $relation_name = $full_relation_name;
        }

        return array($parent_relation_name, $relation_name);
    }
}
