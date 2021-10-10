<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\destroyBlockTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockDelayedTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockRepeatingTask;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;
use pocketmine\scheduler\Task;

class SoulHand extends Task
{
    private $sa;
    private $player;

    public $endTime;
    public $oneRoundSpeed;

    private $checkTick = 0;
    private $howManyTimeCheck = 0;
    public function __construct(SkillAnimate $sa, Player $player)
    {
        $this->sa = $sa;
        $this->player = $player;
        if(!is_null($config = $sa->database->getConfig($player)->getNested("SoulHand"))){
            $this->endTime = $config["endTime"];
            $this->oneRoundSpeed =  $config["oneRoundSpeed"];
        }else{
            $config = $sa->skillData->getNested("SoulHand");
            $this->endTime = $config["endTime"];
            $this->oneRoundSpeed =  $config["oneRoundSpeed"];
        }
    }
    //look like [true,false,false] for each statemant in the if else inside onRun()
    private $isRun = [false,false,false];
    public function onRun($tick){
        if($this->checkTick == 0){
            $this->checkTick = $tick;
        }
        $tick -= $this->checkTick;

        if($tick >= $this->endTime){
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
        }
        $player = $this->player;
        $pos = new Vector3($player->getX(), $player->getY(), $player->getZ());
        $level = $player->getLevel();
        //dong 0 nam 1  bac 3 tay 2
        $tick -= $this->oneRoundSpeed*$this->howManyTimeCheck;
        //TODO task nay chay nhieu task qua, cho no chay 1 lan thoi
        //TODO trong if else cho them code neu da chay r thi stop
        if($tick <= $this->oneRoundSpeed /4){
            //this is also means do nothing
            if(!$this->isRun[0]) {
                $this->isRun[0] = true;
                $this->isRun[1] = false;
                $endTime = $this->oneRoundSpeed / 4 - $tick;
            }
        }else if($tick <= $this->oneRoundSpeed/4 *3){
            if(!$this->isRun[1]){
                $this->isRun[1] = true;
                $this->isRun[2] = false;
                $endTime = $this->oneRoundSpeed/4*4 - $tick; //đỡ phải lặp lại trong else if ở dưới
                $this->callTaskParallel([3,0,0],$endTime);
                $this->callTaskParallel([3,0,2],$endTime);

                $this->callTaskParallel([1,0,3],$endTime);

                $this->callTaskParallel([-1,0,3],$endTime);

                $this->callTaskParallel([-3,0,0],$endTime);
                $this->callTaskParallel([-3,0,2],$endTime);
            }
        }else if($tick < $this->oneRoundSpeed/4 *4){
            if(!$this->isRun[2]){
                $this->isRun[2] = true;
                $this->isRun[0] = false;
                $endTime = $this->oneRoundSpeed/4*4 - $tick;
                $this->callTaskParallel([3,1,0],$endTime);
                $this->callTaskParallel([3,1,2],$endTime);

                $this->callTaskParallel([1,1,3],$endTime);

                $this->callTaskParallel([-1,1,3],$endTime);

                $this->callTaskParallel([-3,1,0],$endTime);
                $this->callTaskParallel([-3,1,2],$endTime);
            }
        }else{
            $this->howManyTimeCheck++;
        }
    }

    // Tự biến đổi cho mỗi skill
    public function callTaskParallel(array $xyz,?int$endTime,string $sound = null){
        $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];
        $this->sa->getScheduler()->scheduleRepeatingTask(new spawnBlockRepeatingTask($this->sa,[$xyz[0],$xyz[1],$xyz[2]],$this->player,$blockData,$endTime,"SoulHand","dig.grass",1),1);
        $this->sa->getScheduler()->scheduleRepeatingTask(new spawnBlockRepeatingTask($this->sa,[$xyz[0],$xyz[1],-$xyz[2]],$this->player,$blockData,$endTime,"SoulHand","dig.grass",1),1);
    }

    /*luu tru skill cu
     if($tick <= $this->oneRoundSpeed /4){
            $endTime = $this->oneRoundSpeed /4 - $tick;
            $this->callTaskParallel([-1,1,-1],$endTime);

            $this->callTaskParallel([0,2,-2],$endTime);
            $this->callTaskParallel([0,3,-3],$endTime);
            $this->callTaskParallel([0,3,-4],$endTime);;
            $this->callTaskParallel([1,3,-5],$endTime);
            $this->callTaskParallel([2,2,-5],$endTime);

            $this->callTaskParallel([3,1,-4],$endTime);
            $this->callTaskParallel([4,1,-4],$endTime);
            $this->callTaskParallel([5,1,-3],$endTime);
            $this->callTaskParallel([2,1,-3],$endTime);

            $this->callTaskParallel([1,1,-5],$endTime);
            $this->callTaskParallel([0,1,-4],$endTime);
        }else if($tick <= $this->oneRoundSpeed/4 *2){
            $endTime = $this->oneRoundSpeed/4*2 - $tick;
            $this->callTaskParallel([-1,1,-1],$endTime);

            $this->callTaskParallel([0,2,-2],$endTime);
            $this->callTaskParallel([1,3,-2],$endTime);
            $this->callTaskParallel([1,3,-3],$endTime);;
            $this->callTaskParallel([2,3,-4],$endTime);
            $this->callTaskParallel([3,2,-4],$endTime);

            $this->callTaskParallel([4,1,-3],$endTime);
            $this->callTaskParallel([5,1,-3],$endTime);
            $this->callTaskParallel([6,1,-2],$endTime);
            $this->callTaskParallel([3,1,-2],$endTime);

            $this->callTaskParallel([2,1,-4],$endTime);
            $this->callTaskParallel([1,1,-3],$endTime);
        }else if($tick < $this->oneRoundSpeed/4 *4){
            $endTime = $this->oneRoundSpeed/4*4 - $tick;
            $this->callTaskParallel([-1,1,-1],$endTime);

            $this->callTaskParallel([0,2,-2],$endTime);
            $this->callTaskParallel([1,3,-2],$endTime);
            $this->callTaskParallel([2,3,-3],$endTime);
            $this->callTaskParallel([3,3,-3],$endTime);
            $this->callTaskParallel([3,2,-3],$endTime);

            $this->callTaskParallel([3,1,-2],$endTime);
            $this->callTaskParallel([4,1,-2],$endTime);
            $this->callTaskParallel([5,1,-1],$endTime);
            $this->callTaskParallel([2,1,-1],$endTime);

            $this->callTaskParallel([1,1,-3],$endTime);
            $this->callTaskParallel([0,1,-2],$endTime);
        }else{
            $this->howManyTimeCheck++;
        }
     */
}