<?php

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */


/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once 'shuttersCmd.class.php';

class shutters extends eqLogic
{
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
    * Fonction exécutée automatiquement toutes les minutes par NextDom
    public static function cron() {

    }
    */

    /*
    * Fonction exécutée automatiquement toutes les heures par NextDom
      public static function cronHourly() {

    }
    */

    /*
    * Fonction exécutée automatiquement tous les jours par NextDom
      public static function cronDaily() {

    }
    */

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert()
    {

    }

    public function postInsert()
    {
    }

    public function preSave()
    {

    }

    public function postSave()
    {
        $eqType = $this->getConfiguration('eqType', null);
        $eqLogicName = $this->getName();
        log::add('shutters', 'debug', 'shutters::preRemove() : eqLogic => ' . $eqLogicName . ' ; eqType =>' . $eqType);

        if(!empty($eqType)) {
            $this->loadCmdFromConfFile($eqType);
        }

        switch ($eqType) {
            case 'externalConditions':
                $this->addExternalConditionsEvents();
                break;
            case 'heliotropeZone':
                # code...
                break;
            case 'shuttersGroup':
                # code...
                break;
            case 'shutter':
                $this->updateShutterEventsListener();
                break;
            
            default:
                # code...
                break;
        }

    }

    public function preUpdate()
    {
        
    }    

    public function postUpdate()
    {
    }

    public function preRemove()
    {
        $eqType = $this->getConfiguration('eqType', null);
        $eqLogicName = $this->getName();
        log::add('shutters', 'debug', 'shutters::preRemove() : eqLogic => ' . $eqLogicName . ' ; eqType =>' . $eqType);

        switch ($eqType) {
            case 'externalConditions':
                break;
            case 'heliotropeZone':
                # code...
                break;
            case 'shuttersGroup':
                # code...
                break;
            case 'shutter':
                $this->removeShutterEventsListener();
                break;
            
            default:
                # code...
                break;
        }
    }
        
    public function postRemove()
    {
        
    }


    private function checkSettings()
    {
        $eqType = $this->getConfiguration('eqType', null);

        if (empty($eqType)) {
            throw new \Exception (__('Le type d\'équipement doit être renseigné!', __FILE__));
            return;
        }

    }
    
    /**
     * Load commands from JSON file
     */
    private function loadCmdFromConfFile(string $_eqType = '')
    {
        $eqLogicName = $this->getName();
        $file = dirname(__FILE__) . '/../config/devices/' . $_eqType . '.json';
        if (!is_file($file)) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : no commands configuration file to import for => '. $eqLogicName);
			return;
		}
		$content = file_get_contents($file);
		if (!is_json($content)) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file is not JSON formatted for => '. $eqLogicName);
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file is not well formatted for => '. $eqLogicName);
			return;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $existingCmd) {
				if ((isset($command['logicalId']) && $existingCmd->getLogicalId() === $command['logicalId'])
				    || (isset($command['name']) && $existingCmd->getName() === $command['name'])) {
                    log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : command => ' . $command['logicalId'] . ' already exist for => '. $eqLogicName);
                    $cmd = $existingCmd;
					break;
				}
            }
            if($this->getConfiguration('eqType', null) === 'externalConditions') {
                if(isset($command['configuration']['condition']) && empty($this->getConfiguration($command['configuration']['condition'], null))) {
                    if($cmd !== null || is_object($cmd)) {
                        $cmd->remove();
                        log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : command => ' . $command['logicalId'] . ' successfully deleted for => '. $eqLogicName);
                    }
                    continue;
                }
            }
        
			if ($cmd === null || !is_object($cmd)) {
				$cmd = new shuttersCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
                log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : command => ' . $command['logicalId'] . ' successfully added for => '. $eqLogicName);
			}
        }
        log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands imported successfully for => '. $eqLogicName);
    }

    private function addExternalConditionsEvents()
    {
        foreach (eqLogic::byType('shutters', true) as $eqLogic) {
            if (!is_object($eqLogic) || $eqLogic->getConfiguration('eqType', null) !== 'shutter') {
                continue;
            }
            if (empty($eqLogic->getConfiguration('externalConditionsId', null))) {
                continue;
            }
            $eqLogicName = $eqLogic->getName();
            $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutter' => $eqLogic->getId()));
            if (!is_object($listener)) {
                log::add('shutters', 'debug', 'shutters::addExternalConditionsEvents() : externalConditions events listener doesn\'t exist for shutter => ' . $eqLogicName);
                continue;
            }
            $conditions = ['fireCondition', 'absenceCondition', 'presenceCondition', 'outdoorLuminosityCondition', 'outdoorTemperatureCondition', 'firstUserCondition', 'secondUserCondition'];
            foreach ($conditions as $condition) {
                $cmdId = str_replace('#', '', $eqLogic->getConfiguration($condition));
                if (empty($cmdId)) {
                    continue;
                }
                $cmd = cmd::byId($cmdId);
                if (!is_object($cmd)) {
                    log::add('shutters', 'debug', 'shutters::addExternalConditionsEvents() : cmd => ' . $cmdId  . ' doesn\'t exist for externalConditions =>' . $this->getName());
                    continue;
                } else {
                    $listener->addEvent($cmdId);
                    log::add('shutters', 'debug', 'shutters::addExternalConditionsEvents() : cmd => ' . $cmdId  . ' successfully added to listener for shutter => ' . $eqLogicName);
                }
            }
            $listener->save();
        }
    }
    

    private function updateShutterEventsListener() 
    {
        $eqLogicName = $this->getName();
        log::add('shutters', 'debug', 'shutters::updateShutterEventsListener() : eqLogic => ' . $eqLogicName);
        $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutter' => $this->getId()));
        if (!is_object($listener)) {
            $listener = new listener();
        }
        $listener->setClass('shutters');
        $listener->setFunction('externalConditionsEvents');
        $listener->setOption(array('shutter' => $this->getId()));
        $listener->emptyEvent();
        $listener->save();
        log::add('shutters', 'debug', 'shutters::updateShutterEventsListener() : externalConditions events listener successfully added for => ' . $eqLogicName);

        $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutter' => $this->getId()));
        if (!is_object($listener)) {
            $listener = new listener();
        }
        $listener->setClass('shutters');
        $listener->setFunction('heliotropeZoneEvents');
        $listener->setOption(array('shutter' => $this->getId()));
        $listener->emptyEvent();
        $listener->save();
        log::add('shutters', 'debug', 'shutters::updateShutterEventsListener() : heliotropeZone events listener successfully added for => ' . $eqLogicName);

        $listener = listener::byClassAndFunction('shutters', 'shuttersGroupEvents', array('shutter' => $this->getId()));
        if (!is_object($listener)) {
            $listener = new listener();
        }
        $listener->setClass('shutters');
        $listener->setFunction('shuttersGroupEvents');
        $listener->setOption(array('shutter' => $this->getId()));
        $listener->emptyEvent();
        $listener->save();
        log::add('shutters', 'debug', 'shutters::updateShutterEventsListener() : shuttersGroup events listener successfully added for => ' . $eqLogicName);
    }

    private function removeShutterEventsListener()
    {
        $eqLogicName = $this->getName();
        log::add('shutters', 'debug', 'shutters::removeShutterEventsListener() : eqLogic => ' . $eqLogicName);
        $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutter' => $this->getId()));
        if (is_object($listener)) {
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeShutterEventsListener() : externalConditions events listener successfully removed for => ' . $eqLogicName);
        }

        $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutter' => $this->getId()));
        if (is_object($listener)) {
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeShutterEventsListener() : heliotropeZone events listener successfully removed for => ' . $eqLogicName);
        }

        $listener = listener::byClassAndFunction('shutters', 'shuttersGroupEvents', array('shutter' => $this->getId()));
        if (is_object($listener)) {
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeShutterEventsListener() : shuttersGroup events listener successfully removed for => ' . $eqLogicName);
        }
    }

    private function externalConditionsEvents()
    {
        # code...
    }

    private function heliotropeZoneEvents()
    {
        # code...
    }

    private function shuttersGroupEvents()
    {

    }

    private function isCmdExisting($_cmd = '')
    {
        $return = false;
        $cmd = str_replace('#', '', $_cmd);
        if (!empty($cmd)) {
            $cmdId=cmd::byId($cmd);
            $return = (is_object($cmdId)) ? true : false;
        }
        return $return;
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous 
     en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après 
     modification de variable de configuration
      public static function postConfig_<Variable>() {
      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant 
     modification de variable de configuration
      public static function preConfig_<Variable>() {
      }
     */

    /*     * **********************Getteur Setteur*************************** */
}
