
declare(strict_types=1);

namespace pocketmine\event\weather;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class LightningStrikeEvent extends Event implements Cancellable {
	use CancellableTrait;

	public function __construct(
		private World $world,
		private Vector3 $position
	){}

	public function getWorld() : World{
		return $this->world;
	}

	public function getPosition() : Vector3{
		return $this->position;
	}
}