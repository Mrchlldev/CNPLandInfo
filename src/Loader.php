<?php

namespace Mrchlldev\CNPLandInfo;

use Ifera\Scorehud\Scorehud;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\event\TagsResolveEvent;
use xeonch\ClaimAndProtect\manager\LandManager;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use pocketmine\scheduler\ClosureTask;
use pocketmine\event\Listener;

class Loader extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->loadScorehud();
    }

    public function loadScorehud(): void {
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach($this->getServer()->getOnlinePlayers() as $player){
                if(!$player->isOnline()) continue;
                $id = new PlayerTagUpdateEvent($player, new ScoreTag("cnp.id", $this->getLandIdInPosition($player->getPosition())));
                $owner = new PlayerTagUpdateEvent($player, new ScoreTag("cnp.owner", $this->getLandOwnerInPosition($player->getPosition())));
                $id->call();
                $owner->call();
            }
        }), 20); // 20 ticks is 1 second ygy kuy
    }

    public function onTagResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $tag = $event->getTag();
        $value = "";
        switch($tag->getName()){
            case "cnp.id":
                $value = $this->getLandIdInPosition($player->getPosition());
            break;
            case "cnp.owner":
                $value = $this->getLandOwnerInPosition($player->getPosition());
            break;
        }
        $tag->setValue((string) $value);
    }

    public function getLandIdInPosition(Position $pos): int {
        $landid = 0;
        $landManager = new LandManager();
        $landInArea = $landManager->getLandsIn($pos);
        if (empty($landInArea)) return 0;
        foreach ($landInArea as $id => $data) {
            return id;
        }
    }

    public function getLandOwnerInPosition(Position $pos): string|int {
        $landowner = "No Land (unknown)";
        $landManager = new LandManager();
        $landInArea = $landManager->getLandsIn($pos);
        if (empty($landInArea)) return $landowner;
        foreach ($landInArea as $id => $data) {
            $landowner = $data["owner"];
            return $landowner;
        }
    }

    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $landManager = new LandManager();
        $landInArea = $landManager->getLandsIn($player->getPosition());
        if (empty($landInArea)) return;
        foreach ($landInArea as $id => $data) {
            $owner = $data["owner"];
            $player->sendTip(str_replace(["{id}", "{owner}"], [$id, $owner], $this->getConfig()->getNested("land.tip")));
        }
    }
}
