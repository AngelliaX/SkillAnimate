<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\AnimateController;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;

class spawnBlockRepeatingTask extends Task
{
    public $sa;
    public $xyz;
    /** @var Player */
    public $player;
    /** @var array
     * return [id,meta]
     */
    private $blockData;
    /** @var string|null */
    private $sound;
    /** @var int */
    private $endTime;
    private $timeCheck = 0;
    private $distanceForPersonalTask;
    private $skillName;

    public function __construct(SkillAnimate $sa, array $xyz, Player $player, array $blockData, int $endTime, string $skillName, string $sound = null, ?float $distance = 0.5)
    {
        $this->sa = $sa;
        $this->xyz = $xyz;
        $this->player = $player;
        $this->blockData = $blockData;
        $this->sound = $sound;
        $this->endTime = $endTime;
        $this->distanceForPersonalTask = $distance;
        $this->skillName = $skillName;

        if ($this->sound != null) {
            $sound = new PlaySoundPacket();
            $x = $player->getX();
            $z = $player->getZ();
            $sound->x = $x;
            $sound->y = $player->getY();
            $sound->z = $z;
            $sound->volume = 100;
            $sound->pitch = 1;
            $sound->soundName = $this->sound;
            if($player->getLevel() == null){
                $this->getHandler()->cancel();
            }
            foreach ($player->getLevel()->getPlayers() as $player) {
                if (abs($player->getX()) <= $x + 100 && abs($player->getZ()) <= $z + 100) {
                    SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
                }
            }

        }
    }


    public function onRun($tick)
    {
        /** vd endtime = 400 = 20s,tick = 1, => cu moi 1 tick timecheck se cong them 1,400 tick se du */
        if ($this->timeCheck++ > $this->endTime) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $player = $this->player;
        $direc = $player->getDirection();
        $x = $this->xyz[0];
        $y = $this->xyz[1];
        $z = $this->xyz[2];
        $pos = $this->posCorrection($direc, $player, $x, $y, $z);
        $level = $player->getLevel();
        /** check if the player disconnect */
        if ($level == null) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        $task = new blockPersonalTask($this->sa, $this->player, $pos, $level, $this->skillName, 1, $this->distanceForPersonalTask);
        $this->sa->getScheduler()->scheduleRepeatingTask($task, 1);
        if ($level->getBlock($pos)->getId() != 0) {
            return;
        }
        $level->setBlock($pos, Block::get($this->blockData[0], $this->blockData[1]), false, false);
        $this->sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($pos, $level, $this->blockData), 5);
    }

    public function PosCorrection(int $direc, Vector3 $pos, int $x, int $y, int $z): Vector3
    {
        if ($direc == 0) {
            return $pos->add($x, $y, $z);
        } else if ($direc == 1) {
            return $pos->add(-$z, $y, $x);
        } else if ($direc == 2) {
            return $pos->add(-$x, $y, -$z);
        } else {
            return $pos->add($z, $y, -$x);
        }
    }
}