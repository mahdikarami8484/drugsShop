<?php

namespace mahdikarami8484\drugsShop;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\command\{Command , CommandSender};
use pocketmine\utils\TextFormat as c;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;

class main extends PluginBase implements Listener
{
	public $plugName = C::YELLOW."[".C::BLUE."Drug".C::YELLOW."]";
	public $cd = [];
    public $smoking = array();
	public $file;
	public $drugs;
	public $namedrugs = [];
	public $price;
	public $number2;
	
    public function onEnable(): void
    {
		$check = false;
		$this->getServer()->getPluginManager()->registerEvents($this , $this);
        $this->getlogger()->info(C::BLUE."Plugin drugsShop enable");
		if(!file_exists($this->getDataFolder(). 'drugs.yml')){
			$check = true;
		}
		$this->file = new Config($this->getDataFolder()."drugs.yml", Config::YAML);
		if($check){
			$this->file->setNested('Opium', [
				'price' => 1200,
				'time' => 100,
				'effect' => 'HEALTH_BOOST',
				'iditem' => 464
			]);
			$this->file->save();
		}
		$this->drugs = $this->file->getAll();
		foreach ($this->drugs as $name => $value)
		{
			array_push($this->namedrugs, $name);
		}
	}
    

    public function onCommand(CommandSender $player, Command $cmd, String $label, array $args) : bool
    {
        switch($cmd->getName())
        {
            case "buydrug":
                if($player instanceof player) {
                   $this->formDrug($player);
                }else{
					$player->sendMessage($this->plugName.C::RED." please try in game |:");
				}
                
        }
        return true;
    }

	public function onDrug(PlayerItemUseEvent $e)
	{
		$item = $e->getItem();
		$player = $e->getPlayer();
		if(in_array($item->getCustomName(), $this->namedrugs))
		{
			$this->getServer()->broadcastMessage($this->plugName." ".C::RED.$player->getName()." used drugsShop...");
			$time = $this->drugs[$item->getCustomName()]['time'];
			$effect = explode(',', $this->drugs[$item->getName()]['effect']);
			array_push($this->smoking, $player->getName());
			foreach ($player->getInventory()->getContents() as $slot => $it) {
				if ($it->getCustomName() == $item->getName()) {
					$it->setCount($it->getCount() - 1);
					$player->getInventory()->setItem($slot, $it);
				}
			}
			$this->getScheduler()->scheduleRepeatingTask(new MyTask($this, $player), 20);
			$player->getEffects()->add(new effectinstance(VanillaEffects::NAUSEA(), $time * 20, 255, false, false));
			foreach($effect as $eff)
			{
				$player->getEffects()->add(new EffectInstance(StringToEffectParser::getInstance()->parse((string)$eff), $time * 20, 255, false, false));
			}
		}
	}
	
	public function formDrug($player){
		$this->drugs = $this->file->getAll();
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		if($api === null || $api->isDisabled()){
			return;
		}
		$form = new SimpleForm(function (Player $player, $data){
			if($data === null){
				return;
			}
			$i = 0;
			foreach($this->drugs as $drug => $array){
				switch($data){
					case $i:
						$this->formMany($player, $i);
						return;
				}
				$i++;
			}
		});
		$form->setTitle(C::RED."Drug Shop");
		foreach($this->drugs as $drug => $array){
			$form->addButton($drug . "\n" . C::RED . $this->drugs[$drug]['price']);
		}
		$form->sendToPlayer($player);
	}
		
	
	public function formMany($player , $type){
		$i = 0;
		foreach($this->drugs as $drug => $array){
			switch($type){
				case $i:
					$this->namedrug = $drug;
					break;
			}
			$i++;
		}
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = new CustomForm(function (Player $player, $data){
		if($data != null){
			$this->SubmitMoney($player, $this->namedrug , $data[1]);
			return;
		}else{
			return;
		}
		});
		$this->price = $this->drugs[$this->namedrug]['price'];
		$form->setTitle(C::RED."Buy Drug");
		$form->addLabel(C::BLUE."how many ".$this->namedrug." ?");
		$form->addSlider(C::YELLOW.$this->namedrug."s",1,64);
		$form->sendToPlayer($player);
}


	public function SubmitMoney($player , $type, $number){
		$this->number2 = $number;
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = new SimpleForm(function (Player $player, $data){
		if($data === null){
			return;
		}
		switch($data){
			case 0:
				$this->giveMoney($player, $this->namedrug, $this->number2);
				return;
			case 1:
				return;
		}
		});
		$this->price = $this->price * $number;
		$form->setTitle(C::RED."Buy Drug");
		$form->setContent(C::BLUE."Price ".$this->namedrug.C::GREEN." : ".C::BLUE.$this->price);
		$form->addButton(C::GREEN."Yes, Buy it");
		$form->addButton(C::RED."No");
		$form->sendToPlayer($player);
	}


	public function giveMoney($player, $type, $number){
		$api = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if($api === null || $api->isDisabled()){
			return;
		}
		$playermoney = $api->myMoney($player);
		if($playermoney > $this->price){
			$api->reduceMoney($player,$this->price);
			$this->giveDrug($player, $this->namedrug, $number);
		}else{
			$player->sendMessage($this->plugName." ".C::RED."You have not enough money");
			return;
		}
	}

	public function giveDrug($player, $type, $number){
		$idItem = $this->drugs[$type]['iditem'];

		try{
			$item = StringToItemParser::getInstance()->parse((string)$idItem) ?? LegacyStringToItemParser::getInstance()->parse((string)$idItem);
		}catch(LegacyStringToItemParserException $e){
			$item = null;
		}
		if ($item === null) {
			return;
		}
		$item->setCount($number);
		$player->getInventory()->addItem($item->setCustomName($type));
	}
	
	    public function countDrug(Player $player){
        $i = 0;
        foreach ($player->getInventory()->getContents() as $item){
            if($item->getName() == $this->namedrug){
               $count = $item->getCount();
            }
        }
    }

    }

?>
