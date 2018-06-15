<?php
class Alpha {

    public function transliterate($string) {
        $string = mb_strtolower($string);
        $mask = TRANSLIT_MASK;
        $result = '';
        $string_arr = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($string_arr as $char) {
            if (isset($mask[$char])) {
                $result .= $mask[$char];
            } else {
                $result .= $char;
            }
        }
        return $result;
    }

    function generateId($count,$data,$table=null){
        $data = $this->transliterate($data);
        $pattern = "/[b-df-hj-np-tv-z]/";
        preg_match_all($pattern,$data,$matches);
        if ($matches) {
            $result = $matches[0];
        } else {
            return false;
        }
        $result = implode('',$result);
        if (strlen($result) < $count){
            $id_str = substr($data,0,$count);
            if (strlen($data) < $count){
                $id_str = str_pad($id_str,$count,'0');
            }
        } else {
            $id_str = substr($result,0,$count);
        }
        if ($table){
            $table_model = new DataModel($table);
            $pri_key = $table_model->id_info['COLUMN_NAME'];
            $conn = DBConnection::getInstance();
            $n = 0;
            do {
                $query = "SELECT * FROM $table WHERE $pri_key = '$id_str'";
                $exists_id = $conn->performQueryFetch($query);
                if (!$exists_id){
                    return $id_str;
                }
                $str_count = (string)$n;
                $id_str = substr_replace($id_str,$str_count,-(strlen($str_count)));
                var_dump($id_str);
                $n++;
            } while ($exists_id);
        }
        return $id_str;
    }

}