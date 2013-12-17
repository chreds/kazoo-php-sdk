<?php

namespace Kazoo\Api;

use stdClass;

class JsonSchemaObjectFactory {
    
    /**
     * 
     * @param Kazoo\Client $client
     * @param string $uri
     * @param string $entity_class
     * @param string $schema
     * @return Kazoo\Api\Data\AbstractEntity
     */
    public static function getNew(\Kazoo\Client $client, $uri, $entity_class, $schema) {
        $entityInstance = new $entity_class($client, $uri);
        return self::transformToObject(json_decode($schema), $entityInstance);
    }

    private static function transformToObject($json, $accumulator) {
        if (!property_exists($json, 'properties')) {
            return $accumulator;
        }

        foreach ($json->properties as $property_name => $property_meta) {
            if ($property_name !== "properties") {
                if (property_exists($property_meta, 'type')) {
                    switch ($property_meta->type) {
                        case 'string':
                            $accumulator->$property_name = "";
                            break;
                        case 'object':
                            $accumulator->$property_name = self::transformToObject($json->properties->$property_name, new stdClass());
                            break;
                        case 'boolean':
                            if (property_exists($property_meta, 'default')) {
                                $accumulator->$property_name = $property_meta->default;
                            } else {
                                $accumulator->$property_name = false;   //If no default setting is made, set to default
                            }
                            break;
                        case 'array':
                            $accumulator->$property_name = array();
                            break;
                        case 'integer':
                            $accumulator->$property_name = null;
                            break;
                        case 'enum':
                            $accumulator->$property_name = "";
                            break;
                    }
                }
            }
        }

        return $accumulator;
    }

}