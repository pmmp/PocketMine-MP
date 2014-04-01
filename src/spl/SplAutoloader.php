<?php

/**
 * SplAutoloader defines the contract that any OO based autoloader must follow.
 *
 * @author Guilherme Blanco <guilhermeblanco@php.net>
 */
interface SplAutoloader{
	/**
	 * Defines autoloader to work silently if resource is not found.
	 *
	 * @const
	 */
	const MODE_SILENT = 0;

	/**
	 * Defines autoloader to work normally (requiring an un-existent resource).
	 *
	 * @const
	 */
	const MODE_NORMAL = 1;

	/**
	 * Defines autoloader to work in debug mode, loading file and validating requested resource.
	 *
	 * @const
	 */
	const MODE_DEBUG = 2;

	/**
	 * Define the autoloader work mode.
	 *
	 * @param integer $mode Autoloader work mode.
	 */
	public function setMode($mode);

	/**
	 * Add a new resource lookup path.
	 *
	 * @param string $resourceName Resource name, namespace or prefix.
	 * @param mixed  $resourcePath Resource single path or multiple paths (array).
	 */
	public function add($resourceName, $resourcePath = null);

	/**
	 * Load a resource through provided resource name.
	 *
	 * @param string $resourceName Resource name.
	 */
	public function load($resourceName);

	/**
	 * Register this as an autoloader instance.
	 *
	 * @param boolean $prepend Whether to prepend the autoloader or not in autoloader's list.
	 */
	public function register($prepend = false);

	/**
	 * Unregister this autoloader instance.
	 *
	 */
	public function unregister();
}
