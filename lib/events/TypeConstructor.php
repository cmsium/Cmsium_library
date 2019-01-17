<?php
//class TypeConstructor extends Constructor {
class TypeConstructor {

    private static $instance;

    public static function getInstance($type_name)
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($type_name);
        }
        return self::$instance;
    }

    protected function __construct($type_name){
        $this->table_prefix = 'event';
        $this->types_table = 'event_types';
        $this->basic_columns = ['event_id'=>['type'=>'varchar(32)','flags'=>'NOT NULL'],
            'user_id'=>['type'=>'varchar(32)','flags'=>'NOT NULL'],
            'created_at'=>['type'=>'date','flags'=>'NOT NULL']];
        $this->primary='event_id';
        $this->blacklist = ['event_id','user_id'];
        $this->errors = ['create_table_error'=>CREATE_EVENT_TYPE_ERROR,
            'update_table_error'=>UPDATE_EVENT_TYPE_ERROR];
        $this->procedures = ['delete_table'=>'deleteEventTable',
            'get_schema'=>'getEventTableColumns'];
        $this->schema_class = 'EventSchema';
        $this->entity_class = 'Event';
        $this->schema_pattern = EVENT_SCHEMA_PATTERN;
        $this->schema_class_module = "events";
        $this->schema_path_module = "portfolio";
        $this->schemas_path = 'generated_event_schemas';
        $this->xml_basic_columns = [['t_column_name'=>"дата события", "column_name"=>"created_at","column_type"=>["name"=>"date","t_name"=>"date",'props'=>""]]];
        $this->type_name = $type_name;
    }

    /**
     * Get event subclass name based on type
     *
     * @return string Subclass name
     */
    public function getEventClass(){
        $event_type = $this->type_name;
        $name = explode('_',$event_type);
        $name = array_map('ucfirst',$name);
        $name = implode("", $name);
        return "Event".$name;
    }

    public function killInstance(){
        self::$instance = null;
    }
}