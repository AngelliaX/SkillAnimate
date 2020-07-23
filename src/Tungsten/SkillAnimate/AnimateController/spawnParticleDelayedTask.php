<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\AnimateController;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;
use pocketmine\level\particle\Particle;
class spawnParticleDelayedTask extends Task
{
    /** @var Vector3 */
    private $pos;
    /** @var Level */
    private $level;

    private $particleId;
    /** @var string|null  */
    private $sound;
    /** @var int  */
    private $distance = 100;

    private $player;
    private $skillName;
    private $destroyTime;
    private $distanceForPersonalBlock;
    private $skipPersonalTask;
    private $rgb;
    private $skipY;
    //SkillAnimate $sa, Player $player, Vector3 $pos, Level $level, string $skillName, int $endtime
    public function __construct(Vector3 $pos, Level $level, int $particleId,Player $player,string $skillName,int $destroyTime,string $sound = null,?float $distance = 0.5,array $rgb = [0,0,0],bool $skipPersonalTask = false,bool $skipY = false)
    {
        $this->pos = $pos;
        $this->level = $level;
        $this->particleId = $particleId;
        $this->sound = $sound;
        $this->player = $player;
        $this->skillName = $skillName;
        $this->destroyTime = $destroyTime;
        $this->distanceForPersonalBlock =  $distance;
        $this->skipPersonalTask = $skipPersonalTask;
        $this->rgb = $rgb;
        $this->skipY = $skipY;
    }


    public function onRun($tick)
    {
        if($this->level->getBlock($this->pos)->getId() != 0){
            return;
        }
        if(!$this->skipPersonalTask){
            $sa = SkillAnimate::$instance;
            $task = new blockPersonalTask($sa, $this->player, $this->pos, $this->level, $this->skillName, $this->destroyTime,$this->distanceForPersonalBlock,$this->skipY);
            $sa->getScheduler()->scheduleRepeatingTask($task, 1);
        }
        $this->level->addParticle(new GenericParticle($this->pos,$this->particleId,((255 & 0xff) << 24) | (($this->rgb[0] & 0xff) << 16) | (($this->rgb[1] & 0xff) << 8) | ($this->rgb[2] & 0xff)));
        #$this->level->setBlock($this->pos,Block::get($this->blockData[0],$this->blockData[1]),false,false);

        if($this->sound != null){
            $sound = new PlaySoundPacket();
            $x = $this->pos->getX();
            $z = $this->pos->getZ();
            $sound->x = $x;
            $sound->y = $this->pos->getY();
            $sound->z = $z;
            $sound->volume = 100;
            $sound->pitch = 1;
            $sound->soundName = $this->sound;
            foreach($this->level->getPlayers() as $player){
                if(abs($player->getX()) <= $x + $this->distance && abs($player->getZ()) <= $z + $this->distance){
                    SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
                }
            }

        }
    }

}