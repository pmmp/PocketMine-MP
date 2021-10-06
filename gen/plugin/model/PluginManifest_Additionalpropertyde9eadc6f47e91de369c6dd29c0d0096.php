<?php

// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

declare(strict_types = 1);


    namespace pocketmine\plugin\model;



    use PHPModelGenerator\Interfaces\JSONModelInterface;

    use PHPModelGenerator\Exception\ValidationException;


/**
 * Class PluginManifest_Additionalpropertyde9eadc6f47e91de369c6dd29c0d0096
 * @package pocketmine\plugin\model 
 *

 * This is an auto-implemented class implemented by the php-json-schema-model-generator.
 * If you need to implement something in this class use inheritance. Else you will loose your changes if the classes
 * are re-generated.
 */
class PluginManifest_Additionalpropertyde9eadc6f47e91de369c6dd29c0d0096 implements JSONModelInterface
{
    

    
        /** @var string|bool Permission group which has this permission by default */
        protected $default;
    
        /** @var string|null Description of the permission */
        protected $description;
    
    /** @var array */
    private $_rawModelDataInput = [];

    

    /**
     * PluginManifest_Additionalpropertyde9eadc6f47e91de369c6dd29c0d0096 constructor.
     *
     * @param array $modelData
     *
     * @throws ValidationException
     */
    public function __construct(array $modelData = [])
    {
        

        

        
            $this->executeBaseValidators($modelData);
        

        
            
                $this->processDefault($modelData);
            
        
            
                $this->processDescription($modelData);
            
        

        

        $this->_rawModelDataInput = $modelData;

        
    }

    
        private function executeBaseValidators(array &$modelData): void
        {
            $value = &$modelData;

            
                
                if ($additionalProperties =  (function () use ($modelData): array {
    $additionalProperties = array_diff(array_keys($modelData), array (
   'default',
   'description',
));

    

    return $additionalProperties;
})()) {
                    throw new \PHPModelGenerator\Exception\Object\AdditionalPropertiesException($value ?? null, ...array (
  0 => 'PluginManifest_Additionalpropertyde9eadc6f47e91de369c6dd29c0d0096',
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
             * Get the value of default.
             *
             * Permission group which has this permission by default
             *
             * @return string|bool
             */
            public function getDefault()
                
            {
                

                return $this->default;
            }

            

            /**
             * Extract the value, perform validations and set the property default
             *
             * @param array $modelData
             *
             * @throws ValidationException
             */
            protected function processDefault(array $modelData): void
            {
                
                    
                

                $value = array_key_exists('default', $modelData) ? $modelData['default'] : $this->default;

                

                $this->default = $this->validateDefault($value, $modelData);
            }

            /**
             * Execute all validators for the property default
             */
            protected function validateDefault($value, array $modelData)
            {
                
                    
                    if (!array_key_exists('default', $modelData)) {
                        throw new \PHPModelGenerator\Exception\Object\RequiredValueException($value ?? null, ...array (
  0 => 'default',
));
                    }
                
                    
                    if (!in_array($value, array (
   '!admin',
   '!op',
   '!operator',
   'admin',
   false,
   'false',
   'isadmin',
   'isop',
   'isoperator',
   'notadmin',
   'notop',
   'notoperator',
   'op',
   'operator',
   true,
   'true',
), true)) {
                        throw new \PHPModelGenerator\Exception\Generic\EnumException($value ?? null, ...array (
  0 => 'default',
  1 => 
  array (
    0 => '!admin',
    1 => '!op',
    2 => '!operator',
    3 => 'admin',
    4 => false,
    5 => 'false',
    6 => 'isadmin',
    7 => 'isop',
    8 => 'isoperator',
    9 => 'notadmin',
    10 => 'notop',
    11 => 'notoperator',
    12 => 'op',
    13 => 'operator',
    14 => true,
    15 => 'true',
  ),
));
                    }
                

                return $value;
            }
        
    
        
            /**
             * Get the value of description.
             *
             * Description of the permission
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
        
    

    
}

// @codeCoverageIgnoreEnd
