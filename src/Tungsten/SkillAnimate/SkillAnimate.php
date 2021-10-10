<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate;

use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Tungsten\SkillAnimate\Commands\Commands;
use Tungsten\SkillAnimate\Database\YamlDatabase;
use Tungsten\SkillAnimate\EventListener\ChakraGenerateListener;
use Tungsten\SkillAnimate\EventListener\MainEventListener;
use Tungsten\SkillAnimate\EventListener\SkillCollideListener;
use Tungsten\SkillAnimate\EventListener\SkillExecuteListener;
use Tungsten\SkillAnimate\EventListener\SkillOnHandListener;
use Tungsten\SkillAnimate\RepeatingTask\showingChakraTask;

class SkillAnimate extends PluginBase implements Listener
{
    /** @var */
    public static $instance;
    public $pvpWorldName = "thepit";
    public $skillIdItem = [500, 501, 502, 503, 504, 505];

    public $database;
    public $skillData;
    public $safeArea;

    public $evlistener;
    public $cmds;
    public function onEnable()
    {
        $this->database = new YamlDatabase($this);
        $this->saveResource("skillData.yml");
        $this->skillData = new Config($this->getDataFolder() . "skillData.yml");
        $this->safeArea = new Config($this->getDataFolder() . "config.yml");


        $this->cmds = $cmds = new Commands($this);
        $this->getServer()->getCommandMap()->register("skillanimate", $cmds);
        $this->evlistener = $listener = new MainEventListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $listener = new ChakraGenerateListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $listener = new SkillCollideListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $listener = new SkillExecuteListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $listener = new SkillOnHandListener($this);
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        $this->getScheduler()->scheduleRepeatingTask($listener, 1);

        self::$instance = $this;
        $task = new showingChakraTask($this);
        $this->getServer()->getPluginManager()->registerEvents($task, $this);
        $this->getScheduler()->scheduleRepeatingTask($task, 1);

        foreach ($this->skillIdItem as $id) {
            ItemFactory::registerItem(new Item($id));
            Item::addCreativeItem(Item::get($id));
        }

    }

    public function onDisable()
    {
        $this->database->saveAll();
    }
}