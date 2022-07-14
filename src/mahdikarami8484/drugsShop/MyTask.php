<?php

namespace mahdi;

use pocketmine\entity\Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\particle\SmokeParticle;

class MyTask extends Task
{
    public $plugin;
    public $smoker;

    public function __construct($plugin, $player)
    {
        $this->plugin = $plugin;
        $this->smoker = $player;
    }

    public function onRun() : void
    {
		//var_dump($this->smoker->getLevel());
		if(is_null($this->smoker->getWorld())){
            var_dump("SSS");
            $this->onCancel();
		}else{
            var_dump("OOO");
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.1,1.5,0), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.2,1.5,0), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.3,2.5,0), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.4,2.5,0), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.5,2.5,0), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.1,1.5,0.1), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.2,1.5,0.2), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.3,2.5,0.3), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.4,2.5,0.4), new SmokeParticle(1));
            $this->smoker->getWorld()->addParticle($this->smoker->getPosition()->add(0.5,2.5,0.5), new SmokeParticle(1));
        if (is_null($this->smoker->getEffects()->get(VanillaEffects::NAUSEA()))){
            $this->onCancel();
        }
		}
    }
}