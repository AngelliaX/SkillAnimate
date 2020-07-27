<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\AnimateController;

use pocketmine\level\particle\GenericParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;

class spawnParticleRepeatingTask extends Task
{
    public $sa;
    public $xyz;
    /** @var Player */
    public $player;
    /** @var array
     * return [id,meta]
     */
    private $particleId;
    /** @var string|null */
    private $sound;
    /** @var int */
    private $endTime;
    private $timeCheck = 0;
    private $distanceForPersonalTask;
    private $skillName;
    private $followPlayer;
    /** this var is for the purpose that you dont want the particle to follow the player */
    private $constantPos;
    private $rgb;
    private $howManyPar;
    private $skipY;
    private $isTriggered = false;

    public function __construct(SkillAnimate $sa, array $xyz, Player $player, int $particleId, int $endTime, string $skillName, string $sound = null, ?float $distance = 0.5, bool $followPlayer = false, bool $skipY = false, bool $skipCorrectPos = false, ?array $rgb = [255, 0, 0], ?int $howManyPar = 1)
    {
        $this->sa = $sa;
        $this->xyz = $xyz;
        /**$player nay cung la skillOwner , vi ban dau task nay co muc dich spawn particle dc attach vao skillowner*/
        $this->player = $player;
        $this->particleId = $particleId;
        $this->sound = $sound;
        $this->endTime = $endTime;
        $this->distanceForPersonalTask = $distance;
        $this->skillName = $skillName;
        $this->followPlayer = $followPlayer;
        $this->rgb = $rgb;
        $this->howManyPar = $howManyPar;

        $this->skipY = $skipY;

        $direc = $player->getDirection();
        $x = $xyz[0];
        $y = $xyz[1];
        $z = $xyz[2];
        $this->constantPos = $player->add($x, $y, $z);
        if (!$skipCorrectPos) {
            $this->constantPos = $this->posCorrection($direc, $player, $x, $y, $z);
        }
        if (($level = $player->getLevel()) == null) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        if ($sound != null) {
            $sound = new PlaySoundPacket();
            $x = $player->getX();
            $z = $player->getZ();
            $sound->x = $x;
            $sound->y = $player->getY();
            $sound->z = $z;
            $sound->volume = 100;
            $sound->pitch = 1;
            $sound->soundName = $this->sound;
            foreach ($level->getPlayers() as $player) {
                if (abs($player->getX()) <= $x + 100 && abs($player->getZ()) <= $z + 100) {
                    SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
                }
            }
        }
    }

    private function PosCorrection(int $direc, Vector3 $pos, ?float $x, ?float $y, ?float $z): Vector3
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

    public function onRun($tick)
    {
        $level = $this->player->getLevel();

        if (!$this->followPlayer and !$this->isTriggered) {
            $task = new blockPersonalTask($this->sa, $this->player, $this->constantPos, $level, $this->skillName, $this->endTime, $this->distanceForPersonalTask, $this->skipY, $this);
            $this->sa->getScheduler()->scheduleRepeatingTask($task, 1);
            $this->isTriggered = true;
        }

        /** vd endtime = 400 = 20s,tick = 1, => cu moi 1 tick timecheck se cong them 1,400 tick se du */
        if (($this->timeCheck += $this->getHandler()->getPeriod()) > $this->endTime) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $player = $this->player;

        $pos = $this->constantPos;
        if ($this->followPlayer) {
            $direc = $player->getDirection();
            $x = $this->xyz[0];
            $y = $this->xyz[1];
            $z = $this->xyz[2];
            $pos = $this->posCorrection($direc, $player, $x, $y, $z);
        }


        /** check if the player disconnect */
        if ($level == null) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        if ($level->getBlock($pos)->getId() != 0) {
            return;
        }
        #var_dump($pos);
        #var_dump("call");
        for ($i = 1; $i <= $this->howManyPar; $i++) {
            $level->addParticle(new GenericParticle($pos, $this->particleId, ((255 & 0xff) << 24) | (($this->rgb[0] & 0xff) << 16) | (($this->rgb[1] & 0xff) << 8) | ($this->rgb[2] & 0xff)));
        }
    }
}