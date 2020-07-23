<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\Color;
use pocketmine\utils\Config;
use Tungsten\SkillAnimate\Events\ChakraGenerateEvent;
use Tungsten\SkillAnimate\Events\SkillCollideEvent;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
use Tungsten\SkillAnimate\SkillAnimate;

use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;

use Tungsten\SkillAnimate\SkillContainer\GaraProtection;
use Tungsten\SkillAnimate\SkillContainer\GroundBadabum;
use Tungsten\SkillAnimate\SkillContainer\SoulHand;
use Tungsten\SkillAnimate\SkillContainer\WispsSpawner;

class ChakraGenerateListener implements Listener
{
    public $sa;
    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }
    // Call by playerjoinevent
    public function onGenerateChakra(ChakraGenerateEvent $ev){
        $player = $ev->getPlayer();
        $this->sa->database->addConfig($player);
    }
    public function playMusic(Player $player, string $soundName)
    {
        $sound = new PlaySoundPacket();
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 0.5;
        $sound->pitch = 1;
        $sound->soundName = $soundName;
        SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
    }
}