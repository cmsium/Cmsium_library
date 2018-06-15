<?php
class DataModel{
    public $table_name='';
    public $data_structure=[];
    public $id_info;
    public $data_array=[];
    public $count;

    public function __construct($table_name) {
        $this->table_name = $table_name;
        $conn = DBConnection::getInstance();
        $query ="call getTableStructureData('$table_name');";
        $data_structure = $conn->performQueryFetchALL($query);
        if (!$data_structure){
            AppErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
        $this->data_structure = $data_structure;
        foreach ($data_structure as $key => $value){
            if ($value['COLUMN_KEY'] == 'PRI'){
                $this->id_info=$value;
            }
        }
    }

    public function add($data,$given_id=null) {
        $conn = DBConnection::getInstance();
        if ($given_id) {
            $id = $given_id;
            $data[$this->id_info['COLUMN_NAME']] = $id;
        }
        else {
            if ($this->id_info['DATA_TYPE'] != 'int') {
                $id = $conn->generateId($this->table_name, $this->id_info);
                $data[$this->id_info['COLUMN_NAME']] = $id;
            }
        }
        $insert_names_str = implode(',', array_keys($data));
        $insert_values = [];
        foreach ($data as $value){
            $insert_values[] = "\"$value\"";
        }
        $insert_values_str = implode(',', $insert_values);

        $query = "call addDataToEntity('$this->table_name','$insert_names_str','$insert_values_str');";
        return $conn->performQuery($query);
    }

    public function addPrepared($data,$given_id=null) {
        $conn = DBConnection::getInstance();
        if ($given_id) {
            $id = $given_id;
            $data[$this->id_info['COLUMN_NAME']] = $id;
        }
        else {
            if ($this->id_info['DATA_TYPE'] != 'int') {
                $id = $conn->generateId($this->table_name, $this->id_info);
                $data[$this->id_info['COLUMN_NAME']] = $id;
            }
        }
        $insert_names_str = implode(',', array_keys($data));
        $insert_values = [];
        foreach ($data as $value){
            $insert_values[] = "'$value'";
        }
        $insert_values_str = implode(',', $insert_values);

        $query = "call addDataToEntity(?,?,?);";
        return $conn->performPreparedQuery($query,[$this->table_name,$insert_names_str,$insert_values_str]);
    }

    public function update($id,$data){
        $update_arr=[];
        foreach ($data as $key => $value){
            $update_arr[] = "$key = \"$value\"";
        }
        $update_str = implode(',', $update_arr);
        $conn = DBConnection::getInstance();
        $query = "UPDATE $this->table_name SET $update_str WHERE {$this->id_info['COLUMN_NAME']} = '$id'";
        return $conn->performQuery($query);
    }

    public function read($id,$additional=null){
        $conn = DBConnection::getInstance();
        $add_str='';
        if ($additional['fkeys']){
            foreach ($additional['fkeys'] as $fkey => $fvalue){
                $add_str .= " JOIN $fvalue ON {$this->table_name}.$fkey = $fvalue.$fkey ";
            }
        }
        if ($additional['joins']){
            foreach ($additional['joins'] as $jkey => $jvalue){
                $add_str .= " JOIN $jvalue ON {$this->table_name}.{$this->id_info['COLUMN_NAME']} = $jvalue.{$this->id_info['COLUMN_NAME']} ";
            }
        }
        $query = "SELECT * FROM {$this->table_name} $add_str WHERE {$this->table_name}.{$this->id_info['COLUMN_NAME']} = '$id' ;";
        return $conn->performQueryFetch($query);
    }

    public function delete($id,$cons_check_tables=null){
        if ($cons_check_tables){
            foreach ($cons_check_tables as $table){
                $entities = $table->getAll([$this->id_info['COLUMN_NAME'] => $id]);
                if ($entities)
                    return false;
            }
        }
        $conn = DBConnection::getInstance();
        $query = "DELETE FROM {$this->table_name} WHERE {$this->id_info['COLUMN_NAME']} = '$id' ;";
        return $conn->performQuery($query);
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
                $where_arr[] = "$key LIKE \"%$value%\"";
            }
            $query_where = "WHERE ".implode(' AND ',$where_arr);
        } else {
            $query_where = '';
        }
        $conn = DBConnection::getInstance();
        if ($query_where){
            $query = "call getAllEntities('$this->table_name','$query_where',$start,$offset);";
        }
        else {
            $query = "call getAllEntities('$this->table_name',' ',$start,$offset);";
        }
        $categories =  $conn->performQueryFetchAll($query);
        if ($categories){
            $query = "SELECT FOUND_ROWS() as count";
            $this->count = $conn->performQueryFetch($query)['count'];
        }
        return $categories;
    }
}