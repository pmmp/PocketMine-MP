<?php
/**
 * pthreads extension stub file for code completion purposes
 *
 * WARNING: Do not include this file
 *
 * @author  Lisachenko Alexander <lisachenko.it@gmail.com>, updated by Shoghi Cervantes <shoghicp@gmail.com>
 * @version 3.0.0
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

interface Collectable{

	/**
	 * @return bool
	 */
	public function isGarbage();
}

class Volatile extends Threaded{

}

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
class Threaded implements Traversable, Collectable{

	/**
	 * @param object $obj
	 */
	public static function extend($obj){

	}

	/**
	 * Fetches a chunk of the objects properties table of the given size
	 *
	 * @param int  $size     The number of items to fetch
	 * @param bool $preserve default false
	 * @return array An array of items from the objects member table
	 * @link http://www.php.net/manual/en/threaded.chunk.php
	 */
	public function chunk($size, bool $preserve = false){}

	/**
	 * {@inheritdoc}
	 */
	public function count(){}

	/**
	 * Tell if the referenced object is executing
	 *
	 * @link http://www.php.net/manual/en/threaded.isrunning.php
	 * @return bool A boolean indication of state
	 */
	public function isRunning(){}

	/**
	 * Tell if the referenced object exited, suffered fatal errors, or threw uncaught exceptions during execution
	 *
	 * @link http://www.php.net/manual/en/threaded.isterminated.php
	 * @return bool A boolean indication of state
	 */
	public function isTerminated(){}

	/**
	 * Merges data into the current object
	 *
	 * @param mixed $from      The data to merge
	 * @param bool  $overwrite Overwrite existing keys flag, by default true
	 *
	 * @link http://www.php.net/manual/en/threaded.merge.php
	 * @return bool A boolean indication of success
	 */
	public function merge($from, $overwrite = true){}

	/**
	 * Send notification to the referenced object
	 *
	 * @link http://www.php.net/manual/en/threaded.notify.php
	 * @return bool A boolean indication of success
	 */
	public function notify(){}

	public function notifyOne(){}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset){}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value){}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset){}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset){}

	/**
	 * Pops an item from the objects property table
	 *
	 * @link http://www.php.net/manual/en/threaded.pop.php
	 * @return mixed The last item from the objects properties table
	 */
	public function pop(){}

	/**
	 * The programmer should always implement the run method for objects that are intended for execution.
	 *
	 * @link http://www.php.net/manual/en/threaded.run.php
	 * @return void The methods return value, if used, will be ignored
	 */
	public function run(){}

	/**
	 * Shifts an item from the objects properties table
	 *
	 * @link http://www.php.net/manual/en/threaded.shift.php
	 * @return mixed The first item from the objects properties table
	 */
	public function shift(){}

	/**
	 * Executes the block while retaining the synchronization lock for the current context.
	 *
	 * @param \Closure $function The block of code to execute
	 * @param mixed    $args     ... Variable length list of arguments to use as function arguments to the block
	 *
	 * @link http://www.php.net/manual/en/threaded.synchronized.php
	 * @return mixed The return value from the block
	 */
	public function synchronized(\Closure $function, $args = null){}

	/**
	 * Waits for notification from the Stackable
	 *
	 * @param int $timeout An optional timeout in microseconds
	 *
	 * @link http://www.php.net/manual/en/threaded.wait.php
	 * @return bool A boolean indication of success
	 */
	public function wait($timeout = 0){}

	/**
	 * @return int
	 */
	public function getRefCount(){}

	public function addRef(){}

	public function delRef(){}

	/**
	 * @return bool
	 */
	public function isGarbage(){}
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
	 * Will return the identity of the Thread that created the referenced Thread
	 *
	 * @link http://www.php.net/manual/en/thread.getcreatorid.php
	 * @return int A numeric identity
	 */
	public function getCreatorId(){}

	/**
	 * Will return the instance of currently executing thread
	 *
	 * @return static
	 */
	public static function getCurrentThread(){}

	/**
	 * Will return the identity of the currently executing thread
	 *
	 * @link http://www.php.net/manual/en/thread.getcurrentthreadid.php
	 * @return int
	 */
	public static function getCurrentThreadId(){}

	/**
	 * Will return the identity of the referenced Thread
	 *
	 * @link http://www.php.net/manual/en/thread.getthreadid.php
	 * @return int
	 */
	public function getThreadId(){}

	/**
	 * Tell if the referenced Thread has been joined by another context
	 *
	 * @link http://www.php.net/manual/en/thread.isjoined.php
	 * @return bool A boolean indication of state
	 */
	public function isJoined(){}

	/**
	 * Tell if the referenced Thread has been started
	 *
	 * @link http://www.php.net/manual/en/thread.isstarted.php
	 * @return bool A boolean indication of state
	 */
	public function isStarted(){}

	/**
	 * Causes the calling context to wait for the referenced Thread to finish executing
	 *
	 * @link http://www.php.net/manual/en/thread.join.php
	 * @return bool A boolean indication of state
	 */
	public function join(){}

	/**
	 * Will start a new Thread to execute the implemented run method
	 *
	 * @param int $options An optional mask of inheritance constants, by default PTHREADS_INHERIT_ALL
	 *
	 * @link http://www.php.net/manual/en/thread.start.php
	 * @return bool A boolean indication of success
	 */
	public function start(int $options = PTHREADS_INHERIT_ALL){}
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
	public function getStacked(){}

	/**
	 * Tell if the referenced Worker has been shutdown
	 *
	 * @link http://www.php.net/manual/en/worker.isshutdown.php
	 * @return bool A boolean indication of state
	 */
	public function isShutdown(){}

	public function collector(Collectable $collectable){}

	/**
	 * Shuts down the Worker after executing all the threaded tasks previously stacked
	 *
	 * @link http://www.php.net/manual/en/worker.shutdown.php
	 * @return bool A boolean indication of success
	 */
	public function shutdown(){}

	/**
	 * Appends the referenced object to the stack of the referenced Worker
	 *
	 * @param Collectable $work Collectable object to be executed by the referenced Worker
	 *
	 * @link http://www.php.net/manual/en/worker.stack.php
	 * @return int The new length of the stack
	 */
	public function stack(Collectable &$work){}

	/**
	 * Removes the first item from the stack
	 *
	 * @link http://www.php.net/manual/en/worker.unstack.php
	 * @return int The new length of the stack
	 */
	public function unstack(){}

	/**
	 * Collects finished objects
	 *
	 * @param callable $function
	 *
	 * @link http://www.php.net/manual/en/worker.collect.php
	 * @return void
	 */
	public function collect(callable $function = null){}
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
	 * @param integer     $size  The maximum number of Workers this Pool can create
	 * @param string|null $class The class for new Workers
	 * @param array|null  $ctor  An array of arguments to be passed to new Workers
	 *
	 * @link http://www.php.net/manual/en/pool.__construct.php
	 */
	public function __construct($size, $class, $ctor = []){}

	/**
	 * Collect references to completed tasks
	 *
	 * Allows the Pool to collect references determined to be garbage by the given collector
	 *
	 * @param callable $collector
	 *
	 * @link http://www.php.net/manual/en/pool.collect.php
	 */
	public function collect(callable $collector){}

	/**
	 * Resize the Pool
	 *
	 * @param integer $size The maximum number of Workers this Pool can create
	 *
	 * @link http://www.php.net/manual/en/pool.resize.php
	 */
	public function resize($size){}

	/**
	 * Shutdown all Workers in this Pool
	 *
	 * @link http://www.php.net/manual/en/pool.shutdown.php
	 */
	public function shutdown(){}

	/**
	 * Submit the task to the next Worker in the Pool
	 *
	 * @param Threaded $task The task for execution
	 *
	 * @return int the identifier of the Worker executing the object
	 */
	public function submit(Threaded $task){}

	/**
	 * Submit the task to the specific Worker in the Pool
	 *
	 * @param int      $worker The worker for execution
	 * @param Threaded $task   The task for execution
	 *
	 * @return int the identifier of the Worker that accepted the object
	 */
	public function submitTo($worker, Threaded $task){}
}