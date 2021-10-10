<?php

namespace Tungsten\SkillAnimate\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use Tungsten\SkillAnimate\SkillAnimate;

class Commands extends Command implements PluginIdentifiableCommand
{
    private $sa;

    public function __construct(SkillAnimate $sa)
    {
        parent::__construct("sa", "SkillAnimate Commands");
        $this->setPermission("skillanimate.OP.permission");
        $this->sa = $sa;
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage("§cMissing parameter");
            return;
        }
        if ($args[0] == "addarea") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§cOnly use in-game!");
                return;
            }
            if (!isset($args[1])) {
                $sender->sendMessage("§e/sa addarea <name>");
                return;
            }
            $this->sa->evlistener->playerName[$sender->getName()][2] = $args[1];
            $sender->sendMessage("§6Starting breaking 2 block to finish!");
            return;
        }
        if ($args[0] == "removearea") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§cOnly use in-game!");
                return;
            }
            if (!isset($args[1])) {
                $sender->sendMessage("§e/pi removearea <name>");
                return;
            }
            $config = $this->sa->safeArea;
            if (null == $config->getNested($sender->getLevel()->getName())) {
                $sender->sendMessage("§cThere is no area on this level");
                return;
            }
            if (!array_key_exists($args[1], $config->getNested($sender->getLevel()->getName()))) {
                $sender->sendMessage("§cThere is no area named §6" . $args[1] . "§c on this level");
                return;
            }
            $tempArr = $config->getNested($sender->getLevel()->getName());
            unset($tempArr[$args[1]]);
            $config->setNested($sender->getLevel()->getName(), $tempArr);
            $config->save();
            $sender->sendMessage("§aSuccessfully remove area §6" . $args[1] . "§a on this level");
            return;
        }
        if($args[0] == "sendskill"){
            $skillItem = $this->skillItem;
            $skillName = [["GaraProtection","GroundBadabum","SoulHand"],["WispsSpawner","StickyFluid","ChasingFluid"]];
            if (!isset($args[1])) {
                return;
            }
            if(!isset($args[2])){
                return;
            }
            if(($player = $this->sa->getServer()->getPlayer($args[1])) == null){
                return;
            }
            if(!array_key_exists($args[2],$skillItem)){
                return;
            }
            foreach ($skillItem as $value){
                foreach($value as $number){
                    if($player->getInventory()->contains(Item::get($number))){
                        $player->getInventory()->remove(Item::get($number));
                    }
                }
            }
            $color = ["§r§6","§r§b"];
            foreach($skillItem[$args[2]] as $key=>$num){
                $item = Item::get($num)
                ->setCustomName($color[$args[2]].$skillName[$args[2]][$key]);
                $player->getInventory()->addItem($item);
            }
            $player->sendMessage("§aĐã thêm skill vào túi đồ");
            return;
        }
        $sender->sendMessage("§cUnknown parameter");
        return;
    }
    public $skillItem = [[500,501,502],[503,504,505]];
    public function getPlugin(): Plugin
    {
        return $this->sa;
    }
}