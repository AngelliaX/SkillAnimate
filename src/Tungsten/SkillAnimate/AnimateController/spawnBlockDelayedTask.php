<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\AnimateController;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use Tungsten\SkillAnimate\SkillAnimate;
class spawnBlockDelayedTask extends Task
{
    /** @var Vector3 */
    private $pos;
    /** @var Level */
    private $level;
    /** @var array
     * return [id,meta]
     */
    private $blockData;
    /** @var string|null  */
    private $sound;
    /** @var int  */
    private $distance = 100;
    //SkillAnimate $sa, Player $player, Vector3 $pos, Level $level, string $skillName, int $endtime
    public function __construct(Vector3 $pos, Level $level, array $blockData,string $sound = null)
    {
        $this->pos = $pos;
        $this->level = $level;
        $this->blockData = $blockData;
        $this->sound = $sound;
    }


    public function onRun($tick)
    {
        if($this->level->getBlock($this->pos)->getId() != 0){
            return;
        }

        $this->level->setBlock($this->pos,Block::get($this->blockData[0],$this->blockData[1]),false,false);

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