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

namespace pocketmine\network\mcpe\protocol\types\login;

/**
 * Model class for LoginPacket JSON data for JsonMapper
 */
final class ClientData{

	/**
	 * @var ClientDataAnimationFrame[]
	 * @required
	 */
	public $AnimatedImageData;

	/**
	 * @var string
	 * @required
	 */
	public $ArmSize;

	/**
	 * @var string
	 * @required
	 */
	public $CapeData;

	/**
	 * @var string
	 * @required
	 */
	public $CapeId;

	/**
	 * @var int
	 * @required
	 */
	public $CapeImageHeight;

	/**
	 * @var int
	 * @required
	 */
	public $CapeImageWidth;

	/**
	 * @var bool
	 * @required
	 */
	public $CapeOnClassicSkin;

	/**
	 * @var int
	 * @required
	 */
	public $ClientRandomId;

	/**
	 * @var int
	 * @required
	 */
	public $CurrentInputMode;

	/**
	 * @var int
	 * @required
	 */
	public $DefaultInputMode;

	/**
	 * @var string
	 * @required
	 */
	public $DeviceId;

	/**
	 * @var string
	 * @required
	 */
	public $DeviceModel;

	/**
	 * @var int
	 * @required
	 */
	public $DeviceOS;

	/**
	 * @var string
	 * @required
	 */
	public $GameVersion;

	/**
	 * @var int
	 * @required
	 */
	public $GuiScale;

	/**
	 * @var string
	 * @required
	 */
	public $LanguageCode;

	/**
	 * @var ClientDataPersonaSkinPiece[]
	 * @required
	 */
	public $PersonaPieces;

	/**
	 * @var bool
	 * @required
	 */
	public $PersonaSkin;

	/**
	 * @var ClientDataPersonaPieceTintColor[]
	 * @required
	 */
	public $PieceTintColors;

	/**
	 * @var string
	 * @required
	 */
	public $PlatformOfflineId;

	/**
	 * @var string
	 * @required
	 */
	public $PlatformOnlineId;

	/** @var string */
	public $PlatformUserId = ""; //xbox-only, apparently

	/**
	 * @var bool
	 * @required
	 */
	public $PremiumSkin = false;

	/**
	 * @var string
	 * @required
	 */
	public $SelfSignedId;

	/**
	 * @var string
	 * @required
	 */
	public $ServerAddress;

	/**
	 * @var string
	 * @required
	 */
	public $SkinAnimationData;

	/**
	 * @var string
	 * @required
	 */
	public $SkinColor;

	/**
	 * @var string
	 * @required
	 */
	public $SkinData;

	/**
	 * @var string
	 * @required
	 */
	public $SkinGeometryData;

	/**
	 * @var string
	 * @required
	 */
	public $SkinId;

	/**
	 * @var int
	 * @required
	 */
	public $SkinImageHeight;

	/**
	 * @var int
	 * @required
	 */
	public $SkinImageWidth;

	/**
	 * @var string
	 * @required
	 */
	public $SkinResourcePatch;

	/**
	 * @var string
	 * @required
	 */
	public $ThirdPartyName;

	/**
	 * @var bool
	 * @required
	 */
	public $ThirdPartyNameOnly;

	/**
	 * @var int
	 * @required
	 */
	public $UIProfile;
}
