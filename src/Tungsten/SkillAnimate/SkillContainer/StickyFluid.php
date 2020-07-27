<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\level\Level;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\spawnParticleDelayedTask;
use Tungsten\SkillAnimate\AnimateController\spawnParticleRepeatingTask;
use Tungsten\SkillAnimate\SkillAnimate;


class StickyFluid
{
    private $sa;
    //end this skill time;
    private $endTime;
    //distance to trigger the skillcollideevent;
    private $distance = 0.5;
    //Radius of the fluid zone
    private $radius;

    public function __construct(SkillAnimate $sa, Player $player)
    {
        $this->sa = $sa;
        if (!is_null($config = $sa->database->getConfig($player)->getNested("StickyFluid"))) {
            $this->endTime = $config["endTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
        } else {
            $config = $sa->skillData->getNested("StickyFluid");
            $this->endTime = $config["endTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
        }
        $this->spawnParticle($sa, $player);
    }

    private function spawnParticle(SkillAnimate $sa, Player $player): void
    {
        $level = $player->getLevel();
        $closest = "§cNo Target"; //string or player object
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


        $tempP = $player->add($player->getDirectionVector()->x, 1, 0);
        $tempC = null;
        if ($closest instanceof Player) {
            $tempC = $closest->add(0, 3, 0);
        } else {
            $tempC = $player;
            $lastSquare = 9;
        }
        //7block se mat 1s de toi muc tieu
        $time = (int)round((20 / 7) * sqrt($lastSquare));
        if ($closest instanceof Player) {
            $this->particleFromVectorAtoB($player, $level, $tempP, $tempC, $time);
        }
        $timeToExecute = 0;
        if ($closest instanceof Player) {
            //particle this will be run from $time to $time+10;
            $timeToExecute = 10; #0.5secs
            $this->particleFromVectorAtoB($player, $level, $tempC->add(0, -0.5, 0), $tempC->add(0, $this->getYofBlockUnder($closest), 0), $timeToExecute, $time);
        } else {
            $this->particleFromVectorAtoB($player, $level, $player->add(0, 2.5, 0), $player->add(0, $this->getYofBlockUnder($player), 0), $time, 0);
        }

        $timeToExecute2 = 20; #0.5secs to display a fully circle
        if ($closest instanceof Player) {
            $this->particleCircle($player,$closest, $timeToExecute2, $time + $timeToExecute);
        } else {
            $this->particleCircle($player,$player, $timeToExecute2, $time + $timeToExecute);
        }

    }

    /**
     * @param Player $player is skill owner
     * @param Player $a
     * @param Player|null $b
     * @param int|null $time
     * @param int|null $bonusTime to sync with the previous particle
     */
    private function particleFromVectorAtoB(Player $player, Level $level, Vector3 $a, ?Vector3 $b, ?int $time, ?int $bonusTime = 0): void
    {
        $playerPos = $a;//vector is modified already before this function
        $closest = $b;

        $tempDivide = 0;
        $pos = $closest;
        if (abs($pos->x - $playerPos->x) >= abs($pos->z - $playerPos->z)) {
            $tempDivide = abs($pos->x - $playerPos->x);
        } else {
            $tempDivide = abs($pos->z - $playerPos->z);
        }
        /** abs() is needed cuz if you dont use it, if it is negative, the loop will not be called because of $x always < 0 */
        for ($i = $time + 1; $i > 1; $i--) {
            $tempX = ($pos->x - $playerPos->x) * (($time - $i) / $time);
            $tempY = ($pos->y - $playerPos->y) * (($time - $i) / $time);
            $tempZ = ($pos->z - $playerPos->z) * (($time - $i) / $time);
            /** already correct the pos above ($pos) so dont need to correct again here */
            $tempPos = $playerPos->add($tempX, $tempY, $tempZ);
            $tempPos = $tempPos->add($this->frand(0, 0.125), $this->frand(0, 0.125), $this->frand(0, 0.125));
            /** $time will increase from 0->1*/
            $time2 = (int)$time - $i;
            for ($o = rand(3, 6); $o > 1; $o--) {
                $this->sa->getScheduler()->scheduleDelayedTask(new spawnParticleDelayedTask($tempPos, $level, Particle::TYPE_SPARKLER, $player, "StickyFluid", 1, "liquid.water", $this->distance, [rand(0, 81), rand(0, 247), rand(235, 255)], true), $time2 + $bonusTime);
            }
        }
    }


    private function frand($min, $max, $decimals = 0): float
    {
        $scale = pow(10, $decimals);
        return mt_rand((int)$min * $scale, (int)$max * $scale) / $scale;
    }

    private function getYofBlockUnder(Player $target): ?float
    {
        $y = $target->y;
        while (true) {
            $block = $target->getLevel()->getBlock(new Vector3($target->x,$y,$target->z));
            if ($block->getId() != 0) {
                $y = $block->y + 1;
                break;
            }
            if ($y < 0) {
                $y = $target->y;
                break;
            }
            $y--;
        }
        $y = $y - $target->y;
        return $y;
    }

    private function particleCircle(Player $skillOwner,Player $target, int $time, int $bonusTime)
    {
        $y = $this->getYofBlockUnder($target);
        $timeToExecute2 = $time;
        $totalParticle = 0;
        for ($r = 0; $r <= $this->radius; $r += 0.25) {
            $totalParticle = $totalParticle + ($r * 60) + 1;
        }
        $tempParticle = 0;//how many particle having been displayed
        for ($r = 0; $r <= $this->radius; $r += 0.25) {
            $amount = ($r * 60) + 1;
            for ($i = pi(); $i <= 3 * pi(); $i += 2 * pi() / $amount) {
                $tempParticle++;
                $x = cos($i) * $r;
                $z = sin($i) * $r;
                $tempTime = (int)(($timeToExecute2 / $totalParticle) * $tempParticle + 1); //+1 for the case 0.xxxx
                $correctX = $target->x - $skillOwner->x;
                $correctY = $target->y - $skillOwner->y;
                $correctZ = $target->z - $skillOwner->z;
                $this->sa->getScheduler()->scheduleDelayedRepeatingTask(new spawnParticleRepeatingTask($this->sa, [$x +$correctX, $y+$correctY, $z+$correctZ], $skillOwner, Particle::TYPE_SPARKLER, $this->endTime, "StickyFluid", "liquid.water", $this->distance, false, true, true, [rand(0, 81), rand(0, 247), rand(235, 255)],1), (int)$tempTime + $bonusTime, 30);
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
}