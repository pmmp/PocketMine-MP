<?php
/**
 * pthreads extension stub file for code completion purposes
 *
 * @author Lisachenko Alexander <lisachenko.it@gmail.com>
 * @version 3.0.0
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
 * Threaded class
 *
 * Threaded objects form the basis of pthreads ability to execute user code in parallel;
 * they expose and include synchronization methods and various useful interfaces.
 *
 * Threaded objects, most importantly, provide implicit safety for the programmer;
 * all operations on the object scope are safe.
 *
 * @link http://www.php.net/manual/en/class.threaded.php
 * @since 2.0.0
 */
class Threaded implements Traversable, Countable, ArrayAccess, Collectable{
	/**
	 * Increments the object's reference count
	 */
	public function addRef(){}

	/**
	 * Fetches a chunk of the objects properties table of the given size
	 *
	 * @param int  $size The number of items to fetch
	 * @param bool $preserve Preserve the keys of members
	 *
	 * @link http://www.php.net/manual/en/threaded.chunk.php
	 * @return array An array of items from the objects member table
	 */
	public function chunk($size, $preserve = false){}

	/**
	 * {@inheritdoc}
	 */
	public function count(){}

	/**
	 * Decrements the object's reference count
	 */
	public function delRef(){}

	/**
	 * Runtime extending of the Threaded class
	 *
	 * @param string $class The name of the class to extend Threaded
	 *
	 * @return bool A boolean indication of success
	 */
	public static function extend($class){}

	/**
	 * Gets the object's reference count
	 *
	 * @return int The object's reference count
	 */
	public function getRefCount(){}

	/**
	 * A default method for marking an object as ready to be destroyed
	 *
	 * @return bool(true) The referenced object can be destroyed
	 */
	public function isGarbage() : bool{}

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
	 * @param mixed $from The data to merge
	 * @param bool  $overwrite Overwrite existing keys flag
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

	/**
	 * Send notification to one context waiting on the Threaded
	 *
	 * @return bool A boolean indication of success
	 */
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
	 * @param mixed    $args ... Variable length list of arguments to use as function arguments to the block
	 *
	 * @link http://www.php.net/manual/en/threaded.synchronized.php
	 * @return mixed The return value from the block
	 */
	public function synchronized(\Closure $function, ...$args){}

	/**
	 * Waits for notification from the Stackable
	 *
	 * @param int $timeout An optional timeout in microseconds
	 *
	 * @link http://www.php.net/manual/en/threaded.wait.php
	 * @return bool A boolean indication of success
	 */
	public function wait(int $timeout = 0){}
}

/**
 * Volatile class
 *
 * The Volatile class is new to pthreads v3. Its introduction is a consequence of the new immutability semantics of
 * Threaded members of Threaded classes. The Volatile class enables for mutability of its Threaded members, and is also
 * used to store PHP arrays in Threaded contexts.
 *
 * @link http://php.net/manual/en/class.volatile.php
 * @since 3.0.0
 */
class Volatile extends Threaded{
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
	 * Executes the optional collector on each of the tasks, removing the task if true is returned
	 *
	 * @param callable $function The collector to be executed upon each task
	 *
	 * @return int The number of tasks left to be collected
	 */
	public function collect(callable $function = null){}

	/**
	 * Executes the collector on the collectable object passed
	 *
	 * @param Collectable $collectable The collectable object to run the collector on
	 *
	 * @return bool The referenced object can be destroyed
	 */
	public function collector(Collectable $collectable){}

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
	 * @param Threaded $work Threaded object to be executed by the referenced Worker
	 *
	 * @link http://www.php.net/manual/en/worker.stack.php
	 * @return int The new length of the stack
	 */
	public function stack(Threaded $work){}

	/**
	 * Removes the first task (the oldest one) in the stack.
	 *
	 * @link http://www.php.net/manual/en/worker.unstack.php
	 * @return Collectable|null The item removed from the stack
	 */
	public function unstack(){}
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
	 * @param integer $size The maximum number of Workers this Pool can create
	 * @param string  $class The class for new Workers
	 * @param array   $ctor An array of arguments to be passed to new Workers
	 *
	 * @link http://www.php.net/manual/en/pool.__construct.php
	 */
	public function __construct(int $size, string $class = Worker::class, array $ctor = array()){}

	/**
	 * Collect references to completed tasks
	 *
	 * Allows the Pool to collect references determined to be garbage by the given collector
	 *
	 * @param callable $collector
	 *
	 * @link http://www.php.net/manual/en/pool.collect.php
	 */
	public function collect(callable $collector = null){}

	/**
	 * Resize the Pool
	 *
	 * @param integer $size The maximum number of Workers this Pool can create
	 *
	 * @link http://www.php.net/manual/en/pool.resize.php
	 */
	public function resize(int $size){}

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
	 * @param Threaded $task The task for execution
	 *
	 * @return int the identifier of the Worker that accepted the object
	 */
	public function submitTo(int $worker, Threaded $task){}
}

/**
 * Collectable Class
 *
 * Garbage Collection interface for references to objects on Worker stacks
 *
 * @link http://www.php.net/manual/en/class.collectable.php
 */
interface Collectable{
	/**
	 * Determine whether an object is ready to be destroyed
	 *
	 * @return bool Whether the referenced object can be destroyed
	 */
	public function isGarbage() : bool;
}

class Socket extends \Threaded{
	public const AF_UNIX = 1;
	public const AF_INET = 2;
	public const AF_INET6 = 10;
	public const SOCK_STREAM = 1;
	public const SOCK_DGRAM = 2;
	public const SOCK_RAW = 3;
	public const SOCK_SEQPACKET = 5;
	public const SOCK_RDM = 4;
	public const SO_DEBUG = 1;
	public const SO_REUSEADDR = 2;
	public const SO_REUSEPORT = 15;
	public const SO_KEEPALIVE = 9;
	public const SO_DONTROUTE = 5;
	public const SO_LINGER = 13;
	public const SO_BROADCAST = 6;
	public const SO_OOBINLINE = 10;
	public const SO_SNDBUF = 7;
	public const SO_RCVBUF = 8;
	public const SO_SNDLOWAT = 19;
	public const SO_RCVLOWAT = 18;
	public const SO_SNDTIMEO = 21;
	public const SO_RCVTIMEO = 20;
	public const SO_TYPE = 3;
	public const SO_ERROR = 4;
	public const SO_BINDTODEVICE = 25;
	public const SOMAXCONN = 128;
	public const TCP_NODELAY = 1;
	public const SOL_SOCKET = 1;
	public const SOL_TCP = 6;
	public const SOL_UDP = 17;
	public const MSG_OOB = 1;
	public const MSG_WAITALL = 256;
	public const MSG_CTRUNC = 8;
	public const MSG_TRUNC = 32;
	public const MSG_PEEK = 2;
	public const MSG_DONTROUTE = 4;
	public const MSG_EOR = 128;
	public const MSG_CONFIRM = 2048;
	public const MSG_ERRQUEUE = 8192;
	public const MSG_NOSIGNAL = 16384;
	public const MSG_MORE = 32768;
	public const MSG_WAITFORONE = 65536;
	public const MSG_CMSG_CLOEXEC = 1073741824;
	public const EPERM = 1;
	public const ENOENT = 2;
	public const EINTR = 4;
	public const EIO = 5;
	public const ENXIO = 6;
	public const E2BIG = 7;
	public const EBADF = 9;
	public const EAGAIN = 11;
	public const ENOMEM = 12;
	public const EACCES = 13;
	public const EFAULT = 14;
	public const ENOTBLK = 15;
	public const EBUSY = 16;
	public const EEXIST = 17;
	public const EXDEV = 18;
	public const ENODEV = 19;
	public const ENOTDIR = 20;
	public const EISDIR = 21;
	public const EINVAL = 22;
	public const ENFILE = 23;
	public const EMFILE = 24;
	public const ENOTTY = 25;
	public const ENOSPC = 28;
	public const ESPIPE = 29;
	public const EROFS = 30;
	public const EMLINK = 31;
	public const EPIPE = 32;
	public const ENAMETOOLONG = 36;
	public const ENOLCK = 37;
	public const ENOSYS = 38;
	public const ENOTEMPTY = 39;
	public const ELOOP = 40;
	public const EWOULDBLOCK = 11;
	public const ENOMSG = 42;
	public const EIDRM = 43;
	public const ECHRNG = 44;
	public const EL2NSYNC = 45;
	public const EL3HLT = 46;
	public const EL3RST = 47;
	public const ELNRNG = 48;
	public const EUNATCH = 49;
	public const ENOCSI = 50;
	public const EL2HLT = 51;
	public const EBADE = 52;
	public const EBADR = 53;
	public const EXFULL = 54;
	public const ENOANO = 55;
	public const EBADRQC = 56;
	public const EBADSLT = 57;
	public const ENOSTR = 60;
	public const ENODATA = 61;
	public const ETIME = 62;
	public const ENOSR = 63;
	public const ENONET = 64;
	public const EREMOTE = 66;
	public const ENOLINK = 67;
	public const EADV = 68;
	public const ESRMNT = 69;
	public const ECOMM = 70;
	public const EPROTO = 71;
	public const EMULTIHOP = 72;
	public const EBADMSG = 74;
	public const ENOTUNIQ = 76;
	public const EBADFD = 77;
	public const EREMCHG = 78;
	public const ERESTART = 85;
	public const ESTRPIPE = 86;
	public const EUSERS = 87;
	public const ENOTSOCK = 88;
	public const EDESTADDRREQ = 89;
	public const EMSGSIZE = 90;
	public const EPROTOTYPE = 91;
	public const ENOPROTOOPT = 92;
	public const EPROTONOSUPPORT = 93;
	public const ESOCKTNOSUPPORT = 94;
	public const EOPNOTSUPP = 95;
	public const EPFNOSUPPORT = 96;
	public const EAFNOSUPPORT = 97;
	public const EADDRINUSE = 98;
	public const EADDRNOTAVAIL = 99;
	public const ENETDOWN = 100;
	public const ENETUNREACH = 101;
	public const ENETRESET = 102;
	public const ECONNABORTED = 103;
	public const ECONNRESET = 104;
	public const ENOBUFS = 105;
	public const EISCONN = 106;
	public const ENOTCONN = 107;
	public const ESHUTDOWN = 108;
	public const ETOOMANYREFS = 109;
	public const ETIMEDOUT = 110;
	public const ECONNREFUSED = 111;
	public const EHOSTDOWN = 112;
	public const EHOSTUNREACH = 113;
	public const EALREADY = 114;
	public const EINPROGRESS = 115;
	public const EISNAM = 120;
	public const EREMOTEIO = 121;
	public const EDQUOT = 122;
	public const ENOMEDIUM = 123;
	public const EMEDIUMTYPE = 124;

	public function __construct(int $domain, int $type, int $protocol){}

	public function setOption(int $level, int $name, int $value) : bool{}

	public function getOption(int $level, int $name) : int{}

	public function bind(string $host, int $port = 0) : bool{}

	public function listen(int $backlog = 0) : bool{}

	public function accept($class = self::class){}

	public function connect(string $host, int $port = 0) : bool{}

	public static function select(array &$read, array &$write, array &$except, ?int $sec, int $usec = 0, int &$error = null){}

	public function read(int $length, int $flags = 0){}

	public function write(string $buffer, int $length = 0){}

	public function send(string $buffer, int $length, int $flags){}

	public function recvfrom(string &$buffer, int $length, int $flags, string &$name, int &$port = null){}

	public function sendto(string $buffer, int $length, int $flags, string $addr, int $port = 0){}

	public function setBlocking(bool $blocking) : bool{}

	public function getPeerName(bool $port = true) : array{}

	public function getSockName(bool $port = true) : array{}

	public function close(){}

	public function getLastError(bool $clear = false){}

	public function clearError(){}
}
