<?php

class Event implements iEvent{
    public $event_id;
    public $user_id;
    public $created_at;
    public $type;
    public $props;

    public function __construct($event_type) {
        $this->isTypeAllowed($event_type);
        $this->type = $event_type;
    }


    public function getNextStep($mask,$current_step){
        if (isset($mask[$current_step])) {
            $result = $mask[$current_step];
            $result[] = $current_step;
            return $result;
        } else
            return [$current_step];
    }


    public function updateEventFilesForm($event,$file_column_name){
        $conn = DBConnection::getInstance();
        $query = "SELECT file_id FROM files_in_event_{$event->type} WHERE event_id = '{$event->event_id}' AND 
        event_column = '{$file_column_name}'";
        $files = $conn->performQueryFetchAll($query);
        $controller = Config::get('controller_url');
        $result_links = "";
        if (!empty($files)) {
            foreach ($files as $file) {
                $request = new Request("$controller/getFileInfo?id={$file['file_id']}");
                $response = $request->sendRequestJSON('GET',null,null);
                $result_links .= "{$response['file_name']}: <a href='/portfolio/update_event_files?event_id={$event->event_id}&amp;type_name={$event->type}&amp;file_id={$file['file_id']}'>удалить</a> | ";
            }
        }
        $user_id = Cookie::getUserId();
        $request = new Request("$controller/getSandboxFiles?user_id=$user_id");
        $files = $request->sendRequestJSON('GET',null,null);
        $options = "";
        foreach ($files as $file){
            $options .= "<option value='{$file['file_id']}'>{$file['file_name']}</option>";
        }
        return $result_links."<br/> добавить: <select multiple='multiple' name='{$file_column_name}[]'>$options</select>";
    }



    public function updateEventFiles($event,$file_column_name,$files){
        $conn = DBConnection::getInstance();
        if (!empty($files)) {
            if (!is_array($files))
                $files = [$files];
            foreach ($files as $file_id) {
                $query = "INSERT INTO files_in_event_{$event->type} (event_id,event_column,file_id)
                          VALUES ('{$event->event_id}','$file_column_name','$file_id');";
                $result = $conn->performQuery($query);
                if (!$result) {
                    AppErrorHandler::throwException(EVENT_UPDATE_ERROR);
                }
                $controller = Config::get('controller_url');
                $request = new Request("$controller/copySandboxFile?id=$file_id");
                $request->sendRequest('GET',null,null);
            }
        }
    }


    public static function deleteEventFile($event_id,$type_name,$file_id){
        $conn = DBConnection::getInstance();
        $query = "DELETE FROM files_in_event_{$type_name} WHERE file_id='$file_id' AND event_id='$event_id'";
        $result = $conn->performQuery($query);
        if (!$result) {
            AppErrorHandler::throwException(UPDATE_ERROR);
        }
        //TODO delete file in file system ?
    }

    public static function deleteAllEventFiles($event_id,$type_name){
        $conn = DBConnection::getInstance();
        $query = "DELETE FROM files_in_event_{$type_name} WHERE event_id='$event_id'";
        $result = $conn->performQuery($query);
//        if (!$result) {
//            AppErrorHandler::throwException(UPDATE_ERROR);
//        }
        //TODO delete file in file system ?
    }


    public function getEventFiles($event,$file_column_name){
        $conn = DBConnection::getInstance();
        $query = "SELECT file_id FROM files_in_event_{$event->type} WHERE event_id = '{$event->event_id}' AND 
        event_column = '{$file_column_name}'";
        $files = $conn->performQueryFetchAll($query);
        $result_links = "";
        if (!empty($files)) {
            foreach ($files as $file) {
                $url = Config::get('controller_url');
                $result_links .= "<a href='http://$url/get?id={$file['file_id']}'>скачать</a> ";
            }
        }
        return $result_links;
    }

    public function createEventFiles($event,$file_column_name,$files){
        $conn = DBConnection::getInstance();
        if (!empty($files)) {
            if (!is_array($files))
                $files = [$files];
            foreach ($files as $file_id) {
                $query = "INSERT INTO files_in_event_{$event->type} (event_id,event_column,file_id) 
                      VALUES ('{$event->event_id}','$file_column_name','$file_id');";
                $result = $conn->performQuery($query);
                if (!$result) {
                    AppErrorHandler::throwException(EVENT_CREATE_ERROR);
                }
                $controller = Config::get('controller_url');
                $request = new Request("$controller/copySandboxFile?id=$file_id");
                $request->sendRequest('GET',null,null);
            }
        }
    }


    /**
     * Check is current event type allowed
     *
     * @param string $event_type Current event type
     * @return bool Allowed|not allowed
     */
    public function isTypeAllowed($event_type){
        $conn = DBConnection::getInstance();
        $query = "CALL isEventTypeAllowed('{$event_type}');";
        $result = $conn->performQuery($query);
        if (!$result)
            AppErrorHandler::throwException(NOT_ALLOWED_EVENT_TYPE, 'page');
        return true;
    }

    public function checkBasicAction($table_name,$method){
        $ref_handler = ReferenceHandler::getInstance();
        $refs = $ref_handler->getAllRefs($table_name);
        if (!$refs) {
            return false;
        }
        foreach ($refs as $column_name => $module_name){
            if ($module_name == 'basic_action'){
                $basic_action_id = $this->props[$column_name];
                $handler = new BasicActionsHandler($basic_action_id,$method);
                return $handler->build();
                }
        }
        return false;
    }


    /**
     * Creates new event in the DB
     *
     * @param $user_id string Id of event owner
     * @param array $user_data Validated data from POST
     * @return bool
     */
    public function create($user_id, array $user_data) {
        $this->created_at = microtime();
        $this->event_id = $this->generateEventId($user_id, $this->created_at);
        $this->user_id = $user_id;
        $conn = DBConnection::getInstance();
        $this->props = $user_data;
        $table_name = $this->getTableName();
        $basic_action_instance = $this->checkBasicAction($table_name,__METHOD__);
        if ($basic_action_instance)
            $basic_action_instance->before($this);
        $insert_array = $this->buildInsertQuery($table_name, $this->props);
        $result = $conn->performPreparedQuery($insert_array['DictionaryQuery'], $insert_array['params']);
        if ($basic_action_instance)
            $basic_action_instance->after($this);
        return $result ? $this->event_id : false;
    }

    /**
     * Update event props in the DB
     *
     * @param $event_id string Id of the event
     * @param array $user_data Validated data from POST
     * @return bool Update Status
     */
    public function update($event_id, array $user_data){
        $event_data = $this->readEvent($event_id);
        if (!$event_data)
            AppErrorHandler::throwException(UNDEFINED_EVENT, 'page');
        $conn = DBConnection::getInstance();
        $query_array = $this->buildUpdateQuery($user_data);
        if ($query_array === true)
            return true;
        $table_name = $this->getTableName();
        $basic_action_instance = $this->checkBasicAction($table_name,__METHOD__);
        if ($basic_action_instance)
            $basic_action_instance->before($this);
        $result = $conn->performPreparedQuery($query_array['DictionaryQuery'], $query_array['params']);
        $this->readEvent($event_id);
        if ($basic_action_instance)
            $basic_action_instance->after($this);
        return $result ? $event_id : false;
    }

    /**
     * Returns Event data array
     *
     * @param string $event_id Data array
     * @return bool
     */
    public function read($event_id){
        return $this->readEvent($event_id);
    }


    public function delete($event_id){
        $event_data = $this->readEvent($event_id);
        if (!$event_data)
            AppErrorHandler::throwException(UNDEFINED_EVENT, 'page');
        $this->deleteEvent($event_id);
    }


    /**
     * Returns valid query for writing event props to database
     *
     * @param $table_name string Name of the props table
     * @param $user_data array Validated data from POST
     * @return array
     */
    protected function buildInsertQuery($table_name, array $user_data) {
        foreach ($user_data as $key => $value) {
            $event_props_columns[] = $key;
            $event_props_values[] = $value;
        }
        $query = "INSERT INTO $table_name(" . implode(", ", $event_props_columns) . ", event_id, user_id) 
                  VALUES(" . str_repeat('?, ', count($user_data)) . "'{$this->event_id}', '{$this->user_id}');";
        return ['DictionaryQuery' => $query, 'params' => $event_props_values];
    }


    /**
     * Returns valid query for updating event props in database
     *
     * @param $user_data array Validated data from POST
     * @return string
     */
    protected function buildUpdateQuery(array $user_data) {
        if (empty($user_data))
          return true;
        foreach ($user_data as $key => $value){
            $query_array[] = "$key = ?";
            $query_array_values[] = $value;
        }
        $table_name = $this->getTableName();
        $query = "UPDATE $table_name SET " . implode(", ", $query_array) . " WHERE event_id = '{$this->event_id}';";
        return ['DictionaryQuery' => $query, 'params' => $query_array_values];
    }


    /**
     * Returns id of the current event based on time and owner's id
     *
     * @param $user_id string user_id id
     * @param $time integer UNIX timestamp
     * @return string Hash
     */
    protected function generateEventId($user_id, $time) {
        return md5($user_id.$time);
    }


    /**
     * Check current event existence in DB and return data
     * @param string $event_id Id of current event
     * @return bool Event existence status
     */
    protected function readEvent($event_id){
        $conn = DBConnection::getInstance();
        $table_name =  $this->getTableName();
        $query = "CALL readEvent('{$table_name}','{$event_id}');";
        $result = $conn->performQueryFetch($query);
        if (!$result) {
            AppErrorHandler::throwException(PERFORM_QUERY_ERROR, 'page');
        }
        $this->setData($result);
        return $result;
    }

    /**
     * Delete event
     * @param string $event_id Id of current event
     * @return bool Event delete status
     */
    public function deleteEvent($event_id){
        $conn = DBConnection::getInstance();
        $table_name =  $this->getTableName();
        $query = "CALL deleteEvent('{$table_name}','{$event_id}');";
        $result = $conn->performQuery($query);
        if (!$result) {
            AppErrorHandler::throwException(PERFORM_QUERY_ERROR, 'page');
        }
        //$this->deleteAllEventFiles($event_id,$table_name);
        return true;
    }

    public function getTableName(){
        return 'event_'.$this->type;
    }

    /**
     * Set event data to object properties
     * @param array $data Event properties array
     */
    protected function setData(array $data){
        foreach ($data as $key => $value){
            if (array_key_exists($key,get_object_vars($this)))
                $this->$key = $value;
            else
                $this->props[$key]=$value;
        }
    }

    public static function toXML(array $event_data, $keys = true) {
        $converter = DataConverter::getInstance();
        $result = $converter->arrayToXML($event_data, 'event', $keys);
        if (!$result) {
            AppErrorHandler::throwException(ARRAY_TO_XML_CONVERT_ERROR);
        }
        return $result;
    }

    /**
     * Return class name of object
     * @return string
     */
    public function getName(){
        return __CLASS__;
    }

    public function __destruct(){
        EventHandler::popFromEvents($this);
    }

}