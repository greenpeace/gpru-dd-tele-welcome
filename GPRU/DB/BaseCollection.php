<?php
namespace GPRU\DB;

abstract class BaseCollection
{
    private static $singletones = array();

    public static function getSingleton($class_name, $short = false)
    {
        if ($short) {
            $class_name = __NAMESPACE__.'\\'.$class_name;
        }
        if (!isset(self::$singletones[$class_name])) {
            self::$singletones[$class_name] = new $class_name;
        }
        return self::$singletones[$class_name];
    }

    private static function dbh()
    {
        return Connection::dbh('join_greenpeace');
    }

    private static function fieldMapper($model_class_name, $field_name)
    {
        return $field_name;
    }

    private static function getFullModelClassName($model_class_name)
    {
        return __NAMESPACE__.'\\Models\\'.$model_class_name;
    }

    public static function getOne($select_fields, $opts = null)
    {
        $a = self::get($select_fields, array_merge($opts, array("limit" => 1)));
        return count($a) > 0 ? $a[0] : null;
    }

    public static function getHash($select_fields, $opts)
    {
        $result = array();
        $all = self::get($select_fields, $opts);

        foreach ($all as $item) {
            $key = $opts['hash_by']($item);
            $result[$key] = $item;
        }
        return $result;
    }

    public static function get($select_fields, $opts = null)
    {
        $col = self::getSingleton(get_called_class());

        if (!isset($select_fields)) {
            throw new \ErrorException("bad args bitch");
        }
        if (!is_array($select_fields)) {
            $select_fields = preg_split('/\s*,\s*/', trim($select_fields));
            if ($select_fields[count($select_fields) - 1] == '') {
                unset($select_fields[count($select_fields) - 1]);
            }
        }

        $sql_where = (isset($opts['where']) ? $opts['where'] : '');
        $sql_order_by = (isset($opts['order by']) ? $opts['order by'] : '');
        $sql_limit = (isset($opts['limit']) ? $opts['limit'] : '');

        $query_relations = $col->processFields($select_fields, $sql_where, $sql_order_by, $opts);

        $sql_select_fields = array();
        $sql_joins = array();
        foreach ($query_relations as $relation) {
            foreach ($relation['fields'] as $field => $dbfield) {
                $sql_select_fields[] = $dbfield;
            }
            $join_type = '';
            $on_clause = '';
            if ($relation['relation']->getParentRelation() != null) {
                if ($relation['optional']) {
                    $join_type = 'LEFT JOIN ';
                } else {
                    $join_type = 'INNER JOIN ';
                }
                $on_clause = ' ON '.$relation['relation']->getOnClause();
            }
            $table_name = $relation['relation']->getTableNameForJoin();
            $sql_joins[] = $join_type.$table_name.$on_clause;
        }

        $sql = "SELECT ".implode(",\n    ", $sql_select_fields)."\n".
               "FROM ".implode("\n    ", $sql_joins).
               ($sql_where != '' ? "\nWHERE $sql_where" : '').
               ($sql_order_by != '' ? "\nORDER BY $sql_order_by" : '').
               ($sql_limit != '' ? "\nLIMIT $sql_limit" : '');

        print "DEBUG SQL:\n$sql\n";
        if (isset($opts["params"])) {
            print_r($opts["params"]);
            print "\n";
        }
        $dbh = self::dbh();
        $sth = $dbh->prepare($sql);
        $sth->execute(isset($opts["params"]) ? $opts["params"] : null);

        $relations_cache = array();
        $obj_rows = array();
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $objs = array();
            foreach ($query_relations as $relation) {
                $full_relation_name = $relation['relation']->getFullRelationName();
                $col = $relation['relation']->getCollection();
                $pk = $relation['relation']->getFieldPrefix().'_'.$col->getPrimaryKey();
                $id = $row[$pk];
                if (!isset($id)) {
                    continue;
                }
                if (!isset($relations_cache[$full_relation_name][$id])) {
                    $full_model_class_name = self::getFullModelClassName($col->getModelClassName());
                    $relations_cache[$full_relation_name][$id] = new $full_model_class_name($row, $relation['fields'], $relation['relation']->getFieldPrefix());
                }
                $obj = $relations_cache[$full_relation_name][$id];
                $objs[$full_relation_name] = $obj;
                if ($relation['relation']->getParentRelation() != null) {
                    $parent_obj = $objs[$relation['relation']->getParentRelation()->getFullRelationName()];
                    $relation_name = $relation['relation']->getRelationName();
                    $parent_obj->$relation_name = $obj;
                } else {
                    $obj_rows[] = $obj;
                }
            }
        }
        return $obj_rows;
    }

    private $relation_manager;
    private static $counter = 0;
    private $model_class_name;
    private $fields_map;
    private function __construct()
    {
        print "constructing ".get_class($this)."\n";
        if (self::$counter > 15) {
            throw new \Exception("Error Processing Request", 1);
        }
        self::$counter++;
        $relations = $this->getRelations();
        $this->relation_manager = new RelationManager($this, $relations);
        $this->model_class_name = $this->getModelClassName();
        $this->fields_map = array();

        $class_vars = get_class_vars($this->getFullModelClassName($this->model_class_name));
        if ($class_vars === false) {
            throw new \ErrorException("Model ".$this->model_class_name." not found");
        }
        $custom_db = $this->getDBFieldsMapping();
        foreach ($class_vars as $var_name => $var_value) {
            if (isset($relations[$var_name])) {
                continue;
            }

            $dbname = isset($custom_db[$var_name]) ? $custom_db[$var_name] : $var_name;
            $this->fields_map[$var_name] = $dbname;
        }
    }

    public abstract function getModelClassName();
    public abstract function getPrimaryKey();
    public abstract function getTableName();

    public function hasPrimaryKey()
    {
        return true;
    }
    public function getRelations()
    {
        return array();
    }
    public function getDBFieldsMapping()
    {
        return array();
    }
    public final function getFieldMapping($field)
    {
        if (!isset($this->fields_map[$field])) {
            throw new \ErrorException("Unknown field $field in ".$this->model_class_name);
        }
        return $this->fields_map[$field];
    }

    private function processFields(&$select_fields, &$sql_where, &$sql_order_by, $opts = null)
    {
        $fields = array_unique(array_merge($select_fields, $this->extractFields($sql_where), $this->extractFields($sql_order_by)));

        $fields_map = array();
        $query_relations = array();
        foreach ($fields as $field) {
            list($full_relation_name, $field_name, $optional) = $this->splitField($field, @$opts['aliases']);
            $relation = $this->relation_manager->get($full_relation_name);

            $walk_relations = $relation;

            while (isset($walk_relations))
            {
                if (!isset($query_relations[$walk_relations->getFullRelationName()])) {
                    $query_relations[$walk_relations->getFullRelationName()]  = array(
                            'relation' => $walk_relations,
                            'fields' => array(),
                            'optional' => $optional,
                        );
                } elseif (!$optional && $query_relations[$walk_relations->getFullRelationName()]['optional']) {
                    $query_relations[$walk_relations->getFullRelationName()]['optional'] = false;
                } else {
                    break;
                }
                $walk_relations = $walk_relations->getParentRelation();
            }

            $fields_map[$field] = $relation->getDBField($field_name);
        }
        list($sql_where, $sql_order_by) =
            preg_replace_callback("/(?:\\w+\??\.)+(?:\\w+)/", function ($m) use ($fields_map) { return $fields_map[$m[0]]; }, array($sql_where, $sql_order_by));

        foreach ($select_fields as $field) {
            list($full_relation_name, $field_name, $optional) = $this->splitField($field, @$opts['aliases']);

            $query_relation = &$query_relations[$full_relation_name];
            $query_relation['fields'][$field_name] = $query_relation['relation']->getSelectField($field_name);
        }

        $query_relations = array_values($query_relations);
        usort($query_relations,
            function($a, $b) {
                $cmp = $a['relation']->cmp($b['relation']);
                if ($cmp == 0) {
                    if (!$a['optional'] && $b['optional']) {
                        return -1;
                    } elseif ($a['optional'] && !$b['optional']) {
                        return 1;
                    }
                }
                return $cmp;
            }
        );
        foreach ($query_relations as &$relation) {
            if ($relation['relation']->isBaseRelation() && !$relation['relation']->getCollection()->hasPrimaryKey()) {
                continue;
            }
            $pk = $relation['relation']->getCollection()->getPrimaryKey();
            if (!isset($relation['fields'][$pk])) {
                $relation['fields'][$pk] = $relation['relation']->getSelectField($pk);
            }
        }

        return $query_relations;
    }

    private function splitField($field, $aliases)
    {
        $last_dot_index = strrpos($field, '.');
        if ($last_dot_index === false) {
            throw new \ErrorException("Field $field has no dot");
        }
        $optional = false;
        $relation_name = substr($field, 0, $last_dot_index);
        if (substr($relation_name, strlen($relation_name) - 1) == '?') {
            $optional = true;
            $relation_name = substr($relation_name, 0, strlen($relation_name) - 1);
        }
        $field_name = substr($field, $last_dot_index + 1);
        if (isset($aliases) && isset($aliases[$relation_name])) {
            $relation_name = $aliases[$relation_name];
        }

        return array($relation_name, $field_name, $optional);
    }

    private function extractFields($str)
    {
        $result = array();
        if ($str != '') {
            preg_match_all("/(?:\\w+\??\.)+(?:\\w+)/", $str, $result);
            $result = $result[0];
        }
        return $result;
    }
}
