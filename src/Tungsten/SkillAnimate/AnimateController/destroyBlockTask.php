<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\AnimateController;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use Tungsten\SkillAnimate\SkillAnimate;
class destroyBlockTask extends Task
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
    public function __construct(Vector3 $pos, Level $level, array $blockData,string $sound = null)
    {
        $this->pos = $pos;
        $this->level = $level;
        $this->blockData = $blockData;
        $this->sound = $sound;
    }


    public function onRun($tick)
    {

        if($this->level->getBlock($this->pos)->getId() != $this->blockData[0] or $this->level->getBlock($this->pos)->getDamage() != $this->blockData[1]){
            return;
        }

        $this->level->setBlock($this->pos,Block::get(0,0),false,false);

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