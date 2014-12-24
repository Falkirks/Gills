<?php
namespace gills;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Gills extends PluginBase implements CommandExecutor, Listener{
    /** @var  \SplObjectStorage*/
    private $players;
    public function onEnable(){
        $this->players = new \SplObjectStorage();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(isset($args[0])){
            $player = $this->getServer()->getPlayer($args[0]);
            if(!($player instanceof Player)){
                $sender->sendMessage($args[0] . " is not a valid player.");
                return true;
            }
        }
        elseif($sender instanceof Player){
            $player = $sender;
        }
        else{
            $sender->sendMessage("Usage: /gills <name>");
            return true;
        }

        if(isset($args[1])){
            switch(strtolower($args[1])){
                case 'give':
                case 'on':
                case 'true':
                    $sender->sendMessage($player->getName() . " can now breath underwater.");
                    $this->players->attach($player);
                    return true;
                    break;
                case 'take':
                case 'off':
                case 'false':
                    $sender->sendMessage($player->getName() . " can no longer breath underwater.");
                    unset($this->players[$player]);
                    return true;
                    break;
            }
        }

        if(isset($this->players[$player])){
            $sender->sendMessage($player->getName() . " can no longer breath underwater.");
            unset($this->players[$player]);
            return true;
        }
        else{
            $sender->sendMessage($player->getName() . " can now breath underwater.");
            $this->players->attach($player);
            return true;
        }

    }
    public function onEntityDamage(EntityDamageEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_DROWNING){
            if($player->hasPermission("gills.breath") || isset($this->players[$player])){
                $event->setCancelled();
            }
        }
    }
}