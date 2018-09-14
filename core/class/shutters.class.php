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
    public static function start()
    {
        log::add('shutters', 'debug', 'shutters::start()');
        shutters::updateEventsListener();
    }

    public static function stop()
    {
        log::add('shutters', 'debug', 'shutters::stop()');
    }

     public static function externalConditionsEvents($_option)
    {
        $shutterId = $_option['shutterId'];
        $shutter = eqlogic::byId($shutterId);
        $cmdId = $_option['event_id'];
        $cmdValue = $_option['value'];
        log::add('shutters', 'debug', print_r($_option, true));
        log::add('shutters', 'debug', 'shutters::externalConditionsEvents() : event received for shutter [' . $shutterId . '] from cmd [' . $cmdId . '] cmd value => ' . $cmdValue);

   }

    public static function heliotropeZoneEvents($_option)
    {
        $shutterId = $_option['shutterId'];
        $cmdId = $_option['event_id'];
        $cmdValue = $_option['value'];
        log::add('shutters', 'debug', print_r($_option, true));
        log::add('shutters', 'debug', 'shutters::heliotropeZoneEvents() : event received for shutter [' . $shutterId . '] from cmd [' . $cmdId . '] cmd value => ' . $cmdValue);
    }

    public static function shuttersGroupEvents($_option)
    {
        $shutterId = $_option['shutterId'];
        $cmdId = $_option['event_id'];
        $cmdValue = $_option['value'];
        log::add('shutters', 'debug', print_r($_option, true));
        log::add('shutters', 'debug', 'shutters::shuttersGroupEvents() : event received for shutter [' . $shutterId . '] from cmd [' . $cmdId . '] cmd value => ' . $cmdValue);

    }

    private static function updateEventsListener() 
    {
        foreach (eqLogic::byType('shutters', false) as $eqLogic) {
            if (!is_object($eqLogic) || $eqLogic->getConfiguration('eqType', null) !== 'shutter') {
                continue;
            }
        
            $eqLogicName = $eqLogic->getName();
            $eqLogicId = $eqLogic->getId();

            $conditionsEventListener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', ['shutterId' => $eqLogic->getId()]);
            if (!is_object($conditionsEventListener)) {
                $conditionsEventListener = new listener();
                $conditionsEventListener->setClass('shutters');
                $conditionsEventListener->setFunction('externalConditionsEvents');
                $conditionsEventListener->setOption(['shutterId' => $eqLogic->getId()]);
                $conditionsEventListener->emptyEvent();
                $conditionsEventListener->save();
                $conditionsEventListenerId = $conditionsEventListener->getId();
                log::add('shutters', 'debug', 'shutters::updateEventsListener() : external conditions events listener [' . $conditionsEventListenerId . ']  successfully created for shutter [' . $eqLogicName . ']');
            } else {
                $conditionsEventListenerId = $conditionsEventListener->getId();
            }

            $heliotropeEventListener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $eqLogic->getId()));
            if (!is_object($heliotropeEventListener)) {
                $heliotropeEventListener = new listener();
                $heliotropeEventListener->setClass('shutters');
                $heliotropeEventListener->setFunction('heliotropeZoneEvents');
                $heliotropeEventListener->setOption(array('shutterId' => $eqLogic->getId()));
                $heliotropeEventListener->emptyEvent();
                $heliotropeEventListener->save();
                log::add('shutters', 'debug', 'shutters::updateEventsListener() : heliotrope events listener [' . $heliotropeEventListenerId . ']  successfully created for shutter [' . $eqLogicName . ']');
            } else {
                $heliotropeEventListenerId = $heliotropeEventListener->getId();
            }
            
            if (!$eqLogic->getIsEnable()) {
                $conditionsEventListener->emptyEvent();
                $conditionsEventListener->save();
                log::add('shutters', 'debug', 'shutters::updateEventsListener() : external conditions events successfully removed from listener [' . $conditionsEventListenerId . '] for shutter [' . $eqLogicName . ']');
                $heliotropeEventListener->emptyEvent();
                $heliotropeEventListener->save();
                log::add('shutters', 'debug', 'shutters::updateEventsListener() : heliotrope events successfully removed from listener [' . $heliotropeEventListenerId . '] for shutter [' . $eqLogicName . ']');
            } else {
                $externalConditionsId = str_replace('#', '', $eqLogic->getConfiguration('externalConditionsId', null));
                if (!empty($externalConditionsId) && $externalConditionsId !== 'none') {
                    $externalConditionsEqLogic = eqLogic::byId($externalConditionsId);
                    if (is_object($externalConditionsEqLogic)) {
                        if ($externalConditionsEqLogic->getIsEnable()) {
                            $conditionsWithEvent = $externalConditionsEqLogic->getConfiguration('conditionsWithEvent', null);
                            foreach ($conditionsWithEvent as $cmdId) {
                                $cmd = cmd::byId($cmdId);
                                if (!is_object($cmd)) {
                                    log::add('shutters', 'debug', 'shutters::updateEventsListener() : cmd  [' . $cmdId  . '] configured in externalConditions [' . $externalConditionsId . '] doesn\'t exist');
                                    continue;
                                }
                                $conditionsEventListener->addEvent($cmdId);
                                log::add('shutters', 'debug', 'shutters::updateEventsListener() : cmd [' . $cmdId  . '] configured in externalConditions [' . $externalConditionsId . '] successfully added to listener [' . $conditionsEventListenerId . '] for shutter [' . $eqLogicName . ']');
                            }
                            $conditionsEventListener->save();
                        } else {
                            $conditionsEventListener->emptyEvent();
                            $conditionsEventListener->save();
                            log::add('shutters', 'debug', 'shutters::updateEventsListener() : external conditions events successfully removed from listener [' . $conditionsEventListenerId . '] for shutter [' . $eqLogicName . ']');
                        } 
                    } else {
                        $conditionsEventListener->emptyEvent();
                        $conditionsEventListener->save();
                        log::add('shutters', 'debug', 'shutters::updateEventsListener() : externalConditions [' . $externalConditionsId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                    }
                }

                $heliotropeZoneId = str_replace('#', '', $eqLogic->getConfiguration('heliotropeZoneId', null));
                if (!empty($heliotropeZoneId) && $heliotropeZoneId !== 'none') {
                    $heliotropeZoneEqLogic = eqLogic::byId($heliotropeZoneId);
                    if (is_object($heliotropeZoneEqLogic)) {
                        if ($heliotropeZoneEqLogic->getIsEnable()) {

                            $heliotropeId = str_replace('#', '', $heliotropeZoneEqLogic->getConfiguration('heliotrope', null));
                            if(!empty($heliotropeId) && $heliotropeId !== 'none'){
                                $heliotrope=eqlogic::byId($heliotropeId);
                                if(is_object($heliotrope)) {
                                    $heliotropeCmdLogicalId = ['altitude', 'azimuth360'];
                                    foreach ($heliotropeCmdLogicalId as $cmdLogicalId) {
                                        $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, $cmdLogicalId);
                                        if(is_object($cmd)) {
                                            $cmdId = $cmd->getId();
                                            $heliotropeEventListener->addEvent($cmdId);
                                            log::add('shutters', 'debug', 'shutters::updateEventsListener() : cmd [' . $cmdId  . '] from heliotrope [' . $heliotropeId . '] successfully added to listener [' . $heliotropeEventListenerId . '] for shutter [' . $eqLogicName . ']');
                                        } else {
                                            log::add('shutters', 'debug', 'shutters::updateEventsListener() : cmd [' . $cmdId  . '] from heliotrope [' . $heliotropeId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                                        }
                                    }
                                    $heliotropeEventListener->save();
                                } else {
                                    $heliotropeEventListener->emptyEvent();
                                    $heliotropeEventListener->save();
                                    log::add('shutters', 'debug', 'shutters::updateEventsListener() : heliotrope [' . $heliotropeId . '] configured in heliotropeZone [' . $heliotropeZoneId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                                }
                            } else {
                                $heliotropeEventListener->emptyEvent();
                                $heliotropeEventListener->save();
                                log::add('shutters', 'debug', 'shutters::updateEventsListener() : no heliotrope configured in heliotropeZone [' . $heliotropeZoneId . '] for shutter [' . $eqLogicName . ']');
                            }
                        } else {
                            $heliotropeEventListener->emptyEvent();
                            $heliotropeEventListener->save();
                            log::add('shutters', 'debug', 'shutters::updateEventsListener() : heliotrope events successfully removed from listener [' . $heliotropeEventListenerId . '] for shutter [' . $eqLogicName . ']');
                        } 
                    } else {
                        $heliotropeEventListener->emptyEvent();
                        $heliotropeEventListener->save();
                        log::add('shutters', 'debug', 'shutters::updateEventsListener() : heliotropeZone [' . $heliotropeZoneId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                    }
                }
    
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert()
    {
 
    }

    public function postInsert()
    {

    }

    public function preSave()
    {
        $thisEqType = $this->getConfiguration('eqType', null);
        $thisName = $this->getName();
        log::add('shutters', 'debug', 'shutters::preSave() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        switch ($eqType) {
            case 'externalConditions':
                shutters::updateEventsListener();
                break;
            case 'heliotropeZone':
                shutters::updateEventsListener();
                break;
            case 'shuttersGroup':
                break;
            case 'shutter':
                shutters::updateEventsListener();
                break;
            default:
                break;
        }

    }

    public function postSave()
    {
        $thisEqType = $this->getConfiguration('eqType', null);
        $thisName = $this->getName();
        log::add('shutters', 'debug', 'shutters::postSave() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        if(!empty($eqType)) {
            $this->loadCmdFromConfFile($eqType);
        }

        switch ($eqType) {
            case 'externalConditions':
                $this->getConditionWithEvent();
                break;
            case 'heliotropeZone':
               break;
            case 'shuttersGroup':
                break;
            case 'shutter':
                break;
            default:
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
        $thisEqType = $this->getConfiguration('eqType', null);
        $thisName = $this->getName();
        log::add('shutters', 'debug', 'shutters::preRemove() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        switch ($eqType) {
            case 'externalConditions':
                $this->removeEventsFromListener();
                break;
            case 'heliotropeZone':
                $this->removeEventsFromListener();
                break;
            case 'shuttersGroup':
                break;
            case 'shutter':
                $this->removeEventListener();
                break;
            default:
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
        $thisName = $this->getName();
        $file = dirname(__FILE__) . '/../config/devices/' . $_eqType . '.json';
        if (!is_file($file)) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : no commands configuration file to import for ['. $thisName . ']');
			return;
		}
		$content = file_get_contents($file);
		if (!is_json($content)) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file is not JSON formatted for ['. $thisName . ']');
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file isn\'t well formatted for ['. $thisName . ']');
			return;
		}

        foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $existingCmd) {
				if ((isset($command['logicalId']) && $existingCmd->getLogicalId() === $command['logicalId'])
				    || (isset($command['name']) && $existingCmd->getName() === $command['name'])) {
                    log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : cmd [' . $command['logicalId'] . '] already exist for ['. $thisName . ']');
                    $cmd = $existingCmd;
					break;
				}
            }
            /*
            if($this->getConfiguration('eqType', null) === 'externalConditions') {
                if(isset($command['configuration']['condition']) && empty($this->getConfiguration($command['configuration']['condition'], null))) {
                    if($cmd !== null || is_object($cmd)) {
                        $cmd->remove();
                        log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : command => ' . $command['logicalId'] . ' successfully removed for => '. $eqLogicName);
                    }
                    continue;
                }
            }
            */
			if ($cmd === null || !is_object($cmd)) {
				$cmd = new shuttersCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
                log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : cmd [' . $command['logicalId'] . '] successfully added for ['. $thisName . ']');
            }
        }
        log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands successfully imported for ['. $thisName . ']');
    }

    /**
     * 
     */
    private function getConditionWithEvent()
    {
        $thisName = $this->getName();
        $conditionsWithEvent = $this->getConfiguration('conditionsWithEvent', null);
        if (!is_array($conditionsWithEvent)) {
            $conditionsWithEvent = [];
        }
        $conditions = ['fireCondition', 'absenceCondition', 'presenceCondition', 'outdoorLuminosityCondition', 'outdoorTemperatureCondition', 'firstUserCondition', 'secondUserCondition'];
        foreach ($conditions as $condition) {
            $cmdId = str_replace('#', '', $this->getConfiguration($condition, null));
            if (empty($cmdId)) {
                continue;
            }
            $cmd = cmd::byId($cmdId);
            if (!is_object($cmd)) {
                log::add('shutters', 'debug', 'shutters::getConditionWithEvent() : cmd  [' . $cmdId  . '] configured in externalConditions [' . $thisName . '][' . $condition . '] doesn\'t exist');
                continue;
            }
            if (!in_array($cmdId, $conditionsWithEvent)) {
                $conditionsWithEvent[] = $cmdId;
            }
        }
        $this->setConfiguration('conditionsWithEvent', $conditionsWithEvent);
    }

    private function removeEventsFromListener()
    {
        $thisName = $this->getName();
        $thisId = $this->getId();
        $thisEqType = $this->getConfiguration('eqType', null);
        log::add('shutters', 'debug', 'shutters::removeEventsFromListener() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        foreach (eqLogic::byType('shutters', true) as $eqLogic) {
            if (!is_object($eqLogic) || $eqLogic->getConfiguration('eqType', null) !== 'shutter') {
                continue;
            }
            if ($thisId === $eqLogic->getConfiguration('externalConditionsId', null)) {
                $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutterId' => $eqLogic->getId()));
            }
            if ($thisId === $eqLogic->getConfiguration('heliotropeZoneId', null)) {
                $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $eqLogic->getId()));
            }
            $listener->emptyEvent();
            $listener->save();
            log::add('shutters', 'debug', 'shutters::removeEventsFromListener() : [' . $thisName . '] events successfully removed from listener [' . $listener->getId() . '] for shutter [' . $eqLogic->getName() . ']');
        }
    }

    private function removeEventListener()
    {
        $thisName = $this->getName();
        $thisEqType = $this->getConfiguration('eqType', null);
        log::add('shutters', 'debug', 'shutters::removeEventListener() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');
        $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutterId' => $this->getId()));
        if (is_object($listener)) {
            $listener->emptyEvent();
            $listener->save();
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeEventListener() : external conditions events listener [' . $listener->getId() . '] successfully removed for shutter [' . $thisName . ']');
        }

        $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $this->getId()));
        if (is_object($listener)) {
            $listener->emptyEvent();
            $listener->save();
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeEventListener() : heliotrope events listener [' . $listener->getId() . '] successfully removed for shutter [' . $thisName . ']');
        }
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
