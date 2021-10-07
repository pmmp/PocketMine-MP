<?php

// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

declare(strict_types = 1);


    namespace pocketmine\plugin\model;



    use PHPModelGenerator\Interfaces\JSONModelInterface;

    use PHPModelGenerator\Exception\ValidationException;


/**
 * Class PluginManifest_Additionalproperty3a366b0629f407e724333385722a07cf
 * @package pocketmine\plugin\model 
 *

 * This is an auto-implemented class implemented by the php-json-schema-model-generator.
 * If you need to implement something in this class use inheritance. Else you will loose your changes if the classes
 * are re-generated.
 */
class PluginManifest_Additionalproperty3a366b0629f407e724333385722a07cf implements JSONModelInterface
{
    

    
        /** @var string[]|null Aliases of the command */
        protected $aliases;
    
        /** @var string|null Description of the command as shown in /help */
        protected $description;
    
        /** @var string Name of the permission to check for, or multiple permissions separated */
        protected $permission;
    
        /** @var string|null Message to send to users if permission checks fail. Occurrences of <permission> are replaced with the `permission` property. */
        protected $permissionMessage;
    
        /** @var string|null Usage message of the command */
        protected $usage;
    
    /** @var array */
    private $_rawModelDataInput = [];

    

    /**
     * PluginManifest_Additionalproperty3a366b0629f407e724333385722a07cf constructor.
     *
     * @param array $modelData
     *
     * @throws ValidationException
     */
    public function __construct(array $modelData = [])
    {
        

        

        
            $this->executeBaseValidators($modelData);
        

        
            
                $this->processAliases($modelData);
            
        
            
                $this->processDescription($modelData);
            
        
            
                $this->processPermission($modelData);
            
        
            
                $this->processPermissionMessage($modelData);
            
        
            
                $this->processUsage($modelData);
            
        

        

        $this->_rawModelDataInput = $modelData;

        
    }

    
        private function executeBaseValidators(array &$modelData): void
        {
            $value = &$modelData;

            
                
                if ($additionalProperties =  (function () use ($modelData): array {
    $additionalProperties = array_diff(array_keys($modelData), array (
   'aliases',
   'description',
   'permission',
   'permission-message',
   'usage',
));

    

    return $additionalProperties;
})()) {
                    throw new \PHPModelGenerator\Exception\Object\AdditionalPropertiesException($value ?? null, ...array (
  0 => 'PluginManifest_Additionalproperty3a366b0629f407e724333385722a07cf',
  1 => $additionalProperties,
));
                }
            

            
        }
    

    /**
     * Get the raw input used to set up the model
     *
     * @return array
     */
    public function getRawModelDataInput(): array
    {
        return $this->_rawModelDataInput;
    }

    
        
            /**
             * Get the value of aliases.
             *
             * Aliases of the command
             *
             * @return string[]|null
             */
            public function getAliases()
                : ?array
            {
                

                return $this->aliases;
            }

            

            /**
             * Extract the value, perform validations and set the property aliases
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processAliases(array $modelData): void
            {
                
                    
                        if (!array_key_exists('aliases', $modelData) && $this->aliases === null) {
                            return;
                        }
                    
                

                $value = array_key_exists('aliases', $modelData) ? $modelData['aliases'] : $this->aliases;

                

                $this->aliases = $this->validateAliases($value, $modelData);
            }

            /**
             * Execute all validators for the property aliases
             */
            protected function validateAliases($value, array $modelData)
            {
                
                    
                    if (!is_array($value)) {
                        throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'aliases',
  1 => 'array',
));
                    }
                
                    $invalidItems_59914f24f37c7daa41a38db269718114 = [];
                    if (is_array($value) && (function (&$items) use (&$invalidItems_59914f24f37c7daa41a38db269718114) {
    

    foreach ($items as $index => &$value) {
        

        try {
            

            
                
                if (!is_string($value)) {
                    throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'item of array aliases',
  1 => 'string',
));
                }
            

            
        } catch (\Exception $e) {
            // collect all errors concerning invalid items
            isset($invalidItems_59914f24f37c7daa41a38db269718114[$index])
                ? $invalidItems_59914f24f37c7daa41a38db269718114[$index][] = $e
                : $invalidItems_59914f24f37c7daa41a38db269718114[$index] = [$e];
        }
    }

    

    return !empty($invalidItems_59914f24f37c7daa41a38db269718114);
})($value)) {
                        throw new \PHPModelGenerator\Exception\Arrays\InvalidItemException($value ?? null, ...array (
  0 => 'aliases',
  1 => $invalidItems_59914f24f37c7daa41a38db269718114,
));
                    }
                

                return $value;
            }
        
    
        
            /**
             * Get the value of description.
             *
             * Description of the command as shown in /help
             *
             * @return string|null
             */
            public function getDescription()
                : ?string
            {
                

                return $this->description;
            }

            

            /**
             * Extract the value, perform validations and set the property description
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processDescription(array $modelData): void
            {
                
                    
                        if (!array_key_exists('description', $modelData) && $this->description === null) {
                            return;
                        }
                    
                

                $value = array_key_exists('description', $modelData) ? $modelData['description'] : $this->description;

                

                $this->description = $this->validateDescription($value, $modelData);
            }

            /**
             * Execute all validators for the property description
             */
            protected function validateDescription($value, array $modelData)
            {
                
                    
                    if (!is_string($value)) {
                        throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'description',
  1 => 'string',
));
                    }
                

                return $value;
            }
        
    
        
            /**
             * Get the value of permission.
             *
             * Name of the permission to check for, or multiple permissions separated
             *
             * @return string
             */
            public function getPermission()
                : string
            {
                

                return $this->permission;
            }

            

            /**
             * Extract the value, perform validations and set the property permission
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processPermission(array $modelData): void
            {
                
                    
                

                $value = array_key_exists('permission', $modelData) ? $modelData['permission'] : $this->permission;

                

                $this->permission = $this->validatePermission($value, $modelData);
            }

            /**
             * Execute all validators for the property permission
             */
            protected function validatePermission($value, array $modelData)
            {
                
                    
                    if (!array_key_exists('permission', $modelData)) {
                        throw new \PHPModelGenerator\Exception\Object\RequiredValueException($value ?? null, ...array (
  0 => 'permission',
));
                    }
                
                    
                    if (!is_string($value)) {
                        throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'permission',
  1 => 'string',
));
                    }
                

                return $value;
            }
        
    
        
            /**
             * Get the value of permission-message.
             *
             * Message to send to users if permission checks fail. Occurrences of <permission> are replaced with the `permission` property.
             *
             * @return string|null
             */
            public function getPermissionMessage()
                : ?string
            {
                

                return $this->permissionMessage;
            }

            

            /**
             * Extract the value, perform validations and set the property permissionMessage
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processPermissionMessage(array $modelData): void
            {
                
                    
                        if (!array_key_exists('permission-message', $modelData) && $this->permissionMessage === null) {
                            return;
                        }
                    
                

                $value = array_key_exists('permission-message', $modelData) ? $modelData['permission-message'] : $this->permissionMessage;

                

                $this->permissionMessage = $this->validatePermissionMessage($value, $modelData);
            }

            /**
             * Execute all validators for the property permissionMessage
             */
            protected function validatePermissionMessage($value, array $modelData)
            {
                
                    
                    if (!is_string($value)) {
                        throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'permission-message',
  1 => 'string',
));
                    }
                

                return $value;
            }
        
    
        
            /**
             * Get the value of usage.
             *
             * Usage message of the command
             *
             * @return string|null
             */
            public function getUsage()
                : ?string
            {
                

                return $this->usage;
            }

            

            /**
             * Extract the value, perform validations and set the property usage
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processUsage(array $modelData): void
            {
                
                    
                        if (!array_key_exists('usage', $modelData) && $this->usage === null) {
                            return;
                        }
                    
                

                $value = array_key_exists('usage', $modelData) ? $modelData['usage'] : $this->usage;

                

                $this->usage = $this->validateUsage($value, $modelData);
            }

            /**
             * Execute all validators for the property usage
             */
            protected function validateUsage($value, array $modelData)
            {
                
                    
                    if (!is_string($value)) {
                        throw new \PHPModelGenerator\Exception\Generic\InvalidTypeException($value ?? null, ...array (
  0 => 'usage',
  1 => 'string',
));
                    }
                

                return $value;
            }
        
    

    
}

// @codeCoverageIgnoreEnd
