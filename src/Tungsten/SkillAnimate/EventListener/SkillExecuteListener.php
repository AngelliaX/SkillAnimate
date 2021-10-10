<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
use Tungsten\SkillAnimate\RepeatingTask\coolDownTask;
use Tungsten\SkillAnimate\SkillAnimate;
use Tungsten\SkillAnimate\SkillContainer\ChasingFluid;
use Tungsten\SkillAnimate\SkillContainer\GaraProtection;
use Tungsten\SkillAnimate\SkillContainer\GroundBadabum;
use Tungsten\SkillAnimate\SkillContainer\SoulHand;
use Tungsten\SkillAnimate\SkillContainer\StickyFluid;
use Tungsten\SkillAnimate\SkillContainer\WispsSpawner;

class SkillExecuteListener implements Listener
{
    public $sa;
    public $coolDown = [];

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function onSkillExecute(SkillExecuteEvent $ev)
    {
        $skillName = $ev->getSkillName();
        $player = $ev->getPlayer();

        $isOnSafeArea = $this->checkIsOnSafeArea($player);
        if ($isOnSafeArea) {
            $player->sendPopup("§cBạn đang trong khu vực an toàn");
            $this->playMusic($player, "mob.elderguardian.curse");
            return;
        }
        $chasingFluidCheck = "lol";
        if ($skillName == "ChasingFluid") {
            $chasingFluidCheck = $this->checkChasingFluid($player);
            if (!$chasingFluidCheck instanceof Player) {
                $this->playMusic($player, "mob.elderguardian.curse");
                $player->sendPopup("§cChasingFluid:§f khong thay muc tieu");
                return;
            } else {
                $isOnSafeArea = $this->checkIsOnSafeArea($player);
                if ($isOnSafeArea) {
                    $player->sendPopup("§cMục tiêu đang trong khu vực an toàn");
                    $this->playMusic($player, "mob.elderguardian.curse");
                    return;
                }
            }
        }


        $canExecute = $this->checkCoolDown($player, $skillName);
        if (!$canExecute) return;

        $config = $this->sa->database->getConfig($player);
        $chakra = $config->getNested("Chakra");
        $requiredChakra = $this->sa->skillData->getNested($skillName)["chakra"];

        if ($chakra < $requiredChakra) {
            $this->playMusic($player, "mob.elderguardian.curse");
            return;
        }

        if ($skillName == "GaraProtection") {
            $config->setNested("Chakra", $config->getNested("Chakra") - $requiredChakra);
            new GaraProtection($this->sa, $player);
            if (!is_null($config = $config->getNested("GaraProtection"))) {
                $this->addEffect($player, 10, $config["destroyTime"], $config["effect"]);
                $this->addEffect($player, 11, $config["destroyTime"], $config["effect"]);
                return;
            }
            $config = $this->sa->skillData->getNested("GaraProtection");
            $this->addEffect($player, 10, $config["destroyTime"], $config["effect"]);
            $this->addEffect($player, 11, $config["destroyTime"], $config["effect"]);
        } else if ($skillName == "GroundBadabum") {
            $config->setNested("Chakra", $config->getNested("Chakra") - $requiredChakra);
            new GroundBadabum($this->sa, $player);
        } else if ($skillName == "SoulHand") {
            $config->setNested("Chakra", $config->getNested("Chakra") - $requiredChakra);
            $task = new SoulHand($this->sa, $player);
            $this->sa->getScheduler()->scheduleRepeatingTask($task, 1);
            if (!is_null($config = $config->getNested("SoulHand"))) {
                $this->addEffect($player, 1, $config["endTime"], $config["effect"]);
                $this->addEffect($player, 11, $config["endTime"], $config["effect"]);
                return;
            }
            $config = $this->sa->skillData->getNested("SoulHand");
            $this->addEffect($player, 1, $config["endTime"], $config["effect"]);
            $this->addEffect($player, 11, $config["endTime"], $config["effect"]);
        } else if ($skillName == "WispsSpawner") {
            new WispsSpawner($this->sa, $player);
            $config->setNested("Chakra", $config->getNested("Chakra") - $requiredChakra);
        } else if ($skillName == "StickyFluid") {
            new StickyFluid($this->sa, $player);
            $config->setNested("Chakra", $config->getNested("Chakra") - $requiredChakra);
        } else if ($skillName == "ChasingFluid") {
            $task = new ChasingFluid($this->sa, $player, $chasingFluidCheck);
            $this->sa->getScheduler()->scheduleRepeatingTask($task, 1);
        }
    }

    private function checkIsOnSafeArea(Player $player): bool
    {
        $config = $this->sa->safeArea->getNested($player->getLevel()->getName());
        if (is_array($config)) {
            foreach ($config as $value) {
                if ($this->isInside($value["x"][0], $value["x"][1], $value["y"][0], $value["y"][1], $value["z"][0], $value["z"][1], $player)) {
                    return true;
                }
            }
        }
        return false;

    }

    private function isInside(float $minX, float $maxX, float $minY, float $maxY, float $minZ, float $maxZ, Vector3 $vector)
    {
        if ($vector->x < $minX or $vector->x > $maxX) {
            return false;
        }
        if ($vector->y < $minY or $vector->y > $maxY) {
            return false;
        }

        return $vector->z > $minZ and $vector->z < $maxZ;
    }

    private function checkChasingFluid(Player $player): ?Player
    {
        $closest = null; //null or player object
        $lastSquare = 900; //this is also the range,30 block
        foreach ($player->getLevel()->getPlayers() as $target) { // for every player in the sender's world
            if ($target->getName() != $player->getName()) {
                $square = $player->distanceSquared($target);
                if ($lastSquare >= $square) {
                    $closest = $target;
                    $lastSquare = $square;
                }
            }
        }
        return $closest;
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

    private function checkCoolDown(Player $player, string $skillName): bool
    {
        if (array_key_exists($player->getName() . "_" . $skillName, $this->coolDown)) {
            $player->sendPopup("§c$skillName: §f" . $this->coolDown[$player->getName() . "_" . $skillName]->getTimeLeft() . "s");
            $this->playMusic($player, "mob.snowgolem.hurt");
            return false;
        } else {
            $coolDown = $this->sa->skillData->getNested($skillName)["cooldown"];
            $this->playMusic($player, "random.totem");
            $this->sa->getScheduler()->scheduleRepeatingTask(new coolDownTask($this, $player, $skillName, $coolDown), 1);
            return true;
        }
    }

    private function addEffect(Player $player, int $id, int $endTime, int $level)
    {
        $effect = Effect::getEffect($id);
        $effect = new EffectInstance($effect, $endTime, $level);
        $player->addEffect($effect);
    }
}