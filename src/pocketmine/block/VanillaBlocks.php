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

namespace pocketmine\block;

/**
 * This class provides a nice type-safe way to access vanilla blocks.
 * The entire class is auto-generated based on the current contents of the block factory.
 * Do not modify this class manually.
 */
final class VanillaBlocks{

	private function __construct(){
		//NOOP
	}

	//region auto-generated code

	public static function ACACIA_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(395, 0));
	}

	public static function ACACIA_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(196, 0));
	}

	public static function ACACIA_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 4));
	}

	public static function ACACIA_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(187, 0));
	}

	public static function ACACIA_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(161, 0));
	}

	public static function ACACIA_LOG() : Log{
		return Log::cast(BlockFactory::get(162, 0));
	}

	public static function ACACIA_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 4));
	}

	public static function ACACIA_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(405, 0));
	}

	public static function ACACIA_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 4));
	}

	public static function ACACIA_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(445, 0));
	}

	public static function ACACIA_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 4));
	}

	public static function ACACIA_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(163, 0));
	}

	public static function ACACIA_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(400, 0));
	}

	public static function ACACIA_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 4));
	}

	public static function ACTIVATOR_RAIL() : ActivatorRail{
		return ActivatorRail::cast(BlockFactory::get(126, 0));
	}

	public static function AIR() : Air{
		return Air::cast(BlockFactory::get(0, 0));
	}

	public static function ALLIUM() : Flower{
		return Flower::cast(BlockFactory::get(38, 2));
	}

	public static function ANDESITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 5));
	}

	public static function ANDESITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 3));
	}

	public static function ANDESITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(426, 0));
	}

	public static function ANDESITE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 4));
	}

	public static function ANVIL() : Anvil{
		return Anvil::cast(BlockFactory::get(145, 0));
	}

	public static function AZURE_BLUET() : Flower{
		return Flower::cast(BlockFactory::get(38, 3));
	}

	public static function BANNER() : Banner{
		return Banner::cast(BlockFactory::get(176, 0));
	}

	public static function BARRIER() : Transparent{
		return Transparent::cast(BlockFactory::get(416, 0));
	}

	public static function BED() : Bed{
		return Bed::cast(BlockFactory::get(26, 0));
	}

	public static function BEDROCK() : Bedrock{
		return Bedrock::cast(BlockFactory::get(7, 0));
	}

	public static function BEETROOTS() : Beetroot{
		return Beetroot::cast(BlockFactory::get(244, 0));
	}

	public static function BIRCH_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(396, 0));
	}

	public static function BIRCH_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(194, 0));
	}

	public static function BIRCH_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 2));
	}

	public static function BIRCH_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(184, 0));
	}

	public static function BIRCH_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(18, 2));
	}

	public static function BIRCH_LOG() : Log{
		return Log::cast(BlockFactory::get(17, 2));
	}

	public static function BIRCH_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 2));
	}

	public static function BIRCH_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(406, 0));
	}

	public static function BIRCH_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 2));
	}

	public static function BIRCH_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(441, 0));
	}

	public static function BIRCH_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 2));
	}

	public static function BIRCH_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(135, 0));
	}

	public static function BIRCH_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(401, 0));
	}

	public static function BIRCH_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 2));
	}

	public static function BLACK_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 15));
	}

	public static function BLACK_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 15));
	}

	public static function BLACK_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 15));
	}

	public static function BLACK_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(235, 2));
	}

	public static function BLACK_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 15));
	}

	public static function BLACK_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 15));
	}

	public static function BLACK_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 15));
	}

	public static function BLACK_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 15));
	}

	public static function BLUE_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 11));
	}

	public static function BLUE_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 11));
	}

	public static function BLUE_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 11));
	}

	public static function BLUE_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(231, 2));
	}

	public static function BLUE_ICE() : BlueIce{
		return BlueIce::cast(BlockFactory::get(266, 0));
	}

	public static function BLUE_ORCHID() : Flower{
		return Flower::cast(BlockFactory::get(38, 1));
	}

	public static function BLUE_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 11));
	}

	public static function BLUE_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 11));
	}

	public static function BLUE_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 11));
	}

	public static function BLUE_TORCH() : Torch{
		return Torch::cast(BlockFactory::get(204, 5));
	}

	public static function BLUE_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 11));
	}

	public static function BONE_BLOCK() : BoneBlock{
		return BoneBlock::cast(BlockFactory::get(216, 0));
	}

	public static function BOOKSHELF() : Bookshelf{
		return Bookshelf::cast(BlockFactory::get(47, 0));
	}

	public static function BREWING_STAND() : BrewingStand{
		return BrewingStand::cast(BlockFactory::get(117, 0));
	}

	public static function BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 4));
	}

	public static function BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(108, 0));
	}

	public static function BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 6));
	}

	public static function BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(45, 0));
	}

	public static function BROWN_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 12));
	}

	public static function BROWN_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 12));
	}

	public static function BROWN_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 12));
	}

	public static function BROWN_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(232, 2));
	}

	public static function BROWN_MUSHROOM() : BrownMushroom{
		return BrownMushroom::cast(BlockFactory::get(39, 0));
	}

	public static function BROWN_MUSHROOM_BLOCK() : BrownMushroomBlock{
		return BrownMushroomBlock::cast(BlockFactory::get(99, 0));
	}

	public static function BROWN_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 12));
	}

	public static function BROWN_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 12));
	}

	public static function BROWN_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 12));
	}

	public static function BROWN_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 12));
	}

	public static function CACTUS() : Cactus{
		return Cactus::cast(BlockFactory::get(81, 0));
	}

	public static function CAKE() : Cake{
		return Cake::cast(BlockFactory::get(92, 0));
	}

	public static function CARROTS() : Carrot{
		return Carrot::cast(BlockFactory::get(141, 0));
	}

	public static function CHEST() : Chest{
		return Chest::cast(BlockFactory::get(54, 2));
	}

	public static function CHISELED_QUARTZ() : Solid{
		return Solid::cast(BlockFactory::get(155, 1));
	}

	public static function CHISELED_RED_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(179, 1));
	}

	public static function CHISELED_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(24, 1));
	}

	public static function CHISELED_STONE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(98, 3));
	}

	public static function CLAY() : Clay{
		return Clay::cast(BlockFactory::get(82, 0));
	}

	public static function COAL() : Coal{
		return Coal::cast(BlockFactory::get(173, 0));
	}

	public static function COAL_ORE() : CoalOre{
		return CoalOre::cast(BlockFactory::get(16, 0));
	}

	public static function COARSE_DIRT() : CoarseDirt{
		return CoarseDirt::cast(BlockFactory::get(3, 1));
	}

	public static function COBBLESTONE() : Solid{
		return Solid::cast(BlockFactory::get(4, 0));
	}

	public static function COBBLESTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 3));
	}

	public static function COBBLESTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(67, 0));
	}

	public static function COBBLESTONE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 0));
	}

	public static function COBWEB() : Cobweb{
		return Cobweb::cast(BlockFactory::get(30, 0));
	}

	public static function COCOA_POD() : CocoaBlock{
		return CocoaBlock::cast(BlockFactory::get(127, 0));
	}

	public static function CORNFLOWER() : Flower{
		return Flower::cast(BlockFactory::get(38, 9));
	}

	public static function CRACKED_STONE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(98, 2));
	}

	public static function CRAFTING_TABLE() : CraftingTable{
		return CraftingTable::cast(BlockFactory::get(58, 0));
	}

	public static function CUT_RED_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(179, 2));
	}

	public static function CUT_RED_SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(421, 4));
	}

	public static function CUT_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(24, 2));
	}

	public static function CUT_SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(421, 3));
	}

	public static function CYAN_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 9));
	}

	public static function CYAN_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 9));
	}

	public static function CYAN_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 9));
	}

	public static function CYAN_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(229, 2));
	}

	public static function CYAN_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 9));
	}

	public static function CYAN_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 9));
	}

	public static function CYAN_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 9));
	}

	public static function CYAN_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 9));
	}

	public static function DANDELION() : Flower{
		return Flower::cast(BlockFactory::get(37, 0));
	}

	public static function DARK_OAK_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(397, 0));
	}

	public static function DARK_OAK_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(197, 0));
	}

	public static function DARK_OAK_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 5));
	}

	public static function DARK_OAK_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(186, 0));
	}

	public static function DARK_OAK_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(161, 1));
	}

	public static function DARK_OAK_LOG() : Log{
		return Log::cast(BlockFactory::get(162, 1));
	}

	public static function DARK_OAK_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 5));
	}

	public static function DARK_OAK_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(407, 0));
	}

	public static function DARK_OAK_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 5));
	}

	public static function DARK_OAK_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(447, 0));
	}

	public static function DARK_OAK_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 5));
	}

	public static function DARK_OAK_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(164, 0));
	}

	public static function DARK_OAK_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(402, 0));
	}

	public static function DARK_OAK_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 5));
	}

	public static function DARK_PRISMARINE() : Solid{
		return Solid::cast(BlockFactory::get(168, 1));
	}

	public static function DARK_PRISMARINE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 3));
	}

	public static function DARK_PRISMARINE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(258, 0));
	}

	public static function DAYLIGHT_SENSOR() : DaylightSensor{
		return DaylightSensor::cast(BlockFactory::get(151, 0));
	}

	public static function DEAD_BUSH() : DeadBush{
		return DeadBush::cast(BlockFactory::get(32, 0));
	}

	public static function DETECTOR_RAIL() : DetectorRail{
		return DetectorRail::cast(BlockFactory::get(28, 0));
	}

	public static function DIAMOND() : Solid{
		return Solid::cast(BlockFactory::get(57, 0));
	}

	public static function DIAMOND_ORE() : DiamondOre{
		return DiamondOre::cast(BlockFactory::get(56, 0));
	}

	public static function DIORITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 3));
	}

	public static function DIORITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 4));
	}

	public static function DIORITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(425, 0));
	}

	public static function DIORITE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 3));
	}

	public static function DIRT() : Dirt{
		return Dirt::cast(BlockFactory::get(3, 0));
	}

	public static function DOUBLE_TALLGRASS() : DoubleTallGrass{
		return DoubleTallGrass::cast(BlockFactory::get(175, 2));
	}

	public static function DRAGON_EGG() : DragonEgg{
		return DragonEgg::cast(BlockFactory::get(122, 0));
	}

	public static function DRIED_KELP() : DriedKelp{
		return DriedKelp::cast(BlockFactory::get(394, 0));
	}

	public static function ELEMENT_ACTINIUM() : Element{
		return Element::cast(BlockFactory::get(355, 0));
	}

	public static function ELEMENT_ALUMINUM() : Element{
		return Element::cast(BlockFactory::get(279, 0));
	}

	public static function ELEMENT_AMERICIUM() : Element{
		return Element::cast(BlockFactory::get(361, 0));
	}

	public static function ELEMENT_ANTIMONY() : Element{
		return Element::cast(BlockFactory::get(317, 0));
	}

	public static function ELEMENT_ARGON() : Element{
		return Element::cast(BlockFactory::get(284, 0));
	}

	public static function ELEMENT_ARSENIC() : Element{
		return Element::cast(BlockFactory::get(299, 0));
	}

	public static function ELEMENT_ASTATINE() : Element{
		return Element::cast(BlockFactory::get(351, 0));
	}

	public static function ELEMENT_BARIUM() : Element{
		return Element::cast(BlockFactory::get(322, 0));
	}

	public static function ELEMENT_BERKELIUM() : Element{
		return Element::cast(BlockFactory::get(363, 0));
	}

	public static function ELEMENT_BERYLLIUM() : Element{
		return Element::cast(BlockFactory::get(270, 0));
	}

	public static function ELEMENT_BISMUTH() : Element{
		return Element::cast(BlockFactory::get(349, 0));
	}

	public static function ELEMENT_BOHRIUM() : Element{
		return Element::cast(BlockFactory::get(373, 0));
	}

	public static function ELEMENT_BORON() : Element{
		return Element::cast(BlockFactory::get(271, 0));
	}

	public static function ELEMENT_BROMINE() : Element{
		return Element::cast(BlockFactory::get(301, 0));
	}

	public static function ELEMENT_CADMIUM() : Element{
		return Element::cast(BlockFactory::get(314, 0));
	}

	public static function ELEMENT_CALCIUM() : Element{
		return Element::cast(BlockFactory::get(286, 0));
	}

	public static function ELEMENT_CALIFORNIUM() : Element{
		return Element::cast(BlockFactory::get(364, 0));
	}

	public static function ELEMENT_CARBON() : Element{
		return Element::cast(BlockFactory::get(272, 0));
	}

	public static function ELEMENT_CERIUM() : Element{
		return Element::cast(BlockFactory::get(324, 0));
	}

	public static function ELEMENT_CESIUM() : Element{
		return Element::cast(BlockFactory::get(321, 0));
	}

	public static function ELEMENT_CHLORINE() : Element{
		return Element::cast(BlockFactory::get(283, 0));
	}

	public static function ELEMENT_CHROMIUM() : Element{
		return Element::cast(BlockFactory::get(290, 0));
	}

	public static function ELEMENT_COBALT() : Element{
		return Element::cast(BlockFactory::get(293, 0));
	}

	public static function ELEMENT_COPERNICIUM() : Element{
		return Element::cast(BlockFactory::get(378, 0));
	}

	public static function ELEMENT_COPPER() : Element{
		return Element::cast(BlockFactory::get(295, 0));
	}

	public static function ELEMENT_CURIUM() : Element{
		return Element::cast(BlockFactory::get(362, 0));
	}

	public static function ELEMENT_DARMSTADTIUM() : Element{
		return Element::cast(BlockFactory::get(376, 0));
	}

	public static function ELEMENT_DUBNIUM() : Element{
		return Element::cast(BlockFactory::get(371, 0));
	}

	public static function ELEMENT_DYSPROSIUM() : Element{
		return Element::cast(BlockFactory::get(332, 0));
	}

	public static function ELEMENT_EINSTEINIUM() : Element{
		return Element::cast(BlockFactory::get(365, 0));
	}

	public static function ELEMENT_ERBIUM() : Element{
		return Element::cast(BlockFactory::get(334, 0));
	}

	public static function ELEMENT_EUROPIUM() : Element{
		return Element::cast(BlockFactory::get(329, 0));
	}

	public static function ELEMENT_FERMIUM() : Element{
		return Element::cast(BlockFactory::get(366, 0));
	}

	public static function ELEMENT_FLEROVIUM() : Element{
		return Element::cast(BlockFactory::get(380, 0));
	}

	public static function ELEMENT_FLUORINE() : Element{
		return Element::cast(BlockFactory::get(275, 0));
	}

	public static function ELEMENT_FRANCIUM() : Element{
		return Element::cast(BlockFactory::get(353, 0));
	}

	public static function ELEMENT_GADOLINIUM() : Element{
		return Element::cast(BlockFactory::get(330, 0));
	}

	public static function ELEMENT_GALLIUM() : Element{
		return Element::cast(BlockFactory::get(297, 0));
	}

	public static function ELEMENT_GERMANIUM() : Element{
		return Element::cast(BlockFactory::get(298, 0));
	}

	public static function ELEMENT_GOLD() : Element{
		return Element::cast(BlockFactory::get(345, 0));
	}

	public static function ELEMENT_HAFNIUM() : Element{
		return Element::cast(BlockFactory::get(338, 0));
	}

	public static function ELEMENT_HASSIUM() : Element{
		return Element::cast(BlockFactory::get(374, 0));
	}

	public static function ELEMENT_HELIUM() : Element{
		return Element::cast(BlockFactory::get(268, 0));
	}

	public static function ELEMENT_HOLMIUM() : Element{
		return Element::cast(BlockFactory::get(333, 0));
	}

	public static function ELEMENT_HYDROGEN() : Element{
		return Element::cast(BlockFactory::get(267, 0));
	}

	public static function ELEMENT_INDIUM() : Element{
		return Element::cast(BlockFactory::get(315, 0));
	}

	public static function ELEMENT_IODINE() : Element{
		return Element::cast(BlockFactory::get(319, 0));
	}

	public static function ELEMENT_IRIDIUM() : Element{
		return Element::cast(BlockFactory::get(343, 0));
	}

	public static function ELEMENT_IRON() : Element{
		return Element::cast(BlockFactory::get(292, 0));
	}

	public static function ELEMENT_KRYPTON() : Element{
		return Element::cast(BlockFactory::get(302, 0));
	}

	public static function ELEMENT_LANTHANUM() : Element{
		return Element::cast(BlockFactory::get(323, 0));
	}

	public static function ELEMENT_LAWRENCIUM() : Element{
		return Element::cast(BlockFactory::get(369, 0));
	}

	public static function ELEMENT_LEAD() : Element{
		return Element::cast(BlockFactory::get(348, 0));
	}

	public static function ELEMENT_LITHIUM() : Element{
		return Element::cast(BlockFactory::get(269, 0));
	}

	public static function ELEMENT_LIVERMORIUM() : Element{
		return Element::cast(BlockFactory::get(382, 0));
	}

	public static function ELEMENT_LUTETIUM() : Element{
		return Element::cast(BlockFactory::get(337, 0));
	}

	public static function ELEMENT_MAGNESIUM() : Element{
		return Element::cast(BlockFactory::get(278, 0));
	}

	public static function ELEMENT_MANGANESE() : Element{
		return Element::cast(BlockFactory::get(291, 0));
	}

	public static function ELEMENT_MEITNERIUM() : Element{
		return Element::cast(BlockFactory::get(375, 0));
	}

	public static function ELEMENT_MENDELEVIUM() : Element{
		return Element::cast(BlockFactory::get(367, 0));
	}

	public static function ELEMENT_MERCURY() : Element{
		return Element::cast(BlockFactory::get(346, 0));
	}

	public static function ELEMENT_MOLYBDENUM() : Element{
		return Element::cast(BlockFactory::get(308, 0));
	}

	public static function ELEMENT_MOSCOVIUM() : Element{
		return Element::cast(BlockFactory::get(381, 0));
	}

	public static function ELEMENT_NEODYMIUM() : Element{
		return Element::cast(BlockFactory::get(326, 0));
	}

	public static function ELEMENT_NEON() : Element{
		return Element::cast(BlockFactory::get(276, 0));
	}

	public static function ELEMENT_NEPTUNIUM() : Element{
		return Element::cast(BlockFactory::get(359, 0));
	}

	public static function ELEMENT_NICKEL() : Element{
		return Element::cast(BlockFactory::get(294, 0));
	}

	public static function ELEMENT_NIHONIUM() : Element{
		return Element::cast(BlockFactory::get(379, 0));
	}

	public static function ELEMENT_NIOBIUM() : Element{
		return Element::cast(BlockFactory::get(307, 0));
	}

	public static function ELEMENT_NITROGEN() : Element{
		return Element::cast(BlockFactory::get(273, 0));
	}

	public static function ELEMENT_NOBELIUM() : Element{
		return Element::cast(BlockFactory::get(368, 0));
	}

	public static function ELEMENT_OGANESSON() : Element{
		return Element::cast(BlockFactory::get(384, 0));
	}

	public static function ELEMENT_OSMIUM() : Element{
		return Element::cast(BlockFactory::get(342, 0));
	}

	public static function ELEMENT_OXYGEN() : Element{
		return Element::cast(BlockFactory::get(274, 0));
	}

	public static function ELEMENT_PALLADIUM() : Element{
		return Element::cast(BlockFactory::get(312, 0));
	}

	public static function ELEMENT_PHOSPHORUS() : Element{
		return Element::cast(BlockFactory::get(281, 0));
	}

	public static function ELEMENT_PLATINUM() : Element{
		return Element::cast(BlockFactory::get(344, 0));
	}

	public static function ELEMENT_PLUTONIUM() : Element{
		return Element::cast(BlockFactory::get(360, 0));
	}

	public static function ELEMENT_POLONIUM() : Element{
		return Element::cast(BlockFactory::get(350, 0));
	}

	public static function ELEMENT_POTASSIUM() : Element{
		return Element::cast(BlockFactory::get(285, 0));
	}

	public static function ELEMENT_PRASEODYMIUM() : Element{
		return Element::cast(BlockFactory::get(325, 0));
	}

	public static function ELEMENT_PROMETHIUM() : Element{
		return Element::cast(BlockFactory::get(327, 0));
	}

	public static function ELEMENT_PROTACTINIUM() : Element{
		return Element::cast(BlockFactory::get(357, 0));
	}

	public static function ELEMENT_RADIUM() : Element{
		return Element::cast(BlockFactory::get(354, 0));
	}

	public static function ELEMENT_RADON() : Element{
		return Element::cast(BlockFactory::get(352, 0));
	}

	public static function ELEMENT_RHENIUM() : Element{
		return Element::cast(BlockFactory::get(341, 0));
	}

	public static function ELEMENT_RHODIUM() : Element{
		return Element::cast(BlockFactory::get(311, 0));
	}

	public static function ELEMENT_ROENTGENIUM() : Element{
		return Element::cast(BlockFactory::get(377, 0));
	}

	public static function ELEMENT_RUBIDIUM() : Element{
		return Element::cast(BlockFactory::get(303, 0));
	}

	public static function ELEMENT_RUTHENIUM() : Element{
		return Element::cast(BlockFactory::get(310, 0));
	}

	public static function ELEMENT_RUTHERFORDIUM() : Element{
		return Element::cast(BlockFactory::get(370, 0));
	}

	public static function ELEMENT_SAMARIUM() : Element{
		return Element::cast(BlockFactory::get(328, 0));
	}

	public static function ELEMENT_SCANDIUM() : Element{
		return Element::cast(BlockFactory::get(287, 0));
	}

	public static function ELEMENT_SEABORGIUM() : Element{
		return Element::cast(BlockFactory::get(372, 0));
	}

	public static function ELEMENT_SELENIUM() : Element{
		return Element::cast(BlockFactory::get(300, 0));
	}

	public static function ELEMENT_SILICON() : Element{
		return Element::cast(BlockFactory::get(280, 0));
	}

	public static function ELEMENT_SILVER() : Element{
		return Element::cast(BlockFactory::get(313, 0));
	}

	public static function ELEMENT_SODIUM() : Element{
		return Element::cast(BlockFactory::get(277, 0));
	}

	public static function ELEMENT_STRONTIUM() : Element{
		return Element::cast(BlockFactory::get(304, 0));
	}

	public static function ELEMENT_SULFUR() : Element{
		return Element::cast(BlockFactory::get(282, 0));
	}

	public static function ELEMENT_TANTALUM() : Element{
		return Element::cast(BlockFactory::get(339, 0));
	}

	public static function ELEMENT_TECHNETIUM() : Element{
		return Element::cast(BlockFactory::get(309, 0));
	}

	public static function ELEMENT_TELLURIUM() : Element{
		return Element::cast(BlockFactory::get(318, 0));
	}

	public static function ELEMENT_TENNESSINE() : Element{
		return Element::cast(BlockFactory::get(383, 0));
	}

	public static function ELEMENT_TERBIUM() : Element{
		return Element::cast(BlockFactory::get(331, 0));
	}

	public static function ELEMENT_THALLIUM() : Element{
		return Element::cast(BlockFactory::get(347, 0));
	}

	public static function ELEMENT_THORIUM() : Element{
		return Element::cast(BlockFactory::get(356, 0));
	}

	public static function ELEMENT_THULIUM() : Element{
		return Element::cast(BlockFactory::get(335, 0));
	}

	public static function ELEMENT_TIN() : Element{
		return Element::cast(BlockFactory::get(316, 0));
	}

	public static function ELEMENT_TITANIUM() : Element{
		return Element::cast(BlockFactory::get(288, 0));
	}

	public static function ELEMENT_TUNGSTEN() : Element{
		return Element::cast(BlockFactory::get(340, 0));
	}

	public static function ELEMENT_URANIUM() : Element{
		return Element::cast(BlockFactory::get(358, 0));
	}

	public static function ELEMENT_VANADIUM() : Element{
		return Element::cast(BlockFactory::get(289, 0));
	}

	public static function ELEMENT_XENON() : Element{
		return Element::cast(BlockFactory::get(320, 0));
	}

	public static function ELEMENT_YTTERBIUM() : Element{
		return Element::cast(BlockFactory::get(336, 0));
	}

	public static function ELEMENT_YTTRIUM() : Element{
		return Element::cast(BlockFactory::get(305, 0));
	}

	public static function ELEMENT_ZERO() : Solid{
		return Solid::cast(BlockFactory::get(36, 0));
	}

	public static function ELEMENT_ZINC() : Element{
		return Element::cast(BlockFactory::get(296, 0));
	}

	public static function ELEMENT_ZIRCONIUM() : Element{
		return Element::cast(BlockFactory::get(306, 0));
	}

	public static function EMERALD() : Solid{
		return Solid::cast(BlockFactory::get(133, 0));
	}

	public static function EMERALD_ORE() : EmeraldOre{
		return EmeraldOre::cast(BlockFactory::get(129, 0));
	}

	public static function ENCHANTING_TABLE() : EnchantingTable{
		return EnchantingTable::cast(BlockFactory::get(116, 0));
	}

	public static function END_PORTAL_FRAME() : EndPortalFrame{
		return EndPortalFrame::cast(BlockFactory::get(120, 0));
	}

	public static function END_ROD() : EndRod{
		return EndRod::cast(BlockFactory::get(208, 0));
	}

	public static function END_STONE() : Solid{
		return Solid::cast(BlockFactory::get(121, 0));
	}

	public static function END_STONE_BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 0));
	}

	public static function END_STONE_BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(433, 0));
	}

	public static function END_STONE_BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 10));
	}

	public static function END_STONE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(206, 0));
	}

	public static function ENDER_CHEST() : EnderChest{
		return EnderChest::cast(BlockFactory::get(130, 2));
	}

	public static function FAKE_WOODEN_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 2));
	}

	public static function FARMLAND() : Farmland{
		return Farmland::cast(BlockFactory::get(60, 0));
	}

	public static function FERN() : TallGrass{
		return TallGrass::cast(BlockFactory::get(31, 2));
	}

	public static function FIRE() : Fire{
		return Fire::cast(BlockFactory::get(51, 0));
	}

	public static function FLOWER_POT() : FlowerPot{
		return FlowerPot::cast(BlockFactory::get(140, 0));
	}

	public static function FROSTED_ICE() : FrostedIce{
		return FrostedIce::cast(BlockFactory::get(207, 0));
	}

	public static function FURNACE() : Furnace{
		return Furnace::cast(BlockFactory::get(61, 2));
	}

	public static function GLASS() : Glass{
		return Glass::cast(BlockFactory::get(20, 0));
	}

	public static function GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(102, 0));
	}

	public static function GLOWING_OBSIDIAN() : GlowingObsidian{
		return GlowingObsidian::cast(BlockFactory::get(246, 0));
	}

	public static function GLOWSTONE() : Glowstone{
		return Glowstone::cast(BlockFactory::get(89, 0));
	}

	public static function GOLD() : Solid{
		return Solid::cast(BlockFactory::get(41, 0));
	}

	public static function GOLD_ORE() : Solid{
		return Solid::cast(BlockFactory::get(14, 0));
	}

	public static function GRANITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 1));
	}

	public static function GRANITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 6));
	}

	public static function GRANITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(424, 0));
	}

	public static function GRANITE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 2));
	}

	public static function GRASS() : Grass{
		return Grass::cast(BlockFactory::get(2, 0));
	}

	public static function GRASS_PATH() : GrassPath{
		return GrassPath::cast(BlockFactory::get(198, 0));
	}

	public static function GRAVEL() : Gravel{
		return Gravel::cast(BlockFactory::get(13, 0));
	}

	public static function GRAY_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 7));
	}

	public static function GRAY_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 7));
	}

	public static function GRAY_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 7));
	}

	public static function GRAY_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(227, 2));
	}

	public static function GRAY_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 7));
	}

	public static function GRAY_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 7));
	}

	public static function GRAY_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 7));
	}

	public static function GRAY_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 7));
	}

	public static function GREEN_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 13));
	}

	public static function GREEN_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 13));
	}

	public static function GREEN_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 13));
	}

	public static function GREEN_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(233, 2));
	}

	public static function GREEN_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 13));
	}

	public static function GREEN_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 13));
	}

	public static function GREEN_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 13));
	}

	public static function GREEN_TORCH() : Torch{
		return Torch::cast(BlockFactory::get(202, 13));
	}

	public static function GREEN_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 13));
	}

	public static function HARDENED_BLACK_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 15));
	}

	public static function HARDENED_BLACK_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 15));
	}

	public static function HARDENED_BLUE_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 11));
	}

	public static function HARDENED_BLUE_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 11));
	}

	public static function HARDENED_BROWN_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 12));
	}

	public static function HARDENED_BROWN_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 12));
	}

	public static function HARDENED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(172, 0));
	}

	public static function HARDENED_CYAN_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 9));
	}

	public static function HARDENED_CYAN_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 9));
	}

	public static function HARDENED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(253, 0));
	}

	public static function HARDENED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(190, 0));
	}

	public static function HARDENED_GRAY_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 7));
	}

	public static function HARDENED_GRAY_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 7));
	}

	public static function HARDENED_GREEN_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 13));
	}

	public static function HARDENED_GREEN_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 13));
	}

	public static function HARDENED_LIGHT_BLUE_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 3));
	}

	public static function HARDENED_LIGHT_BLUE_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 3));
	}

	public static function HARDENED_LIGHT_GRAY_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 8));
	}

	public static function HARDENED_LIGHT_GRAY_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 8));
	}

	public static function HARDENED_LIME_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 5));
	}

	public static function HARDENED_LIME_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 5));
	}

	public static function HARDENED_MAGENTA_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 2));
	}

	public static function HARDENED_MAGENTA_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 2));
	}

	public static function HARDENED_ORANGE_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 1));
	}

	public static function HARDENED_ORANGE_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 1));
	}

	public static function HARDENED_PINK_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 6));
	}

	public static function HARDENED_PINK_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 6));
	}

	public static function HARDENED_PURPLE_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 10));
	}

	public static function HARDENED_PURPLE_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 10));
	}

	public static function HARDENED_RED_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 14));
	}

	public static function HARDENED_RED_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 14));
	}

	public static function HARDENED_WHITE_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 0));
	}

	public static function HARDENED_WHITE_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 0));
	}

	public static function HARDENED_YELLOW_STAINED_GLASS() : HardenedGlass{
		return HardenedGlass::cast(BlockFactory::get(254, 4));
	}

	public static function HARDENED_YELLOW_STAINED_GLASS_PANE() : HardenedGlassPane{
		return HardenedGlassPane::cast(BlockFactory::get(191, 4));
	}

	public static function HAY_BALE() : HayBale{
		return HayBale::cast(BlockFactory::get(170, 0));
	}

	public static function HOPPER() : Hopper{
		return Hopper::cast(BlockFactory::get(154, 0));
	}

	public static function ICE() : Ice{
		return Ice::cast(BlockFactory::get(79, 0));
	}

	public static function INFESTED_CHISELED_STONE_BRICK() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 5));
	}

	public static function INFESTED_COBBLESTONE() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 1));
	}

	public static function INFESTED_CRACKED_STONE_BRICK() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 4));
	}

	public static function INFESTED_MOSSY_STONE_BRICK() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 3));
	}

	public static function INFESTED_STONE() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 0));
	}

	public static function INFESTED_STONE_BRICK() : InfestedStone{
		return InfestedStone::cast(BlockFactory::get(97, 2));
	}

	public static function INFO_UPDATE() : Solid{
		return Solid::cast(BlockFactory::get(248, 0));
	}

	public static function INFO_UPDATE2() : Solid{
		return Solid::cast(BlockFactory::get(249, 0));
	}

	public static function INVISIBLE_BEDROCK() : Transparent{
		return Transparent::cast(BlockFactory::get(95, 0));
	}

	public static function IRON() : Solid{
		return Solid::cast(BlockFactory::get(42, 0));
	}

	public static function IRON_BARS() : Thin{
		return Thin::cast(BlockFactory::get(101, 0));
	}

	public static function IRON_DOOR() : Door{
		return Door::cast(BlockFactory::get(71, 0));
	}

	public static function IRON_ORE() : Solid{
		return Solid::cast(BlockFactory::get(15, 0));
	}

	public static function IRON_TRAPDOOR() : Trapdoor{
		return Trapdoor::cast(BlockFactory::get(167, 0));
	}

	public static function ITEM_FRAME() : ItemFrame{
		return ItemFrame::cast(BlockFactory::get(199, 0));
	}

	public static function JUNGLE_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(398, 0));
	}

	public static function JUNGLE_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(195, 0));
	}

	public static function JUNGLE_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 3));
	}

	public static function JUNGLE_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(185, 0));
	}

	public static function JUNGLE_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(18, 3));
	}

	public static function JUNGLE_LOG() : Log{
		return Log::cast(BlockFactory::get(17, 3));
	}

	public static function JUNGLE_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 3));
	}

	public static function JUNGLE_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(408, 0));
	}

	public static function JUNGLE_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 3));
	}

	public static function JUNGLE_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(443, 0));
	}

	public static function JUNGLE_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 3));
	}

	public static function JUNGLE_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(136, 0));
	}

	public static function JUNGLE_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(403, 0));
	}

	public static function JUNGLE_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 3));
	}

	public static function LADDER() : Ladder{
		return Ladder::cast(BlockFactory::get(65, 2));
	}

	public static function LAPIS_LAZULI() : Solid{
		return Solid::cast(BlockFactory::get(22, 0));
	}

	public static function LAPIS_LAZULI_ORE() : LapisOre{
		return LapisOre::cast(BlockFactory::get(21, 0));
	}

	public static function LARGE_FERN() : DoubleTallGrass{
		return DoubleTallGrass::cast(BlockFactory::get(175, 3));
	}

	public static function LAVA() : Lava{
		return Lava::cast(BlockFactory::get(10, 0));
	}

	public static function LEGACY_STONECUTTER() : Solid{
		return Solid::cast(BlockFactory::get(245, 0));
	}

	public static function LEVER() : Lever{
		return Lever::cast(BlockFactory::get(69, 0));
	}

	public static function LIGHT_BLUE_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 3));
	}

	public static function LIGHT_BLUE_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 3));
	}

	public static function LIGHT_BLUE_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 3));
	}

	public static function LIGHT_BLUE_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(223, 2));
	}

	public static function LIGHT_BLUE_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 3));
	}

	public static function LIGHT_BLUE_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 3));
	}

	public static function LIGHT_BLUE_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 3));
	}

	public static function LIGHT_BLUE_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 3));
	}

	public static function LIGHT_GRAY_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 8));
	}

	public static function LIGHT_GRAY_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 8));
	}

	public static function LIGHT_GRAY_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 8));
	}

	public static function LIGHT_GRAY_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(228, 2));
	}

	public static function LIGHT_GRAY_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 8));
	}

	public static function LIGHT_GRAY_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 8));
	}

	public static function LIGHT_GRAY_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 8));
	}

	public static function LIGHT_GRAY_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 8));
	}

	public static function LILAC() : DoublePlant{
		return DoublePlant::cast(BlockFactory::get(175, 1));
	}

	public static function LILY_OF_THE_VALLEY() : Flower{
		return Flower::cast(BlockFactory::get(38, 10));
	}

	public static function LILY_PAD() : WaterLily{
		return WaterLily::cast(BlockFactory::get(111, 0));
	}

	public static function LIME_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 5));
	}

	public static function LIME_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 5));
	}

	public static function LIME_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 5));
	}

	public static function LIME_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(225, 2));
	}

	public static function LIME_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 5));
	}

	public static function LIME_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 5));
	}

	public static function LIME_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 5));
	}

	public static function LIME_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 5));
	}

	public static function LIT_PUMPKIN() : LitPumpkin{
		return LitPumpkin::cast(BlockFactory::get(91, 0));
	}

	public static function MAGENTA_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 2));
	}

	public static function MAGENTA_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 2));
	}

	public static function MAGENTA_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 2));
	}

	public static function MAGENTA_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(222, 2));
	}

	public static function MAGENTA_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 2));
	}

	public static function MAGENTA_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 2));
	}

	public static function MAGENTA_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 2));
	}

	public static function MAGENTA_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 2));
	}

	public static function MAGMA() : Magma{
		return Magma::cast(BlockFactory::get(213, 0));
	}

	public static function MELON() : Melon{
		return Melon::cast(BlockFactory::get(103, 0));
	}

	public static function MELON_STEM() : MelonStem{
		return MelonStem::cast(BlockFactory::get(105, 0));
	}

	public static function MOB_HEAD() : Skull{
		return Skull::cast(BlockFactory::get(144, 2));
	}

	public static function MONSTER_SPAWNER() : MonsterSpawner{
		return MonsterSpawner::cast(BlockFactory::get(52, 0));
	}

	public static function MOSSY_COBBLESTONE() : Solid{
		return Solid::cast(BlockFactory::get(48, 0));
	}

	public static function MOSSY_COBBLESTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 5));
	}

	public static function MOSSY_COBBLESTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(434, 0));
	}

	public static function MOSSY_COBBLESTONE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 1));
	}

	public static function MOSSY_STONE_BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(421, 0));
	}

	public static function MOSSY_STONE_BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(430, 0));
	}

	public static function MOSSY_STONE_BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 8));
	}

	public static function MOSSY_STONE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(98, 1));
	}

	public static function MYCELIUM() : Mycelium{
		return Mycelium::cast(BlockFactory::get(110, 0));
	}

	public static function NETHER_BRICK_FENCE() : Fence{
		return Fence::cast(BlockFactory::get(113, 0));
	}

	public static function NETHER_BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 7));
	}

	public static function NETHER_BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(114, 0));
	}

	public static function NETHER_BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 9));
	}

	public static function NETHER_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(112, 0));
	}

	public static function NETHER_PORTAL() : NetherPortal{
		return NetherPortal::cast(BlockFactory::get(90, 1));
	}

	public static function NETHER_QUARTZ_ORE() : NetherQuartzOre{
		return NetherQuartzOre::cast(BlockFactory::get(153, 0));
	}

	public static function NETHER_REACTOR_CORE() : NetherReactor{
		return NetherReactor::cast(BlockFactory::get(247, 0));
	}

	public static function NETHER_WART() : NetherWartPlant{
		return NetherWartPlant::cast(BlockFactory::get(115, 0));
	}

	public static function NETHER_WART_BLOCK() : Solid{
		return Solid::cast(BlockFactory::get(214, 0));
	}

	public static function NETHERRACK() : Netherrack{
		return Netherrack::cast(BlockFactory::get(87, 0));
	}

	public static function NOTE_BLOCK() : Note{
		return Note::cast(BlockFactory::get(25, 0));
	}

	public static function OAK_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(143, 0));
	}

	public static function OAK_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(64, 0));
	}

	public static function OAK_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 0));
	}

	public static function OAK_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(107, 0));
	}

	public static function OAK_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(18, 0));
	}

	public static function OAK_LOG() : Log{
		return Log::cast(BlockFactory::get(17, 0));
	}

	public static function OAK_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 0));
	}

	public static function OAK_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(72, 0));
	}

	public static function OAK_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 0));
	}

	public static function OAK_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(63, 0));
	}

	public static function OAK_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 0));
	}

	public static function OAK_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(53, 0));
	}

	public static function OAK_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(96, 0));
	}

	public static function OAK_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 0));
	}

	public static function OBSIDIAN() : Solid{
		return Solid::cast(BlockFactory::get(49, 0));
	}

	public static function ORANGE_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 1));
	}

	public static function ORANGE_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 1));
	}

	public static function ORANGE_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 1));
	}

	public static function ORANGE_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(221, 2));
	}

	public static function ORANGE_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 1));
	}

	public static function ORANGE_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 1));
	}

	public static function ORANGE_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 1));
	}

	public static function ORANGE_TULIP() : Flower{
		return Flower::cast(BlockFactory::get(38, 5));
	}

	public static function ORANGE_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 1));
	}

	public static function OXEYE_DAISY() : Flower{
		return Flower::cast(BlockFactory::get(38, 8));
	}

	public static function PACKED_ICE() : PackedIce{
		return PackedIce::cast(BlockFactory::get(174, 0));
	}

	public static function PEONY() : DoublePlant{
		return DoublePlant::cast(BlockFactory::get(175, 5));
	}

	public static function PINK_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 6));
	}

	public static function PINK_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 6));
	}

	public static function PINK_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 6));
	}

	public static function PINK_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(226, 2));
	}

	public static function PINK_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 6));
	}

	public static function PINK_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 6));
	}

	public static function PINK_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 6));
	}

	public static function PINK_TULIP() : Flower{
		return Flower::cast(BlockFactory::get(38, 7));
	}

	public static function PINK_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 6));
	}

	public static function PODZOL() : Podzol{
		return Podzol::cast(BlockFactory::get(243, 0));
	}

	public static function POLISHED_ANDESITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 6));
	}

	public static function POLISHED_ANDESITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 2));
	}

	public static function POLISHED_ANDESITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(429, 0));
	}

	public static function POLISHED_DIORITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 4));
	}

	public static function POLISHED_DIORITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 5));
	}

	public static function POLISHED_DIORITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(428, 0));
	}

	public static function POLISHED_GRANITE() : Solid{
		return Solid::cast(BlockFactory::get(1, 2));
	}

	public static function POLISHED_GRANITE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 7));
	}

	public static function POLISHED_GRANITE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(427, 0));
	}

	public static function POPPY() : Flower{
		return Flower::cast(BlockFactory::get(38, 0));
	}

	public static function POTATOES() : Potato{
		return Potato::cast(BlockFactory::get(142, 0));
	}

	public static function POWERED_RAIL() : PoweredRail{
		return PoweredRail::cast(BlockFactory::get(27, 0));
	}

	public static function PRISMARINE() : Solid{
		return Solid::cast(BlockFactory::get(168, 0));
	}

	public static function PRISMARINE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(168, 2));
	}

	public static function PRISMARINE_BRICKS_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 4));
	}

	public static function PRISMARINE_BRICKS_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(259, 0));
	}

	public static function PRISMARINE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 2));
	}

	public static function PRISMARINE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(257, 0));
	}

	public static function PRISMARINE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 11));
	}

	public static function PUMPKIN() : Pumpkin{
		return Pumpkin::cast(BlockFactory::get(86, 0));
	}

	public static function PUMPKIN_STEM() : PumpkinStem{
		return PumpkinStem::cast(BlockFactory::get(104, 0));
	}

	public static function PURPLE_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 10));
	}

	public static function PURPLE_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 10));
	}

	public static function PURPLE_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 10));
	}

	public static function PURPLE_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(219, 2));
	}

	public static function PURPLE_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 10));
	}

	public static function PURPLE_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 10));
	}

	public static function PURPLE_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 10));
	}

	public static function PURPLE_TORCH() : Torch{
		return Torch::cast(BlockFactory::get(204, 13));
	}

	public static function PURPLE_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 10));
	}

	public static function PURPUR() : Solid{
		return Solid::cast(BlockFactory::get(201, 0));
	}

	public static function PURPUR_PILLAR() : Solid{
		return Solid::cast(BlockFactory::get(201, 2));
	}

	public static function PURPUR_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 1));
	}

	public static function PURPUR_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(203, 0));
	}

	public static function QUARTZ() : Solid{
		return Solid::cast(BlockFactory::get(155, 0));
	}

	public static function QUARTZ_PILLAR() : Solid{
		return Solid::cast(BlockFactory::get(155, 2));
	}

	public static function QUARTZ_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 6));
	}

	public static function QUARTZ_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(156, 0));
	}

	public static function RAIL() : Rail{
		return Rail::cast(BlockFactory::get(66, 0));
	}

	public static function RED_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 14));
	}

	public static function RED_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 14));
	}

	public static function RED_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 14));
	}

	public static function RED_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(234, 2));
	}

	public static function RED_MUSHROOM() : RedMushroom{
		return RedMushroom::cast(BlockFactory::get(40, 0));
	}

	public static function RED_MUSHROOM_BLOCK() : RedMushroomBlock{
		return RedMushroomBlock::cast(BlockFactory::get(100, 0));
	}

	public static function RED_NETHER_BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 7));
	}

	public static function RED_NETHER_BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(439, 0));
	}

	public static function RED_NETHER_BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 13));
	}

	public static function RED_NETHER_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(215, 0));
	}

	public static function RED_SAND() : Sand{
		return Sand::cast(BlockFactory::get(12, 1));
	}

	public static function RED_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(179, 0));
	}

	public static function RED_SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 0));
	}

	public static function RED_SANDSTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(180, 0));
	}

	public static function RED_SANDSTONE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 12));
	}

	public static function RED_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 14));
	}

	public static function RED_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 14));
	}

	public static function RED_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 14));
	}

	public static function RED_TORCH() : Torch{
		return Torch::cast(BlockFactory::get(202, 5));
	}

	public static function RED_TULIP() : Flower{
		return Flower::cast(BlockFactory::get(38, 4));
	}

	public static function RED_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 14));
	}

	public static function REDSTONE() : Redstone{
		return Redstone::cast(BlockFactory::get(152, 0));
	}

	public static function REDSTONE_COMPARATOR() : RedstoneComparator{
		return RedstoneComparator::cast(BlockFactory::get(149, 0));
	}

	public static function REDSTONE_LAMP() : RedstoneLamp{
		return RedstoneLamp::cast(BlockFactory::get(123, 0));
	}

	public static function REDSTONE_ORE() : RedstoneOre{
		return RedstoneOre::cast(BlockFactory::get(73, 0));
	}

	public static function REDSTONE_REPEATER() : RedstoneRepeater{
		return RedstoneRepeater::cast(BlockFactory::get(93, 0));
	}

	public static function REDSTONE_TORCH() : RedstoneTorch{
		return RedstoneTorch::cast(BlockFactory::get(76, 5));
	}

	public static function REDSTONE_WIRE() : RedstoneWire{
		return RedstoneWire::cast(BlockFactory::get(55, 0));
	}

	public static function RESERVED6() : Reserved6{
		return Reserved6::cast(BlockFactory::get(255, 0));
	}

	public static function ROSE_BUSH() : DoublePlant{
		return DoublePlant::cast(BlockFactory::get(175, 4));
	}

	public static function SAND() : Sand{
		return Sand::cast(BlockFactory::get(12, 0));
	}

	public static function SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(24, 0));
	}

	public static function SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 1));
	}

	public static function SANDSTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(128, 0));
	}

	public static function SANDSTONE_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 5));
	}

	public static function SEA_LANTERN() : SeaLantern{
		return SeaLantern::cast(BlockFactory::get(169, 0));
	}

	public static function SEA_PICKLE() : SeaPickle{
		return SeaPickle::cast(BlockFactory::get(411, 0));
	}

	public static function SLIGHTLY_DAMAGED_ANVIL() : Anvil{
		return Anvil::cast(BlockFactory::get(145, 4));
	}

	public static function SMOOTH_QUARTZ() : Solid{
		return Solid::cast(BlockFactory::get(155, 3));
	}

	public static function SMOOTH_QUARTZ_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(421, 1));
	}

	public static function SMOOTH_QUARTZ_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(440, 0));
	}

	public static function SMOOTH_RED_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(179, 3));
	}

	public static function SMOOTH_RED_SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(417, 1));
	}

	public static function SMOOTH_RED_SANDSTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(431, 0));
	}

	public static function SMOOTH_SANDSTONE() : Solid{
		return Solid::cast(BlockFactory::get(24, 3));
	}

	public static function SMOOTH_SANDSTONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(182, 6));
	}

	public static function SMOOTH_SANDSTONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(432, 0));
	}

	public static function SMOOTH_STONE() : Solid{
		return Solid::cast(BlockFactory::get(438, 0));
	}

	public static function SMOOTH_STONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 0));
	}

	public static function SNOW() : Snow{
		return Snow::cast(BlockFactory::get(80, 0));
	}

	public static function SNOW_LAYER() : SnowLayer{
		return SnowLayer::cast(BlockFactory::get(78, 0));
	}

	public static function SOUL_SAND() : SoulSand{
		return SoulSand::cast(BlockFactory::get(88, 0));
	}

	public static function SPONGE() : Sponge{
		return Sponge::cast(BlockFactory::get(19, 0));
	}

	public static function SPRUCE_BUTTON() : WoodenButton{
		return WoodenButton::cast(BlockFactory::get(399, 0));
	}

	public static function SPRUCE_DOOR() : WoodenDoor{
		return WoodenDoor::cast(BlockFactory::get(193, 0));
	}

	public static function SPRUCE_FENCE() : WoodenFence{
		return WoodenFence::cast(BlockFactory::get(85, 1));
	}

	public static function SPRUCE_FENCE_GATE() : FenceGate{
		return FenceGate::cast(BlockFactory::get(183, 0));
	}

	public static function SPRUCE_LEAVES() : Leaves{
		return Leaves::cast(BlockFactory::get(18, 1));
	}

	public static function SPRUCE_LOG() : Log{
		return Log::cast(BlockFactory::get(17, 1));
	}

	public static function SPRUCE_PLANKS() : Planks{
		return Planks::cast(BlockFactory::get(5, 1));
	}

	public static function SPRUCE_PRESSURE_PLATE() : WoodenPressurePlate{
		return WoodenPressurePlate::cast(BlockFactory::get(409, 0));
	}

	public static function SPRUCE_SAPLING() : Sapling{
		return Sapling::cast(BlockFactory::get(6, 1));
	}

	public static function SPRUCE_SIGN() : Sign{
		return Sign::cast(BlockFactory::get(436, 0));
	}

	public static function SPRUCE_SLAB() : WoodenSlab{
		return WoodenSlab::cast(BlockFactory::get(158, 1));
	}

	public static function SPRUCE_STAIRS() : WoodenStairs{
		return WoodenStairs::cast(BlockFactory::get(134, 0));
	}

	public static function SPRUCE_TRAPDOOR() : WoodenTrapdoor{
		return WoodenTrapdoor::cast(BlockFactory::get(404, 0));
	}

	public static function SPRUCE_WOOD() : Wood{
		return Wood::cast(BlockFactory::get(467, 1));
	}

	public static function STONE() : Solid{
		return Solid::cast(BlockFactory::get(1, 0));
	}

	public static function STONE_BRICK_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(44, 5));
	}

	public static function STONE_BRICK_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(109, 0));
	}

	public static function STONE_BRICK_WALL() : Wall{
		return Wall::cast(BlockFactory::get(139, 7));
	}

	public static function STONE_BRICKS() : Solid{
		return Solid::cast(BlockFactory::get(98, 0));
	}

	public static function STONE_BUTTON() : StoneButton{
		return StoneButton::cast(BlockFactory::get(77, 0));
	}

	public static function STONE_PRESSURE_PLATE() : StonePressurePlate{
		return StonePressurePlate::cast(BlockFactory::get(70, 0));
	}

	public static function STONE_SLAB() : Slab{
		return Slab::cast(BlockFactory::get(421, 2));
	}

	public static function STONE_STAIRS() : Stair{
		return Stair::cast(BlockFactory::get(435, 0));
	}

	public static function SUGARCANE() : Sugarcane{
		return Sugarcane::cast(BlockFactory::get(83, 0));
	}

	public static function SUNFLOWER() : DoublePlant{
		return DoublePlant::cast(BlockFactory::get(175, 0));
	}

	public static function TALL_GRASS() : TallGrass{
		return TallGrass::cast(BlockFactory::get(31, 1));
	}

	public static function TNT() : TNT{
		return TNT::cast(BlockFactory::get(46, 0));
	}

	public static function TORCH() : Torch{
		return Torch::cast(BlockFactory::get(50, 5));
	}

	public static function TRAPPED_CHEST() : TrappedChest{
		return TrappedChest::cast(BlockFactory::get(146, 2));
	}

	public static function TRIPWIRE() : Tripwire{
		return Tripwire::cast(BlockFactory::get(132, 0));
	}

	public static function TRIPWIRE_HOOK() : TripwireHook{
		return TripwireHook::cast(BlockFactory::get(131, 0));
	}

	public static function UNDERWATER_TORCH() : UnderwaterTorch{
		return UnderwaterTorch::cast(BlockFactory::get(239, 5));
	}

	public static function VERY_DAMAGED_ANVIL() : Anvil{
		return Anvil::cast(BlockFactory::get(145, 8));
	}

	public static function VINES() : Vine{
		return Vine::cast(BlockFactory::get(106, 0));
	}

	public static function WATER() : Water{
		return Water::cast(BlockFactory::get(8, 0));
	}

	public static function WEIGHTED_PRESSURE_PLATE_HEAVY() : WeightedPressurePlateHeavy{
		return WeightedPressurePlateHeavy::cast(BlockFactory::get(148, 0));
	}

	public static function WEIGHTED_PRESSURE_PLATE_LIGHT() : WeightedPressurePlateLight{
		return WeightedPressurePlateLight::cast(BlockFactory::get(147, 0));
	}

	public static function WHEAT() : Wheat{
		return Wheat::cast(BlockFactory::get(59, 0));
	}

	public static function WHITE_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 0));
	}

	public static function WHITE_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 0));
	}

	public static function WHITE_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 0));
	}

	public static function WHITE_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(220, 2));
	}

	public static function WHITE_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 0));
	}

	public static function WHITE_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 0));
	}

	public static function WHITE_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 0));
	}

	public static function WHITE_TULIP() : Flower{
		return Flower::cast(BlockFactory::get(38, 6));
	}

	public static function WHITE_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 0));
	}

	public static function YELLOW_CARPET() : Carpet{
		return Carpet::cast(BlockFactory::get(171, 4));
	}

	public static function YELLOW_CONCRETE() : Concrete{
		return Concrete::cast(BlockFactory::get(236, 4));
	}

	public static function YELLOW_CONCRETE_POWDER() : ConcretePowder{
		return ConcretePowder::cast(BlockFactory::get(237, 4));
	}

	public static function YELLOW_GLAZED_TERRACOTTA() : GlazedTerracotta{
		return GlazedTerracotta::cast(BlockFactory::get(224, 2));
	}

	public static function YELLOW_STAINED_CLAY() : HardenedClay{
		return HardenedClay::cast(BlockFactory::get(159, 4));
	}

	public static function YELLOW_STAINED_GLASS() : Glass{
		return Glass::cast(BlockFactory::get(241, 4));
	}

	public static function YELLOW_STAINED_GLASS_PANE() : GlassPane{
		return GlassPane::cast(BlockFactory::get(160, 4));
	}

	public static function YELLOW_WOOL() : Wool{
		return Wool::cast(BlockFactory::get(35, 4));
	}

	//endregion
}
