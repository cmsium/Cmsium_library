<?php
class NPDataModel {

    public function __construct($table_name) {
        $this->table_name = $table_name;
    }

    public function add($data) {
        $conn = DBConnection::getInstance();
        $insert_names_str = implode(',', array_keys($data));
        $insert_values = [];
        foreach ($data as $value){
            $insert_values[] = "\"$value\"";
        }
        $insert_values_str = implode(',', $insert_values);
        $query = "call addDataToEntity('$this->table_name','$insert_names_str','$insert_values_str');";
        return $conn->performQuery($query);
    }

    public function addPrepared($data) {
        $conn = DBConnection::getInstance();
        $insert_names_str = implode(',', array_keys($data));
        $insert_values = [];
        foreach ($data as $value){
            $insert_values[] = "'$value'";
        }
        $insert_values_str = implode(',', $insert_values);

        $query = "call addDataToEntity(?,?,?);";
        return $conn->performPreparedQuery($query,[$this->table_name,$insert_names_str,$insert_values_str]);
    }

    public function read($data,$additional=null){
        $conn = DBConnection::getInstance();
        $add_str='';
        if ($additional['fkeys']){
            foreach ($additional['fkeys'] as $fkey => $fvalue){
                $add_str .= " JOIN $fvalue ON {$this->table_name}.$fkey = $fvalue.$fkey ";
            }
        }
        if (!empty($data)){
            $where_arr=[];
            foreach ($data as $key => $value){
                $where_arr[] = "$key = \"$value\"";
            }
            $query_where = "WHERE ".implode(' AND ',$where_arr);
        } else {
            $query_where = '';
        }
        $query = "SELECT * FROM {$this->table_name} $add_str $query_where;";
        return $conn->performQueryFetchALL($query);
    }

    public function delete($data){
        $where_arr=[];
        foreach ($data as $key => $value){
            $where_arr[] = "$key = \"$value\"";
        }
        $query_where = "WHERE ".implode(' AND ',$where_arr);
        $conn = DBConnection::getInstance();
        $query = "DELETE FROM {$this->table_name} $query_where;";
        return $conn->performQuery($query);
    }

}