<?php
class RelationsHandler {
    public $columns = ['node_id','twig','kind','ent_id'];
    public $twig;

    public function __construct($twig_id = null){
        $this->twig = $twig_id;
    }

    public function getTwigs(){
        $conn = DBConnection::getInstance();
        $query = "call getTwigs();";
        return $conn->performQueryFetchAll($query);
    }

    public function generateId($ent_id){
        return md5($ent_id.$this->twig.time());
    }

    public function add($kind,$ent_id,$parent_id = null){
        $id = $this->generateId($ent_id);
        $conn = DBConnection::getInstance();
        $query = "call addRelEntity('$id',$this->twig,$kind,'$ent_id');";
        $conn->startTransaction();
        if (!$conn->performQuery($query)){
            $conn->rollback();
            return false;
        }
        if ($parent_id) {
            if (!$this->connect($parent_id,$id)){
                $conn->rollback();
                return false;
            }
        }
        $conn->commit();
        return $id;
    }

    public function delete($node_id,$ent_delete = true){
        $handler = new DataModel('relations_tree');
        $ent_data = $handler->read($node_id);
        $conn = DBConnection::getInstance();
        $conn->startTransaction();
        if ($ent_delete){
            $kind_handler = new DataModel('entity_kinds');
            $table = $kind_handler->read($ent_data['kind'])['source_table'];
            $ent_handler = new DataModel($table);
            if (!$ent_handler->delete($ent_data['ent_id'])){
                $conn->rollback();
                return false;
            }
        }
        if (!$handler->delete($node_id)){
            $conn->rollback();
            return false;
        }
        if (!$this->detachNode($node_id)){
            $conn->rollback();
            return false;
        }
        $conn->commit();
        return true;
    }

    public function read($id,$data_return = true){
        $conn = DBConnection::getInstance();
        $query = "call readFromRelations('$id');";
        $result = $conn->performQueryFetch($query);
        if (!$result){
            return false;
        }
        if ($data_return){
            $kind_handler = new DataModel('entity_kinds');
            $table = $kind_handler->read($result['kind'])['source_table'];
            $entity_handler = new DataModel($table);
            $data = $entity_handler->read($result['ent_id']);
            $result = array_merge($result,$data);
        }
        return $result;
    }

    public function update($id,$data){
        foreach ($data as $key => $value){
            if (in_array($key,$this->columns)){
                $base_data[$key] = $value;
            } else {
                $ent_data[$key] = $value;
            }
        }
        if (!empty($base_data)){
            $rel_handler = new DataModel('relations_tree');
            if (!$rel_handler->update($id,$base_data)){
                return false;
            }
        }
        if (!empty($ent_data)){
            $base_info = $this->read($id);
            $kind_handler = new DataModel('entity_kinds');
            $table = $kind_handler->read($base_info['kind'])['source_table'];
            $entity_handler = new DataModel($table);
            if (!$entity_handler->update($base_info['ent_id'],$ent_data)){
                return false;
            }
        }
        return true;
    }

    public function generateHashedId($data){
        $str='';
        foreach ($data as $value){
            $str .= $value;
        }
        return md5($str);
    }

    public function connect($id_up,$id_down){
        $conn = DBConnection::getInstance();
        $rel_id = $this->generateHashedId([$id_up,$id_down]);
        $query = "call connectNodes('$rel_id','$id_up','$id_down');";
        return $conn->performQuery($query);
    }

    public function detachNode($node_id){
        $conn = DBConnection::getInstance();
        $query = "call detachNode('$node_id');";
        return $conn->performQuery($query);
    }

    function getConnectedNodes($node_id){
        $conn = DBConnection::getInstance();
        $query = "call getConnectedNodes('$node_id');";
        return $conn->performQueryFetchAll($query);
    }

    function getNodesByFilter($search_data,$start,$limit){
        $search_arr=[];
        if (!empty($search_data)) {
            foreach ($search_data as $column => $value) {
                if (in_array($column,$this->columns)) {
                    $search_arr[] = "tree.$column = \"$value\"";
                }
            }
        }
        if (empty($search_arr))
            $search_str = " ";
        else
            $search_str = "WHERE ".implode(' AND ',$search_arr);

        if (isset($search_data['parent_id'])){
            $parent_str = " JOIN cross_twig_relations as rels ON (rels.id_up = \"{$search_data['parent_id']}\" AND rels.id_down = tree.node_id) ";
        } else {
            $parent_str='';
        }

        $conn = DBConnection::getInstance();
        $query = "SELECT * from relations_tree AS tree 
                  JOIN twigs ON tree.twig = twigs.twig_id 
                  JOIN entity_kinds as kinds ON tree.kind = kinds.kind_id
                  $parent_str
                  $search_str LIMIT $start,$limit";
        $objects = $conn->performQueryFetchAll($query);
        if (!$objects)
            $objects = [];
        $query = "SELECT FOUND_ROWS() as obj_count";
        $count = $conn->performQueryFetch($query);
        return array_merge($count,$objects);
    }

}