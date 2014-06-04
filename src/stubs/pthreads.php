<?php
/**
 * pthreads extension stub file for code completion purposes
 *
 * WARNING: Do not include this file
 *
 * @author  Lisachenko Alexander <lisachenko.it@gmail.com>
 * @version 2.0.0
 * @link    https://github.com/krakjoe/pthreads/blob/master/examples/stub.php
 */

/**
 * The default inheritance mask used when starting Threads and Workers
 */
define('PTHREADS_INHERIT_ALL', 0x111111);

/**
 * Nothing will be inherited by the new context
 */
define('PTHREADS_INHERIT_NONE', 0);

/**
 * Determines whether the ini entries are inherited by the new context
 */
define('PTHREADS_INHERIT_INI', 0x1);

/**
 * Determines whether the constants are inherited by the new context
 */
define('PTHREADS_INHERIT_CONSTANTS', 0x10);

/**
 * Determines whether the class table is inherited by the new context
 */
define('PTHREADS_INHERIT_CLASSES', 0x100);

/**
 * Determines whether the function table is inherited by the new context
 */
define('PTHREADS_INHERIT_FUNCTIONS', 0x100);

/**
 * Determines whether the included_files table is inherited by the new context
 */
define('PTHREADS_INHERIT_INCLUDES', 0x10000);

/**
 * Determines whether the comments are inherited by the new context
 */
define('PTHREADS_INHERIT_COMMENTS', 0x100000);

/**
 * Allow output headers from the threads
 */
define('PTHREADS_ALLOW_HEADERS', 0x1000000);

/**
 * Allow global inheritance for new threads
 */
define('PTHREADS_ALLOW_GLOBALS', 0x10000000);

/**
 * Threaded class
 *
 * Threaded objects form the basis of pthreads ability to execute user code asynchronously;
 * they expose and include synchronization methods and various useful interfaces.
 *
 * Threaded objects, most importantly, provide implicit safety for the programmer;
 * all operations on the object scope are safe.
 *
 * @link  http://www.php.net/manual/en/class.threaded.php
 * @since 2.0.0
 */
class Threaded implements Traversable, Countable, ArrayAccess{
	/**
	 * Fetches a chunk of the objects properties table of the given size
	 *
	 * @param int $size The number of items to fetch
	 *
	 * @link http://www.php.net/manual/en/threaded.chunk.php
	 * @return array An array of items from the objects member table
	 */
	public function chunk($size){
	}

	/**
	 * {@inheritdoc}
	 */
	public function count(){
	}

	/**
	 * Retrieves terminal error information from the referenced object
	 *
	 * @link http://www.php.net/manual/en/threaded.getterminationinfo.php
	 * @return array|bool array containing the termination conditions of the referenced object
	 */
	public function getTerminationInfo(){
	}

	/**
	 * Tell if the referenced object is executing
	 *
	 * @link http://www.php.net/manual/en/threaded.isrunning.php
	 * @return bool A boolean indication of state
	 */
	public function isRunning(){
	}

	/**
	 * Tell if the referenced object exited, suffered fatal errors, or threw uncaught exceptions during execution
	 *
	 * @link http://www.php.net/manual/en/threaded.isterminated.php
	 * @return bool A boolean indication of state
	 */
	public function isTerminated(){
	}

	/**
	 * Tell if the referenced object is waiting for notification
	 *
	 * @link http://www.php.net/manual/en/threaded.iswaiting.php
	 * @return bool A boolean indication of state
	 */
	public function isWaiting(){
	}

	/**
	 * Lock the referenced objects property table
	 *
	 * @link http://www.php.net/manual/en/threaded.lock.php
	 * @return bool A boolean indication of state
	 */
	public function lock(){
	}

	/**
	 * Merges data into the current object
	 *
	 * @param mixed $from      The data to merge
	 * @param bool  $overwrite Overwrite existing keys flag, by default true
	 *
	 * @link http://www.php.net/manual/en/threaded.merge.php
	 * @return bool A boolean indication of success
	 */
	public function merge($from, $overwrite = true){
	}

	/**
	 * Send notification to the referenced object
	 *
	 * @link http://www.php.net/manual/en/threaded.notify.php
	 * @return bool A boolean indication of success
	 */
	public function notify(){
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset){
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value){
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset){
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset){
	}

	/**
	 * Pops an item from the objects property table
	 *
	 * @link http://www.php.net/manual/en/threaded.pop.php
	 * @return mixed The last item from the objects properties table
	 */
	public function pop(){
	}

	/**
	 * The programmer should always implement the run method for objects that are intended for execution.
	 *
	 * @link http://www.php.net/manual/en/threaded.run.php
	 * @return void The methods return value, if used, will be ignored
	 */
	public function run(){
	}

	/**
	 * Shifts an item from the objects properties table
	 *
	 * @link http://www.php.net/manual/en/threaded.shift.php
	 * @return mixed The first item from the objects properties table
	 */
	public function shift(){
	}

	/**
	 * Executes the block while retaining the synchronization lock for the current context.
	 *
	 * @param \Closure $function The block of code to execute
	 * @param mixed    $args     ... Variable length list of arguments to use as function arguments to the block
	 *
	 * @link http://www.php.net/manual/en/threaded.synchronized.php
	 * @return mixed The return value from the block
	 */
	public function synchronized(\Closure $function, $args = null){
	}

	/**
	 * Unlock the referenced objects storage for the calling context
	 *
	 * @link http://www.php.net/manual/en/threaded.unlock.php
	 * @return bool A boolean indication of success
	 */
	public function unlock(){
	}

	/**
	 * Waits for notification from the Stackable
	 *
	 * @param int $timeout An optional timeout in microseconds
	 *
	 * @link http://www.php.net/manual/en/threaded.wait.php
	 * @return bool A boolean indication of success
	 */
	public function wait($timeout){
	}
}

/**
 * Basic thread implementation
 *
 * An implementation of a Thread should extend this declaration, implementing the run method.
 * When the start method of that object is called, the run method code will be executed in separate Thread.
 *
 * @link http://www.php.net/manual/en/class.thread.php
 */
class Thread extends Threaded{

	/**
	 * Detaches a thread
	 *
	 * @return bool A boolean indication of success
	 */
	public function detach(){
	}

	/**
	 * Will return the identity of the Thread that created the referenced Thread
	 *
	 * @link http://www.php.net/manual/en/thread.getcreatorid.php
	 * @return int A numeric identity
	 */
	public function getCreatorId(){
	}

	/**
	 * Will return the instance of currently executing thread
	 *
	 * @return static
	 */
	public static function getCurrentThread(){
	}

	/**
	 * Will return the identity of the currently executing thread
	 *
	 * @link http://www.php.net/manual/en/thread.getcurrentthreadid.php
	 * @return int
	 */
	public static function getCurrentThreadId(){
	}

	/**
	 * Will return the identity of the referenced Thread
	 *
	 * @link http://www.php.net/manual/en/thread.getthreadid.php
	 * @return int
	 */
	public function getThreadId(){
	}

	/**
	 * Tell if the referenced Thread has been joined by another context
	 *
	 * @link http://www.php.net/manual/en/thread.isjoined.php
	 * @return bool A boolean indication of state
	 */
	public function isJoined(){
	}

	/**
	 * Tell if the referenced Thread has been started
	 *
	 * @link http://www.php.net/manual/en/thread.isstarted.php
	 * @return bool A boolean indication of state
	 */
	public function isStarted(){
	}

	/**
	 * Causes the calling context to wait for the referenced Thread to finish executing
	 *
	 * @link http://www.php.net/manual/en/thread.join.php
	 * @return bool A boolean indication of state
	 */
	public function join(){
	}

	/**
	 * Kills the referenced thread, dangerously !
	 *
	 * @link http://www.php.net/manual/en/thread.kill.php
	 */
	public function kill(){
	}

	/**
	 * Will start a new Thread to execute the implemented run method
	 *
	 * @param int $options An optional mask of inheritance constants, by default PTHREADS_INHERIT_ALL
	 *
	 * @link http://www.php.net/manual/en/thread.start.php
	 * @return bool A boolean indication of success
	 */
	public function start($options = PTHREADS_INHERIT_ALL){
	}

	/**
	 * Will execute the Callable in the global scope
	 *
	 * @param Callable $block The code to execute
	 * @param ...      $args    Variable length list of arguments to pass to the Callable upon execution
	 *
	 * @link  http://www.php.net/manual/en/thread.start.php
	 * @return bool A boolean indication of success
	 */
	public static function globally(Callable $block, $args = null){
	}
}

/**
 * Worker
 *
 * Worker Threads have a persistent context, as such should be used over Threads in most cases.
 *
 * When a Worker is started, the run method will be executed, but the Thread will not leave until one
 * of the following conditions are met:
 *   - the Worker goes out of scope (no more references remain)
 *   - the programmer calls shutdown
 *   - the script dies
 * This means the programmer can reuse the context throughout execution; placing objects on the stack of
 * the Worker will cause the Worker to execute the stacked objects run method.
 *
 * @link http://www.php.net/manual/en/class.worker.php
 */
class Worker extends Thread{

	/**
	 * Returns the number of threaded tasks waiting to be executed by the referenced Worker
	 *
	 * @link http://www.php.net/manual/en/worker.getstacked.php
	 * @return int An integral value
	 */
	public function getStacked(){
	}

	/**
	 * Tell if the referenced Worker has been shutdown
	 *
	 * @link http://www.php.net/manual/en/worker.isshutdown.php
	 * @return bool A boolean indication of state
	 */
	public function isShutdown(){
	}

	/**
	 * Tell if a Worker is executing threaded tasks
	 *
	 * @link http://www.php.net/manual/en/worker.isworking.php
	 * @return bool A boolean indication of state
	 */
	public function isWorking(){
	}

	/**
	 * Shuts down the Worker after executing all the threaded tasks previously stacked
	 *
	 * @link http://www.php.net/manual/en/worker.shutdown.php
	 * @return bool A boolean indication of success
	 */
	public function shutdown(){
	}

	/**
	 * Appends the referenced object to the stack of the referenced Worker
	 *
	 * @param Threaded $work Threaded object to be executed by the referenced Worker
	 *
	 * @link http://www.php.net/manual/en/worker.stack.php
	 * @return int The new length of the stack
	 */
	public function stack(Threaded &$work){
	}

	/**
	 * Removes the referenced object ( or all objects if parameter is null ) from stack of the referenced Worker
	 *
	 * @param Threaded $work Threaded object previously stacked onto Worker
	 *
	 * @link http://www.php.net/manual/en/worker.unstack.php
	 * @return int The new length of the stack
	 */
	public function unstack(Threaded &$work = null){
	}
}

/**
 * Mutex class
 *
 * The static methods contained in the Mutex class provide direct access to Posix Mutex functionality.
 *
 * @link http://www.php.net/manual/en/class.mutex.php
 */
class Mutex{

	/**
	 * Create, and optionally lock a new Mutex for the caller
	 *
	 * @param bool $lock Setting lock to true will lock the Mutex for the caller before returning the handle
	 *
	 * @link http://www.php.net/manual/en/mutex.create.php
	 * @return int A newly created and optionally locked Mutex handle
	 */
	final public static function create($lock = false){
	}

	/**
	 * Destroy mutex
	 *
	 * Destroying Mutex handles must be carried out explicitly by the programmer when they are
	 * finished with the Mutex handle.
	 *
	 * @param int $mutex A handle returned by a previous call to Mutex::create().
	 *
	 * @link http://www.php.net/manual/en/mutex.destroy.php
	 * @return bool A boolean indication of success
	 */
	final public static function destroy($mutex){
	}

	/**
	 * Attempt to lock the Mutex for the caller.
	 *
	 * An attempt to lock a Mutex owned (locked) by another Thread will result in blocking.
	 *
	 * @param int $mutex A handle returned by a previous call to Mutex::create().
	 *
	 * @link http://www.php.net/manual/en/mutex.lock.php
	 * @return bool A boolean indication of success
	 */
	final public static function lock($mutex){
	}

	/**
	 * Attempt to lock the Mutex for the caller without blocking if the Mutex is owned (locked) by another Thread.
	 *
	 * @param int $mutex A handle returned by a previous call to Mutex::create().
	 *
	 * @link http://www.php.net/manual/en/mutex.trylock.php
	 * @return bool A boolean indication of success
	 */
	final public static function trylock($mutex){
	}

	/**
	 * Release mutex
	 *
	 * Attempts to unlock the Mutex for the caller, optionally destroying the Mutex handle.
	 * The calling thread should own the Mutex at the time of the call.
	 *
	 * @param int  $mutex   A handle returned by a previous call to Mutex::create().
	 * @param bool $destroy When true pthreads will destroy the Mutex after a successful unlock.
	 *
	 * @link http://www.php.net/manual/en/mutex.unlock.php
	 * @return bool A boolean indication of success
	 */
	final public static function unlock($mutex, $destroy = false){
	}
}

/**
 * Condition class
 *
 * The static methods contained in the Cond class provide direct access to Posix Condition Variables.
 *
 * @link http://www.php.net/manual/en/class.cond.php
 */
class Cond{
	/**
	 * Broadcast to all Threads blocking on a call to Cond::wait().
	 *
	 * @param int $condition A handle to a Condition Variable returned by a previous call to Cond::create()
	 *
	 * @link http://www.php.net/manual/en/cond.broadcast.php
	 * @return bool A boolean indication of success
	 */
	final public static function broadcast($condition){
	}

	/**
	 * Creates a new Condition Variable for the caller.
	 *
	 * @link http://www.php.net/manual/en/cond.create.php
	 * @return int A handle to a Condition Variable
	 */
	final public static function create(){
	}

	/**
	 * Destroy a condition
	 *
	 * Destroying Condition Variable handles must be carried out explicitly by the programmer when they are
	 * finished with the Condition Variable.
	 * No Threads should be blocking on a call to Cond::wait() when the call to Cond::destroy() takes place.
	 *
	 * @param int $condition A handle to a Condition Variable returned by a previous call to Cond::create()
	 *
	 * @link http://www.php.net/manual/en/cond.destroy.php
	 * @return bool A boolean indication of success
	 */
	final public static function destroy($condition){
	}

	/**
	 * Signal a Condition
	 *
	 * @param int $condition A handle to a Condition Variable returned by a previous call to Cond::create()
	 *
	 * @link http://www.php.net/manual/en/cond.signal.php
	 * @return bool A boolean indication of success
	 */
	final public static function signal($condition){
	}

	/**
	 * Wait for a signal on a Condition Variable, optionally specifying a timeout to limit waiting time.
	 *
	 * @param int $condition A handle to a Condition Variable returned by a previous call to Cond::create()
	 * @param int $mutex     A handle returned by a previous call to Mutex::create() and owned (locked) by the caller.
	 * @param int $timeout   An optional timeout, in microseconds
	 *
	 * @return bool A boolean indication of success
	 */
	final public static function wait($condition, $mutex, $timeout = null){
	}
}

/**
 * Pool class
 *
 * A Pool is a container for, and controller of, a number of Worker threads, the number of threads can be adjusted
 * during execution, additionally the Pool provides an easy mechanism to maintain and collect references in the
 * proper way.
 *
 * @link http://www.php.net/manual/en/class.pool.php
 */
class Pool{
	/**
	 * The maximum number of Worker threads allowed in this Pool
	 *
	 * @var integer
	 */
	protected $size;

	/**
	 * The name of the Worker class for this Pool
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * The array of Worker threads for this Pool
	 *
	 * @var array|Worker[]
	 */
	protected $workers;

	/**
	 * The array of Stackables submitted to this Pool for execution
	 *
	 * @var array|Threaded[]
	 */
	protected $work;

	/**
	 * The constructor arguments to be passed by this Pool to new Workers upon construction
	 *
	 * @var array
	 */
	protected $ctor;

	/**
	 * The numeric identifier for the last Worker used by this Pool
	 *
	 * @var integer
	 */
	protected $last;

	/**
	 * Construct a new Pool of Workers
	 *
	 * @param integer $size  The maximum number of Workers this Pool can create
	 * @param string  $class The class for new Workers
	 * @param array   $ctor  An array of arguments to be passed to new Workers
	 *
	 * @link http://www.php.net/manual/en/pool.__construct.php
	 */
	public function __construct($size, $class, array $ctor = []){
	}

	/**
	 * Shuts down all Workers, and collect all Stackables, finally destroys the Pool
	 *
	 * @link http://www.php.net/manual/en/pool.__destruct.php
	 */
	public function __destruct(){
	}

	/**
	 * Collect references to completed tasks
	 *
	 * Allows the Pool to collect references determined to be garbage by the given collector
	 *
	 * @param callable $collector
	 *
	 * @link http://www.php.net/manual/en/pool.collect.php
	 */
	public function collect(callable $collector){
	}

	/**
	 * Resize the Pool
	 *
	 * @param integer $size The maximum number of Workers this Pool can create
	 *
	 * @link http://www.php.net/manual/en/pool.resize.php
	 */
	public function resize($size){
	}

	/**
	 * Shutdown all Workers in this Pool
	 *
	 * @link http://www.php.net/manual/en/pool.shutdown.php
	 */
	public function shutdown(){
	}

	/**
	 * Submit the task to the next Worker in the Pool
	 *
	 * @param Threaded $task The task for execution
	 *
	 * @return int the identifier of the Worker executing the object
	 */
	public function submit(Threaded $task){
	}

	/**
	 * Submit the task to the specific Worker in the Pool
	 *
	 * @param int      $worker The worker for execution
	 * @param Threaded $task   The task for execution
	 *
	 * @return int the identifier of the Worker that accepted the object
	 */
	public function submitTo($worker, Threaded $task){
	}
}