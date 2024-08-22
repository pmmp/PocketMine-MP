<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\scheduler;

use pmmp\thread\Runnable;
use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use pocketmine\thread\NonThreadSafeValue;
use function array_key_exists;
use function igbinary_serialize;
use function igbinary_unserialize;
use function is_null;
use function is_scalar;
use function spl_object_id;

/**
 * Class used to run async tasks in other threads.
 *
 * An AsyncTask is run by a thread pool of reusable threads, and doesn't have its own dedicated thread. A thread is
 * usually chosen from the pool at random to run the task (though a specific thread in the pool may be selected
 * manually, if needed).
 * Reusing threads this way has a much lower performance cost than starting an entirely new thread for every operation.
 * AsyncTasks are therefore suitable for brief CPU-bound tasks, such as world generation, compression/decompression of
 * data, etc.
 *
 * AsyncTask SHOULD NOT be used for I/O-bound tasks, such as network I/O, file I/O, database I/O, etc. The server's
 * central AsyncPool is used for things like compressing network packets for sending, so using AsyncTask for I/O will
 * slow the whole server down, stall chunk loading, etc.
 *
 * An AsyncTask SHOULD NOT run for more than a few seconds. For tasks that run for a long time or indefinitely, create
 * a dedicated thread instead.
 *
 * The Server instance is not accessible inside {@link AsyncTask::onRun()}. It can only be accessed in the main server
 * thread, e.g. during {@link AsyncTask::onCompletion()} or {@link AsyncTask::onProgressUpdate()}. This means that
 * whatever you do in onRun() must be able to work without the Server instance.
 *
 * If you want to store non-thread-safe objects to access when the task completes, store them using
 * {@link AsyncTask::storeLocal}.
 */
abstract class AsyncTask extends Runnable{
	/**
	 * @var mixed[][] object hash => mixed data
	 * @phpstan-var array<int, array<string, mixed>>
	 *
	 * Used to store thread-local data to be used by onCompletion().
	 */
	private static array $threadLocalStorage = [];

	/** @phpstan-var ThreadSafeArray<int, string>|null */
	private ?ThreadSafeArray $progressUpdates = null;

	private ThreadSafe|string|int|bool|null|float $result = null;

	private bool $submitted = false;
	private bool $finished = false;

	public function run() : void{
		$this->result = null;

		$this->onRun();

		$this->finished = true;
		AsyncWorker::getNotifier()->wakeupSleeper();
	}

	/**
	 * @deprecated
	 */
	public function isCrashed() : bool{
		return $this->isTerminated();
	}

	/**
	 * Returns whether this task has finished executing, whether successfully or not. This differs from isRunning()
	 * because it is not true prior to task execution.
	 */
	public function isFinished() : bool{
		return $this->finished || $this->isTerminated();
	}

	public function hasResult() : bool{
		return $this->result !== null;
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		if($this->result instanceof NonThreadSafeValue){
			return $this->result->deserialize();
		}
		return $this->result;
	}

	public function setResult(mixed $result) : void{
		$this->result = is_scalar($result) || is_null($result) || $result instanceof ThreadSafe ? $result : new NonThreadSafeValue($result);
	}

	/**
	 * @deprecated
	 */
	public function cancelRun() : void{
		//NOOP
	}

	/**
	 * @deprecated
	 */
	public function hasCancelledRun() : bool{
		return false;
	}

	public function setSubmitted() : void{
		$this->submitted = true;
	}

	public function isSubmitted() : bool{
		return $this->submitted;
	}

	/**
	 * Actions to execute when run
	 */
	abstract public function onRun() : void;

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 */
	public function onCompletion() : void{

	}

	/**
	 * Call this method from {@link AsyncTask::onRun} (AsyncTask execution thread) to schedule a call to
	 * {@link AsyncTask::onProgressUpdate} from the main thread with the given progress parameter.
	 *
	 * @param mixed $progress A value that can be safely serialize()'ed.
	 */
	public function publishProgress(mixed $progress) : void{
		$progressUpdates = $this->progressUpdates;
		if($progressUpdates === null){
			$progressUpdates = $this->progressUpdates = new ThreadSafeArray();
		}
		$progressUpdates[] = igbinary_serialize($progress) ?? throw new \InvalidArgumentException("Progress must be serializable");
	}

	/**
	 * @internal Only call from AsyncPool.php on the main thread
	 */
	public function checkProgressUpdates() : void{
		$progressUpdates = $this->progressUpdates;
		if($progressUpdates !== null){
			while(($progress = $progressUpdates->shift()) !== null){
				$this->onProgressUpdate(igbinary_unserialize($progress));
			}
		}
	}

	/**
	 * Called from the main thread after {@link AsyncTask::publishProgress} is called.
	 * All {@link AsyncTask::publishProgress} calls should result in {@link AsyncTask::onProgressUpdate} calls before
	 * {@link AsyncTask::onCompletion} is called.
	 *
	 * @param mixed $progress The parameter passed to {@link AsyncTask#publishProgress}. It is serialize()'ed
	 *                        and then unserialize()'ed, as if it has been cloned.
	 */
	public function onProgressUpdate($progress) : void{

	}

	/**
	 * @deprecated No longer used
	 */
	public function onError() : void{

	}

	/**
	 * Saves mixed data in thread-local storage. Data stored using this storage is **only accessible from the thread it
	 * was stored on**. Data stored using this method will **not** be serialized.
	 * This can be used to store references to variables which you need later on on the same thread, but not others.
	 *
	 * For example, plugin references could be stored in the constructor of the async task (which is called on the main
	 * thread) using this, and then fetched in onCompletion() (which is also called on the main thread), without them
	 * becoming serialized.
	 *
	 * Scalar types can be stored directly in class properties instead of using this storage.
	 *
	 * Objects stored in this storage can be retrieved using fetchLocal() on the same thread that this method was called
	 * from.
	 */
	protected function storeLocal(string $key, mixed $complexData) : void{
		self::$threadLocalStorage[spl_object_id($this)][$key] = $complexData;
	}

	/**
	 * Retrieves data stored in thread-local storage.
	 *
	 * If you used storeLocal(), you can use this on the same thread to fetch data stored. This should be used during
	 * onProgressUpdate() and onCompletion() to fetch thread-local data stored on the parent thread.
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException if no data were stored by this AsyncTask instance.
	 */
	protected function fetchLocal(string $key){
		$id = spl_object_id($this);
		if(!isset(self::$threadLocalStorage[$id]) || !array_key_exists($key, self::$threadLocalStorage[$id])){
			throw new \InvalidArgumentException("No matching thread-local data found on this thread");
		}

		return self::$threadLocalStorage[$id][$key];
	}

	final public function __destruct(){
		$this->reallyDestruct();
		unset(self::$threadLocalStorage[spl_object_id($this)]);
	}

	/**
	 * Override this to do normal __destruct() cleanup from a child class.
	 */
	protected function reallyDestruct() : void{

	}
}
