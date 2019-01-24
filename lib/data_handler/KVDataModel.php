<?php
class KVDataModel extends DataModel {

    public function __construct($table_name,array $id_parts) {
        parent::__construct($table_name);
        $this->id_info['parts'] = $id_parts;
    }

    function generateId($id_part,$part_name){
        do {
            $generated_id = $id_part.$this->randomString($this->id_info['parts'][$part_name]['length']);
            $conn = DBConnection::getInstance();
            $query = "SELECT * FROM $this->table_name WHERE {$this->id_info['COLUMN_NAME']} = '$generated_id';";
            $result = $conn->performQueryFetch($query);
        } while (!empty($result));
        return $generated_id;
    }


    function randomString($length, $string = 'abcdef0123456789'){
        $result='';
        for($i=0;$i<$length;$i++){
            $result .= $string[mt_rand(0,strlen($string) - 1)];
        }
        return $result;
    }


    public function add ($data,$given_id=null){
        if ($given_id){
            return parent::add($data,$given_id);
        } else {
            $id_data=[];
            foreach ($data as $key => $value){
                if (key_exists($key,$this->id_info['parts'])){
                    $id_data[$key] = $value;
                    unset($data[$key]);
                }
            }
            $id="";
            foreach ($this->id_info['parts'] as $key => $value){
                if (key_exists($key,$id_data)){
                    $id .= $id_data[$key];
                } else{
                    $id = $this->generateId($id,$key);
                }
            }
            if (parent::add($data,$id)){
                return $id;
            } else {
                return false;
            }
        }
    }

    public function read($id,$additional=null){
        $conn = DBConnection::getInstance();
        $query = "SELECT * FROM {$this->table_name} ";
        $i=1;
        foreach ($this->id_info['parts'] as $key => $value) {
            if (isset($value['table'])) {
                $query .= " JOIN {$value['table']} ON {$value['table']}.{$key} = SUBSTRING({$this->table_name}.{$this->id_info['COLUMN_NAME']},$i,{$value['length']})";
            }
            $i+=$value['length'];
        }
        if ($additional['fkeys']){
            foreach ($additional['fkeys'] as $fkey => $fvalue){
                $query .= " JOIN $fvalue ON {$this->table_name}.$fkey = $fvalue.$fkey ";
            }
        }
        if ($additional['joins']){
            foreach ($additional['joins'] as $jkey => $jvalue){
                $query .= " JOIN $jvalue ON {$this->table_name}.{$this->id_info['COLUMN_NAME']} = $jvalue.{$this->id_info['COLUMN_NAME']} ";
            }
        }
        $query .= " WHERE {$this->table_name}.{$this->id_info['COLUMN_NAME']} = '$id' ;";
        return $conn->performQueryFetch($query);
    }

    function update($id,$data){
        $update_arr=[];
        $id_change = false;
        $new_id = $id;
        //TODO check consistency
        foreach ($data as $key => $value){
            if (key_exists($key,$this->id_info['parts'])){
                $id_change = true;
                $position = 0;
                foreach ($this->id_info['parts'] as $idkey => $idvalue){
                    $length = $idvalue['length'];
                    if ($idkey == $key){
                        break;
                    }
                    $position += $length;
                }
                $new_id = substr_replace($new_id,$value,$position,$length);
            } else {
                $update_arr[] = "$key = \"$value\"";
            }
        }
        if ($id_change){
            $update_arr[] = "{$this->id_info['COLUMN_NAME']} = '$new_id'";
        }
        $update_str = implode(',', $update_arr);
        $conn = DBConnection::getInstance();
        $query = "UPDATE $this->table_name SET $update_str WHERE {$this->id_info['COLUMN_NAME']} = '$id'";
        $result = $conn->performQuery($query);
        if ($result){
            return $new_id;
        } else {
            return false;
        }
    }

    function getAll($data,$additional = null){
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
            $add_joins=[];
            foreach ($data as $key => $value){
                    if (!key_exists($key,$this->id_info['parts'])) {
                        if ($additional['fkeys'] and key_exists($key,$additional['fkeys'])) {
                            $where_arr[] = "{$additional['fkeys'][$key]}.$key = \"$value\"";
                        } else if ($additional['joins']){
                            $exists = false;
                            foreach ($this->data_structure as $dvalue){
                                if ($dvalue['COLUMN_NAME'] == $key){
                                    $exists = true;
                                    break;
                                }
                            }
                            if ($exists){
                                $where_arr[] = "{$this->table_name}.$key = \"$value\"";
                            } else {
                                if (empty($add_joins)){
                                    foreach ($additional['joins'] as $ajvalue){
                                        $handler = new DataModel($ajvalue);
                                        $add_joins[$ajvalue] = $handler->data_structure;
                                    }
                                }
                                foreach ($add_joins as $adjkey => $adjvalue){
                                   foreach ($adjvalue as $column_info){
                                       if ($column_info['COLUMN_NAME'] == $key){
                                           $join_table_name = $adjkey;
                                           break 2;
                                       }
                                   }
                                }
                                if ($join_table_name) {
                                    $where_arr[] = "$join_table_name.$key = \"$value\"";
                                }
                            }
                        } else {
                            $where_arr[] = "{$this->table_name}.$key = \"$value\"";
                        }
                    } else {
                        $pos_start=1;
                        foreach ($this->id_info['parts'] as $id_key => $id_value){
                            $length = $id_value['length'];
                            if ($id_key == $key){
                                break;
                            }
                            $pos_start  += $length;
                        }
                        $where_arr[] = " SUBSTRING({$this->table_name}.{$this->id_info['COLUMN_NAME']},$pos_start,$length) LIKE \"%$value%\"";
                    }
            }
            if (empty($where_arr)){
                $query_where = '';
            } else {
                $query_where = "WHERE " . implode(' AND ', $where_arr);
            }
        } else {
            $query_where = '';
        }
        $conn = DBConnection::getInstance();
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table_name} ";
        $i=1;
        foreach ($this->id_info['parts'] as $key => $value) {
            if (isset($value['table'])) {
                $query .= " JOIN {$value['table']} ON {$value['table']}.{$key} = SUBSTRING({$this->table_name}.{$this->id_info['COLUMN_NAME']},$i,{$value['length']})";
            }
            $i+=$value['length'];
        }
        if ($additional['fkeys']){
            foreach ($additional['fkeys'] as $fkey => $fvalue){
                $query .= " JOIN $fvalue ON {$this->table_name}.$fkey = $fvalue.$fkey ";
            }
        }
        if ($additional['joins']){
            foreach ($additional['joins'] as $jkey => $jvalue){
                $query .= " JOIN $jvalue ON {$this->table_name}.{$this->id_info['COLUMN_NAME']} = $jvalue.{$this->id_info['COLUMN_NAME']} ";
            }
        }
        $query .= " $query_where LIMIT $start,$offset ;";
        $entities =  $conn->performQueryFetchAll($query);
        if ($entities){
            $query = "SELECT FOUND_ROWS() as count";
            $this->count = $conn->performQueryFetch($query)['count'];
        }
        return $entities;
    }
}