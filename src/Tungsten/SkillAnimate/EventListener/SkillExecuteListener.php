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
use Tungsten\SkillAnimate\SkillContainer\StickyFluid;
use Tungsten\SkillAnimate\SkillContainer\WispsSpawner;

class SkillExecuteListener implements Listener
{
    public $sa;
    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }
    public function onSkillExecute(SkillExecuteEvent $ev){
        $player = $ev->getPlayer();
        $skillName = $ev->getSkillName();
        $config = $this->sa->database->getConfig($player);
        $chakra = $config->getNested("Chakra");
        $requiredChakra = $this->sa->skillData->getNested($skillName)["chakra"];

        if($chakra < $requiredChakra){
            $this->playMusic($player,"mob.elderguardian.curse");
            return;
        }

        if($skillName == "GaraProtection"){
            $config->setNested("Chakra",$config->getNested("Chakra") - $requiredChakra);
            new GaraProtection($this->sa,$player);
            if(!is_null($config = $config->getNested("GaraProtection"))){
                $this->addEffect($player,10,$config["destroyTime"],$config["effect"]);
                $this->addEffect($player,11,$config["destroyTime"],$config["effect"]);
                return;
            }
            $config = $this->sa->skillData->getNested("GaraProtection");
            $this->addEffect($player,10,$config["destroyTime"],$config["effect"]);
            $this->addEffect($player,11,$config["destroyTime"],$config["effect"]);
        }else if ($skillName == "GroundBadabum"){
            $config->setNested("Chakra",$config->getNested("Chakra") - $requiredChakra);
            new GroundBadabum($this->sa,$player);
        }else if ($skillName == "SoulHand"){
            $config->setNested("Chakra",$config->getNested("Chakra") - $requiredChakra);
            $task = new SoulHand($this->sa,$player);
            $this->sa->getScheduler()->scheduleRepeatingTask($task,1);
            if(!is_null($config = $config->getNested("SoulHand"))){
                $this->addEffect($player,1,$config["endTime"],$config["effect"]);
                $this->addEffect($player,11,$config["endTime"],$config["effect"]);
                return;
            }
            $config = $this->sa->skillData->getNested("SoulHand");
            $this->addEffect($player,1,$config["endTime"],$config["effect"]);
            $this->addEffect($player,11,$config["endTime"],$config["effect"]);
        }else if($skillName == "WispsSpawner"){
            new WispsSpawner($this->sa,$player);
            $config->setNested("Chakra",$config->getNested("Chakra") - $requiredChakra);
        }else if($skillName == "StickyFluid"){
            var_dump("call skill execute");
            new StickyFluid($this->sa,$player);
        }
    }
    private function addEffect(Player $player,int $id,int $endTime,int $level){
        $effect = Effect::getEffect($id);
        $effect = new EffectInstance($effect,$endTime,$level);
        $player->addEffect($effect);
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