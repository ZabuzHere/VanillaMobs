<?php

declare(strict_types=1);

namespace tgwaste\Mobs;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use tgwaste\Mobs\event\ItemInteractListener;
use tgwaste\Mobs\Listener\FeedListener;
use tgwaste\Mobs\API\SoundAPI;

class Main extends PluginBase implements Listener {
	public static $instance;

	public $classes;
	public $damagetags;
	public $nospawn;
	public $regainhealth;
	public $spawnmobs;
	public $spawnmsgs;
	public $attrobj;
	public $toolsobj;
	public $spawnobj;
	public $biomesobj;
	public $coordsobj;

	protected function onEnable() : void {
		self::$instance = $this;

		$this->attrobj = (new Attributes);
		$this->toolsobj = (new Tools);
		$this->spawnobj = (new Spawn);
		$this->biomesobj = (new Biomes);
		$this->coordsobj = (new Coords);

		$this->saveDefaultConfig();
		$this->getScheduler()->scheduleRepeatingTask(new Schedule(), 200);
		$this->getServer()->getPluginManager()->registerEvents(new Listen(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new GolemBuilder(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ItemInteractListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new FeedListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new SoundAPI(), $this);

		$this->damagetags = $this->getConfig()->get("damagetags");
		$this->nospawn = $this->getConfig()->get("nospawn");
		$this->regainhealth = $this->getConfig()->get("regainhealth");
		$this->spawnmobs = $this->getConfig()->get("spawnmobs");
		$this->spawnmsgs = $this->getConfig()->get("spawnmsgs");

		(new Registrations)->registerEntities();
	}

	public static function getInstance(): self {
		return self::$instance;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if ($sender instanceof Player and !$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$sender->sendMessage("§cYou do not have permission to use mob commands§r");
			return true;
		}

		if ($label === "listmobs") {
			$this->toolsobj->listMobs($sender);
			return true;
		}

		if ($label === "killmobs") {
			$this->toolsobj->killMobs($sender);
			return true;
		}

		if ($label === "summon") {
			if (!$sender instanceof Player) {
				$sender->sendMessage("§cThat command cannot be done from the console§r");
				return true;
			}

			if (count($args) < 1) {
				return false;
			}

			$entityName = $args[0];

			if (!array_key_exists($entityName, $this->classes)) {
				$sender->sendMessage("§cInvalid Mob§r");
				$available = "";
				foreach ($this->classes as $mobname => $typeclass) {
					$available = $available . " " . $mobname;
				}
				$sender->sendMessage("Available Mobs:§d$available §r");
				return true;
			}

			if (count($args) > 1) {
				for ($x = 0; $x < (int)$args[1]; $x++)
					$this->spawnobj->spawnEntity($entityName, $sender->getWorld(), $sender->getPosition());
			} else {
				$this->spawnobj->spawnEntity($entityName, $sender->getWorld(), $sender->getPosition());
			}

			return true;
		}

		return false;
	}
}
