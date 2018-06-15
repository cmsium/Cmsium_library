<?php
class EventDumper {

    private static $instance;

    /**
     * Get  Instance of EventDumper
     *
     * @return object Engine New instance or self
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}

    public function createAllTypesDump() {
        $event_handler = EventHandler::getInstance();
        $types = $event_handler->getTypes('both');
        $dump = "";
        foreach ($types as $type) {
            $dump_array[] = $this->createTypeDump($type);
        }
        $dump .= implode(';;',$dump_array);
        return $dump;
    }

    /**
     * Creates event dump from XML schema
     *
     * @param array $type_names Name of the current event type
     * @return string
     */
    public function createTypeDump(array $type_names) {
        $converter = DataConverter::getInstance();
        $file = new File(ROOTDIR."/app/modules/portfolio/xml/generated_event_schemas/{$type_names['name']}.xml");
        $xml_array = $converter->XMLToArray($file->getContent());
        unset($xml_array['item'][0]);
        $dump = "name={$type_names['t_name']}&model=";
        foreach ($xml_array['item'] as $column) {
            $props = $column['column_type']['props'];
            if (isset($props['item']) && is_array($props['item'])) {
                foreach ($props['item'] as &$value) {
                    switch ($column['column_type']['name']) {
                        case 'enum':
                            $value = "'$value'";
                            break;
                        case 'set':
                            $value = "'$value'";
                            break;
                        case 'decimal': break;
                        default: break;
                    }
                }
                unset($value);
                $props['item'] = implode(',', $props['item']);
            }
            $model = "({$column['t_column_name']}|{$column['column_type']['t_name']}";
            if (!empty($props)) {
                if (isset($props['item'])) {
                    $model .= "({$props['item']})";
                } else {
                    $model .= "($props)";
                }
            }
            $constraints = isset($column['column_type']['constraints']) ? $column['column_type']['constraints'] : [];
            if (!empty($constraints)) {
                if (is_array($constraints['item'])) {
                    $model .= '|'.implode('|',$constraints['item']);
                } else {
                    $model .= '|'.$constraints['item'];
                }
            }
            if (isset($column['column_type']['foreign_key'])) {
                $model .= "|FOREIGN KEY {$column['column_type']['foreign_key']}";
            }
            if (isset($column['column_type']['reference'])) {
                $model .= "|REFERENCE {$column['column_type']['reference']}";
            }
            $model .= ')';
            $dump_models[] = $model;
        }
        $dump .= implode(';',$dump_models);
        return $dump;
    }

}