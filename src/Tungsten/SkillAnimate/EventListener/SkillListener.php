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

class SkillListener implements Listener
{
    public $sa;
    public $requiredChakra =
        [
            "GaraProtection" => 30,
            "GroundBadabum" => 40,
            "SoulHand" => 50,
        ];
    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }
    // Call by playerjoinevent
    public function onGenerateChakra(ChakraGenerateEvent $ev){
        $player = $ev->getPlayer();
        $this->sa->database->addConfig($player);
    }
    public function onSkillExecute(SkillExecuteEvent $ev){
        $player = $ev->getPlayer();
        $skillName = $ev->getSkillName();
        $config = $this->sa->database->getConfig($player);
        $chakra = $config->getNested("Chakra");
        $requiredChakra = $this->requiredChakra[$skillName];

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

        }


    }
    private function addEffect(Player $player,int $id,int $endTime,int $level){
        $effect = Effect::getEffect($id);
        $effect = new EffectInstance($effect,$endTime,$level);
        $player->addEffect($effect);
    }
    public function onSkillCollide(SkillCollideEvent $ev){

        $player = $ev->getPlayer();
        $skillOwner = $ev->getSkillOwner();
        $direc = $player->getDirection();

        if($direc == 0){
            $xDirec = -1;
            $zDirec = 0;
        }else if($direc == 1){
            $xDirec = 0;
            $zDirec = -1;
        }else if($direc == 2){
            $xDirec = +1;
            $zDirec = 0;
        }else{
            $xDirec = 0;
            $zDirec = +1;
        }

        $config = $this->sa->database->getConfig($skillOwner);
        if($ev->getSkillName() == "GaraProtection") {
            $player->knockBack($player, 999, $xDirec, $zDirec, 0.4);
        }else if($ev->getSkillName() == "GroundBadabum"){
            if(!is_null($config->getNested("GroundBadabum"))){
                $ev = new EntityDamageByEntityEvent($skillOwner,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$config->getNested("GroundBadabum.damage"));
            }else{
                $config = $this->sa->skillData->getNested("GroundBadabum.damage");
                $ev = new EntityDamageByEntityEvent($skillOwner,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$config);
            }
            $player->attack($ev);
            $player->knockBack($player,999,$xDirec,$zDirec,0.2);
        }else  if($ev->getSkillName() == "SoulHand"){
            if(!is_null($config->getNested("SoulHand"))){
                $ev = new EntityDamageByEntityEvent($skillOwner,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$config->getNested("SoulHand.damage"));
            }else{
                $config = $this->sa->skillData->getNested("SoulHand.damage");
                $ev = new EntityDamageByEntityEvent($skillOwner,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$config);
            }
            $player->attack($ev);
        }
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