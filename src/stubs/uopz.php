<?php
/**
 * uopz extension stub file for code completion purposes
 *
 * WARNING: Do not include this file
 *
 * @author  Shoghi Cervantes <shoghicp@gmail.com>
 * @version 1.0.0
 * @link    https://github.com/krakjoe/uopz
 */

/**
 * Invoked by exit() and die(), recieves no arguments. Return boolean TRUE to exit, FALSE to continue
 */
define('ZEND_EXIT', 79);

/**
 * Invoked by object construction, receives the class of object being created as the only argument
 */
define('ZEND_NEW', 68);

/**
 * Invoked by the throw construct, receives the class of exception being thrown as the only argument
 */
define('ZEND_THROW', 108);

/**
 * Determines whether the constants are inherited by the new context
 */
define('ZEND_FETCH_CLASS', 109);

/**
 * Invoked upon composure,
 * recieves the class the trait is being added to as the first argument,
 * and the name of the trait as the second argument
 */
define('ZEND_ADD_TRAIT', 154);

/**
 * Invoked upon composure,
 * recieves the class the interface is being added to as the first argument,
 * and the name of the interface as the second argument
 */
define('ZEND_ADD_INTERFACE', 144);

/**
 * Invoked by instanceof operator,
 * recieves the object being verified as the first argument,
 * and the name of the class which that object should be as the second argument
 */
define('ZEND_INSTANCEOF', 138);



// Modifiers

/**
 * Advance 1 opcode and continue
 */
define('ZEND_USER_OPCODE_CONTINUE', null); //??

/**
 * Enter into new op_array without recursion
 */
define('ZEND_USER_OPCODE_ENTER', null); //??

/**
 * Return to calling op_array within the same executor
 */
define('ZEND_USER_OPCODE_LEAVE', null); //??

/**
 * Dispatch to original opcode handler
 */
define('ZEND_USER_OPCODE_DISPATCH', null); //??

/**
 * Dispatch to a specific handler (OR'd with ZEND opcode constant)
 */
define('ZEND_USER_OPCODE_DISPATCH_TO', null); //??

/**
 * Exit from executor (return from function)
 */
define('ZEND_USER_OPCODE_RETURN', null); //??



//Modifiers


/**
 * Mark function as public, the default
 */
define('ZEND_USER_ACC_PUBLIC', null); //??

/**
 * Mark function as protected
 */
define('ZEND_USER_ACC_PROTECTED', null); //??

/**
 * Mark function as private
 */
define('ZEND_USER_ACC_PRIVATE', null); //??

/**
 * Mark function as static
 */
define('ZEND_USER_ACC_STATIC', null); //??

/**
 * Mark function as abstract
 */
define('ZEND_USER_ACC_ABSTRACT', null); //??

/**
 * Dummy registered for consistency, the default kind of class entry
 */
define('ZEND_USER_ACC_CLASS', null); //??

/**
 * Mark class as interface
 */
define('ZEND_USER_ACC_INTERFACE', null); //??

/**
 * Mark class as trait
 */
define('ZEND_USER_ACC_TRAIT', null); //??

/**
 * Used for getting flags only
*/
define('ZEND_USER_ACC_FLAGS', null); //??

/**
 * Backup a function at runtime, to be restored on shutdown
 *
 * @param string $name Function name or class name
 * @param string $classMethod Class method, if $name is a class name
 *
 * @return void
 */
function uopz_backup($name, $classMethod = null){}

/**
 * Creates a new class of the given name that implements, extends, or uses all of the provided classes
 *
 * @param string $name A legal class name
 * @param array  $classes An array of class, interface and trait names
 * @param array  $methods An associative array of methods, values are either closures or [modifiers => closure]
 * @param array  $properties An associative array of properties, keys are names, values are modifiers
 * @param int    $flags Entry type, by default ZEND_ACC_CLASS
 */
function uopz_compose($name, array $classes, array $methods = [], array $properties = [], $flags = ZEND_USER_ACC_CLASS){}

/**
 * Copy a function by name
 *
 * @param string $name Function name or class name
 * @param string $classMethod Class method, if $name is a class name
 *
 * @return Closure
 */
function uopz_copy($name, $classMethod = null){}

/**
 * Deletes a function or method
 *
 * @param string $name Function name or class name
 * @param string $classMethod Class method, if $name is a class name
 *
 * @return void
 */
function uopz_delete($name, $classMethod = null){}

/**
 * @param string $class The name of the class to extend
 * @param string $parent The name of the class to inherit
 *
 * @return void
 */
function uopz_extend($class, $parent){}

/**
 * Get or set the flags on a class or function entry at runtime
 *
 * @param string $name Function name or class name
 * @param string $classMethod (optional) Class method, if $name is a class name
 * @param int    $flags A valid set of ZEND_ACC_ flags, ZEND_ACC_FETCH to read flags
 *
 * @return void
 */
function uopz_flags($name, $classMethod = null, $flags = null){}

/**
 * Creates a function at runtime
 *
 * @param string  $name Function name or class name
 * @param string  $classMethod (optional) Class method, if $name is a class name
 * @param Closure $handler The Closure for the function
 * @param int     $modifiers The modifiers for the function, by default copied or ZEND_ACC_PUBLIC
 *
 * @return void
 */
function uopz_function($name, $classMethod = null, Closure $handler, $modifiers = ZEND_USER_ACC_PUBLIC){}

/**
 * Makes class implement interface
 *
 * @param string $class
 * @param string $interface
 */
function uopz_implement($class, $interface){}

/**
 * Overloads the specified VM opcode with the user defined function
 *
 * @param int      $opcode A valid opcode, see constants for details of supported codes
 * @param Callable $callable
 *
 * @return void
 */
function uopz_overload($opcode, $callable){}

/**
 * Redefines the given constant as value
 *
 * @param string $name Constant ot class name
 * @param string $classConstant (optional) constant to redefine if $name is a class name
 * @param mixed  $value
 *
 * @return void
 */
function uopz_redefine($name, $classConstant, $value){}

/**
 * Rename a function at runtime
 * Note: If both functions exist, this effectively swaps their names
 *
 * @param string $name Function name or class name
 * @param string $classMethod (optional) Class method, if $name is a class name
 * @param string $rename The new name for the function
 *
 * @return void
 */
function uopz_rename($name, $classMethod = null, $rename){}

/**
 * Restore a previously backed up function
 *
 * @param string $name Function name or class name
 * @param string $classMethod Class method, if $name is a class name
 *
 * @return void
 */
function uopz_restore($name, $classMethod = null){}

/**
 * Removes the constant at runtime
 *
 * @param string $name Function name or class name
 * @param string $classMethod (optional) Class method, if $name is a class name
 *
 * @return void
 */
function uopz_undefine($name, $classMethod = null){}