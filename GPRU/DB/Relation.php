<?php
namespace GPRU\DB;

class Relation
{
    private $full_relation_name;
    private $relation_name;
    private $collection;
    private $parent_relation;
    private $table_name;
    private $table_alias;
    private $field_prefix;
    private $child_relations;
    private $relation_opts;
    private $relation_level;
    private $on_clause;

    public function __construct($full_relation_name, $relation_name, BaseCollection $collection, $parent_relation, $table_name, $table_alias, $relation_opts)
    {
        $this->full_relation_name = $full_relation_name;
        $this->relation_name = $relation_name;
        $this->collection = $collection;
        $this->parent_relation = $parent_relation;
        $this->table_name = $table_name;
        $this->table_alias = $table_alias;
        $this->field_prefix = (isset($parent_relation) ? $parent_relation->getFieldPrefix().'_' : '').$relation_name;
        $this->relation_opts = $relation_opts;
        $this->relation_level = (isset($parent_relation) ? $parent_relation->relation_level + 1 : 0);

        $this->child_relations = array();
        foreach ($collection->getRelations() as $child_name => $opts) {
            $collection = BaseCollection::getSingleton($opts['collection'], true);
            $this->child_relations[$child_name] = array(
                    'collection' => $collection,
                    'opts' => $opts,
                );
        }

        if (isset($relation_opts)) {
            $on_clause = $relation_opts['on_clause'];
            if (strpos($on_clause, '=') === false) {
                $this->on_clause = $this->table_alias.'.'.$this->collection->getFieldMapping($on_clause).' = '.
                        $this->parent_relation->table_alias.'.'.$this->parent_relation->collection->getFieldMapping($on_clause);
            } else {
                preg_match_all("/(?:<(?:LEFT|RIGHT)>)\.(?:\\w+)/", $on_clause, $match);
                $fields = array_unique($match[0]);
                $fields_map = array();
                foreach ($fields as $field) {
                    list($side, $field_name) = explode('.', $field);
                    $mpped_field;

                    if ($side == '<LEFT>') {
                        $mapped_field = $this->parent_relation->table_alias.'.'.$this->parent_relation->collection->getFieldMapping($field_name);
                    } elseif ($side == '<RIGHT>') {
                        $mapped_field = $this->table_alias.'.'.$this->collection->getFieldMapping($field_name);
                    }
                    $fields_map[$field] = $mapped_field;
                }

                $this->on_clause =
                    preg_replace_callback("/(?:<(?:LEFT|RIGHT)>)\.(?:\\w+)/", function ($m) use ($fields_map) { return $fields_map[$m[0]]; }, $on_clause);
            }
        }
    }

    public function getSelectField($field)
    {
        return $this->getDBField($field).' AS '.$this->field_prefix.'_'.$field;
    }

    public function getDBField($field)
    {
        return $this->table_name.'.'.$this->collection->getFieldMapping($field);
    }

    public function getRelationName()
    {
        return $this->relation_name;
    }

    public function getFullRelationName()
    {
        return $this->full_relation_name;
    }

    public function getParentRelation()
    {
        return $this->parent_relation;
    }

    public function hasChild($child_relation)
    {
        return isset($this->child_relations[$child_relation]);
    }

    public function getChildCollection($child_relation)
    {
        return $this->child_relations[$child_relation]['collection'];
    }

    public function getChildRelationOpts($child_relation)
    {
        return $this->child_relations[$child_relation]['opts'];
    }

    public function getFieldPrefix()
    {
        return $this->field_prefix;
    }

    public function cmp(Relation $b)
    {
        if ($this->relation_level == $b->relation_level) {
            return 0;
        }
        return ($this->relation_level < $b->relation_level ? -1 : 1);
    }

    public function isBaseRelation()
    {
        return $this->relation_level == 0;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getOnClause()
    {
        return $this->on_clause;
    }

    public function getTableNameForJoin()
    {
        return $this->table_name.($this->table_alias != $this->table_name ? ' AS '.$this->table_alias : '');
    }
}
