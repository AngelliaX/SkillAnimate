<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use Tungsten\SkillAnimate\Events\SkillCollideEvent;
use Tungsten\SkillAnimate\SkillAnimate;

class SkillCollideListener implements Listener
{
    public $sa;

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function onSkillCollide(SkillCollideEvent $ev)
    {

        $player = $ev->getPlayer();
        $skillOwner = $ev->getSkillOwner();
        $direc = $player->getDirection();

        if ($direc == 0) {
            $xDirec = -1;
            $zDirec = 0;
        } else if ($direc == 1) {
            $xDirec = 0;
            $zDirec = -1;
        } else if ($direc == 2) {
            $xDirec = +1;
            $zDirec = 0;
        } else {
            $xDirec = 0;
            $zDirec = +1;
        }

        $config = $this->sa->database->getConfig($skillOwner);
        if ($ev->getSkillName() == "GaraProtection") {
            $player->knockBack($player, 999, $xDirec, $zDirec, 0.4);
        } else if ($ev->getSkillName() == "GroundBadabum") {
            if (!is_null($config->getNested("GroundBadabum"))) {
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $config->getNested("GroundBadabum.damage"));
            } else {
                $config = $this->sa->skillData->getNested("GroundBadabum.damage");
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $config);
            }
            $player->attack($ev);
            $player->knockBack($player, 999, $xDirec, $zDirec, 0.2);
        } else if ($ev->getSkillName() == "SoulHand") {
            if (!is_null($config->getNested("SoulHand"))) {
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $config->getNested("SoulHand.damage"));
            } else {
                $config = $this->sa->skillData->getNested("SoulHand.damage");
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $config);
            }
            $player->attack($ev);
        } else if ($ev->getSkillName() == "WispsSpawner" or $ev->getSkillName() == "WispsSpawner.small") {
            if ($ev->getSpawnTask() != null) {
                var_dump("removed");
                $this->sa->getScheduler()->cancelTask($ev->getPersonalTask()->getTaskId());
                $this->sa->getScheduler()->cancelTask($ev->getSpawnTask()->getTaskId());
                $this->spawnParticle($player, Particle::TYPE_HUGE_EXPLODE);
                $this->spawnParticle($player, Particle::TYPE_HUGE_EXPLODE);
            }
            $this->playMusic($player, "random.explode");
            if (!is_null($config->getNested("WispsSpawner"))) {
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $config->getNested($ev->getSkillName().".damage"));
            } else {
                $config = $this->sa->skillData->getNested($ev->getSkillName().".damage");
                var_dump($config);
                $ev = new EntityDamageByEntityEvent($skillOwner, $player, EntityDamageEvent::CAUSE_CONTACT, $config);

            }
            $player->attack($ev);
        }else if($ev->getSkillName() == "StickyFluid"){
            if($player->getName() == $skillOwner->getName()){
                if(!$player->hasEffect(16) or !$player->hasEffect(1) or !$player->hasEffect(10)){
                    $player->addEffect(new EffectInstance(Effect::getEffect(16),60,1));
                    $player->addEffect(new EffectInstance(Effect::getEffect(1),60,1));
                    $player->addEffect(new EffectInstance(Effect::getEffect(10),60,2));
                }
            }else{
                if(!$player->hasEffect(2) or !$player->hasEffect(9)){
                    $player->addEffect(new EffectInstance(Effect::getEffect(2),20,4,false));
                    $player->addEffect(new EffectInstance(Effect::getEffect(9),20,1,false));
                }
            }
        }else if($ev->getSkillName() == "ChasingFluid"){
            $this->spawnParticle($player, Particle::TYPE_HUGE_EXPLODE);
            $this->spawnParticle($player, Particle::TYPE_HUGE_EXPLODE);
            $this->playMusic($player, "random.explode");
        }
    }

    public function spawnParticle(Player $player, int $id)
    {
        $player->getLevel()->addParticle(new GenericParticle($player, $id, 0));
    }

    public function playMusic(Player $player, string $soundName)
    {
        $sound = new PlaySoundPacket();
        $x = $player->x;
        $z = $player->z;
        $sound->x = $x;
        $sound->y = $player->y;
        $sound->z = $z;
        $sound->volume = 100;
        $sound->pitch = 1;
        $sound->soundName = $soundName;
        foreach ($player->getLevel()->getPlayers() as $player) {
            if (abs($player->getX()) <= $x + 100 && abs($player->getZ()) <= $z + 100) {
                SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
            }
        }
    }
}