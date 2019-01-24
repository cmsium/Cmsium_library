<?php

/**
 * Created by PhpStorm.
 * User: nick
 * Date: 16.05.17
 * Time: 15:15
 */
class BasicActionsHandler{

    public $basic_action_id = "";
    public $name = "";
    public $t_name = "";
    public $event;
    public $method = "";

    public function __construct($basic_action_id,$method){
        $this->getBasicActionData($basic_action_id);
        $this->method = $method;
    }

    public function getBasicActionData($basic_action_id){
        $conn = DBConnection::getInstance();
        $query = "CALL getBasicActionData('{$basic_action_id}');";
        $result =  $conn->performQueryFetch($query);
        $this->basic_action_id = $result['baction_id'];
        $this->name = $result['name'];
        $this->t_name = $result['t_name'];
    }

    public function build (){
        try {
            $basic_action_class = $this->name;
            $path = ROOTDIR.'/app/modules/events/basic_actions/'.$basic_action_class.'.php';
            if (file_exists($path))
                require $path;
            else
                return false;
            if (!in_array("iBasicAction",class_implements($basic_action_class)))
                AppErrorHandler::throwException(CUSTOM_CLASS_ERROR, 'page');
            $basic_action_instance = new $basic_action_class($this->method);
            return $basic_action_instance;
        } catch (Exception $e){
            return AppErrorHandler::errorMassage($e);
        }
    }
}