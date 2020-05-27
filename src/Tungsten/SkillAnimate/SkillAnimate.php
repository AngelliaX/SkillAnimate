<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Tungsten\SkillAnimate\Database\YamlDatabase;
use Tungsten\SkillAnimate\EventListener\MainEventListener;
use Tungsten\SkillAnimate\EventListener\SkillListener;
use Tungsten\SkillAnimate\RepeatingTask\showingChakraTask;
use Tungsten\SkillAnimate\SkillContainer\GaraProtection;

use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use Tungsten\SkillAnimate\SkillContainer\GroundBadabum;

class SkillAnimate extends PluginBase implements Listener
{
    /** @var  */
    public static $instance;
    public $pvpWorldName = "world";
    public $skillIdItem = [500,501,502];

    public $database;
    public $skillData;
    public function onEnable()
    {
        $this->database = new YamlDatabase($this);
        $this->saveResource("skillData.yml");
        $this->skillData = new Config($this->getDataFolder()."skillData.yml");
        $listener = new MainEventListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $listener = new SkillListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);

        self::$instance = $this;
        $task = new showingChakraTask($this);
        $this->getServer()->getPluginManager()->registerEvents($task,$this);
        $this->getScheduler()->scheduleRepeatingTask($task,1);
    }
    public function onDisable()
    {
        $this->database->saveAll();
    }
}