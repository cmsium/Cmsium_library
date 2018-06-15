<?php
class JSONDataModel extends DataModel {
    public $json_field;

    public function __construct($table_name) {
        parent::__construct($table_name);
        foreach ($this->data_structure as $key => $value){
            if ($value['DATA_TYPE'] == 'json'){
                $this->json_field=$value['COLUMN_NAME'];
            }
        }
    }

    public function update ($id,$data) {
        $conn = DBConnection::getInstance();
        $json_data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $query = "UPDATE $this->table_name SET {$this->json_field} = '$json_data' WHERE {$this->id_info['COLUMN_NAME']} = '$id';";
        return $conn->performQuery($query);
    }

    public function read($id,$additional=null){
        $data = parent::read($id);
        $result_data = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if ($key == $this->json_field) {
                    $result_data = array_merge($result_data, json_decode($value, true));
                } else {
                    $result_data[$key] = $value;
                }
            }
        }
        return $result_data;
    }

    public function add ($data,$given_id=null){
        $json_data[$this->json_field] = json_encode($data,JSON_UNESCAPED_UNICODE);
        return parent::addPrepared($json_data,$given_id);
    }

    function getAll($data){
        if (isset($data['start']) and isset($data['limit'])){
            $start = $data['start'];
            $offset = $data['limit'];
            unset($data['limit']);
            unset($data['start']);
        } else {
            $start=0;
            $offset=10000;
        }
        if (!empty($data)){
            $where_arr=[];
            foreach ($data as $key => $value){
                $where_arr[] = "{$this->json_field}->'$.\"{$key}\"' LIKE \"%$value%\"";
            }
            $query_where = "WHERE ".implode(' AND ',$where_arr);
        } else {
            $query_where = '';
        }
        $conn = DBConnection::getInstance();
        $query = "SELECT SQL_CALC_FOUND_ROWS {$this->id_info['COLUMN_NAME']},{$this->json_field} FROM {$this->table_name}
        $query_where LIMIT $start,$offset;";
        $result =  $conn->performQueryFetchAll($query);
        if ($result){
            $query = "SELECT FOUND_ROWS() as count";
            $this->count = $conn->performQueryFetch($query)['count'];
            foreach ($result as $key => &$value) {
                $value = array_merge($value, json_decode($value[$this->json_field], true));
                unset($value[$this->json_field]);
            }
        }
        return $result;
    }

}