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

namespace pocketmine\updater;

/**
 * Model class for JsonMapper to represent the information returned from the updater API.
 * @link https://update.pmmp.io/api
 */
final class UpdateInfo{
	/**
	 * @var string
	 * @required
	 */
	public $job;
	/**
	 * @var string
	 * @required
	 */
	public $php_version;
	/**
	 * @var string
	 * @required
	 */
	public $base_version;
	/**
	 * @var int
	 * @required
	 */
	public $build_number;
	/**
	 * @var bool
	 * @required
	 */
	public $is_dev;
	/**
	 * @var string
	 * @required
	 */
	public $branch;
	/**
	 * @var string
	 * @required
	 */
	public $git_commit;
	/**
	 * @var string
	 * @required
	 */
	public $mcpe_version;
	/**
	 * @var string
	 * @required
	 */
	public $phar_name;
	/**
	 * @var int
	 * @required
	 */
	public $build;
	/**
	 * @var int
	 * @required
	 */
	public $date;
	/**
	 * @var string
	 * @required
	 */
	public $details_url;
	/**
	 * @var string
	 * @required
	 */
	public $download_url;
}
