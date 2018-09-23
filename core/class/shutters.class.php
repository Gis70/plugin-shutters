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
if (!class_exists('Season')) { require_once dirname(__FILE__) . '/../../core/php/season.class.php'; }

class shutters extends eqLogic
{
    /*     * *************************Attributs****************************** */

    private static $_externalConditions = ['fireCondition', 'absenceCondition', 'presenceCondition', 'outdoorLuminosityCondition', 'outdoorTemperatureCondition', 'firstUserCondition', 'secondUserCondition'];
    private static $_heliotropeHours = ['minSunriseHour' => 0600, 'maxSunriseHour' => 1200, 'minSunsetHour' => 1800, 'maxSunsetHour' => 2359];

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
        self::updateEventsManagement();
    }

    public static function stop()
    {
        log::add('shutters', 'debug', 'shutters::stop()');
    }

    private static function updateEventsManagement() 
    {
        $crossRef = [];

        foreach (eqLogic::byType('shutters', false) as $eqLogic) {
            if (!is_object($eqLogic) || $eqLogic->getConfiguration('eqType', null) !== 'shutter') {
                continue;
            }
        
            $eqLogicName = $eqLogic->getName();
            $eqLogicId = $eqLogic->getId();

            $conditionsEventListener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', ['shutterId' => $eqLogic->getId()]);
            $heliotropeEventListener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $eqLogic->getId()));
            $sunriseCron =cron::byClassAndFunction('shutters', 'sunriseEvent', array('shutterId' => $eqLogic->getId()));
            $sunsetCron =cron::byClassAndFunction('shutters', 'sunsetEvent', array('shutterId' => $eqLogic->getId()));
            $conditionsEventsHandler = ['conditionsEventListener' => $conditionsEventListener];
            $heliotropeEventsHandler = ['heliotropeEventListener' => $heliotropeEventListener, 'sunriseCron' => $sunriseCron, 'sunsetCron' => $sunsetCron];

            if ($eqLogic->getIsEnable()) {
                $externalConditionsId = str_replace('#', '', $eqLogic->getConfiguration('externalConditionsId', null));
                if (!empty($externalConditionsId) && $externalConditionsId !== 'none') {
                    $externalConditionsEqLogic = shutters::byId($externalConditionsId);
                    if (is_object($externalConditionsEqLogic)) {
                        if ($externalConditionsEqLogic->getIsEnable()) {
                            $crossRef[$externalConditionsId][] = $eqLogicId;
                            if (!is_object($conditionsEventListener)) {
                                $conditionsEventListener = new listener();
                                $conditionsEventListener->setClass('shutters');
                                $conditionsEventListener->setFunction('externalConditionsEvents');
                                $conditionsEventListener->setOption(['shutterId' => $eqLogic->getId()]);
                                $conditionsEventListener->emptyEvent();
                                $conditionsEventListener->save();
                                $conditionsEventListenerId = $conditionsEventListener->getId();
                                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : external conditions events listener [' . $conditionsEventListenerId . '] successfully created for shutter [' . $eqLogicName . ']');
                            } else {
                                $conditionsEventListener->emptyEvent();
                                $conditionsEventListener->save();
                                $conditionsEventListenerId = $conditionsEventListener->getId();
                            }
                            foreach (self::$_externalConditions as $condition) {
                                $cmdId = str_replace('#', '', $externalConditionsEqLogic->getConfiguration($condition, null));
                                if (!empty($cmdId)) {
                                    $cmd = cmd::byId($cmdId);
                                    if (is_object($cmd)) {
                                        $conditionsEventListener->addEvent($cmdId);
                                        $conditionManagement = $eqLogic->getCmd('info', 'shutter:' . $condition . 'Status')->execCmd();
                                        if ($conditionManagement !== 'Enable' && $conditionManagement !== 'Disable' ) {
                                            $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', 'Enable');
                                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() :[' . $condition  . '] management set to [Enable] for shutter [' . $eqLogicName . ']');
                                        }
                                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() : cmd [' . $cmdId  . '] configured in externalConditions [' . $externalConditionsId . '] successfully added to listener [' . $conditionsEventListenerId . '] for shutter [' . $eqLogicName . ']');
                                    } else {
                                        $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() : cmd  [' . $cmdId  . '] configured in externalConditions [' . $externalConditionsId . '] doesn\'t exist');
                                    }
                                } else {
                                    $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                                }
                            }
                            $conditionsEventListener->save();
                        } else {
                            self::removeEventsHandler($conditionsEventsHandler);
                            foreach (self::$_externalConditions as $condition) {
                                $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                            }    
                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : externalConditions [' . $externalConditionsId . '] isn\'t activated for shutter [' . $eqLogicName . ']');
                        } 
                    } else {
                        self::removeEventsHandler($conditionsEventsHandler);
                        foreach (self::$_externalConditions as $condition) {
                            $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                        }    
                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() : externalConditions [' . $externalConditionsId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                    }
                } else {
                    self::removeEventsHandler($conditionsEventsHandler);
                    foreach (self::$_externalConditions as $condition) {
                        $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                    }    
                }

                $heliotropeZoneId = str_replace('#', '', $eqLogic->getConfiguration('heliotropeZoneId', null));
                if (!empty($heliotropeZoneId) && $heliotropeZoneId !== 'none') {
                   $heliotropeZoneEqLogic = shutters::byId($heliotropeZoneId);
                    if (is_object($heliotropeZoneEqLogic)) {
                        if ($heliotropeZoneEqLogic->getIsEnable()) {
                            $crossRef[$heliotropeZoneId][] = $eqLogicId;
                            if (!is_object($heliotropeEventListener)) {
                                $heliotropeEventListener = new listener();
                                $heliotropeEventListener->setClass('shutters');
                                $heliotropeEventListener->setFunction('heliotropeZoneEvents');
                                $heliotropeEventListener->setOption(array('shutterId' => $eqLogic->getId()));
                                $heliotropeEventListener->emptyEvent();
                                $heliotropeEventListener->save();
                                $heliotropeEventListenerId = $heliotropeEventListener->getId();
                                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : heliotrope events listener [' . $heliotropeEventListenerId . '] successfully created for shutter [' . $eqLogicName . ']');
                            } else {
                                $heliotropeEventListener->emptyEvent();
                                $heliotropeEventListener->save();
                                $heliotropeEventListenerId = $heliotropeEventListener->getId();
                            }
                            if (!is_object($sunriseCron)) {
                                $sunriseCron = new cron();
                                $sunriseCron->setClass('shutters');
                                $sunriseCron->setFunction('sunriseEvent');
                                $sunriseCron->setOption(array('shutterId' => $eqLogic->getId()));
                                $sunriseCron->setDeamon(0);
                                $sunriseCron->setOnce(0);
                                $sunriseCron->setTimeout(2);
                                $sunriseCron->setSchedule('* * * * * *');
                                $sunriseCron->save();
                                $sunriseCronId = $sunriseCron->getId();
                                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : sunrise cron [' . $sunriseCronId . '] successfully created for shutter [' . $eqLogicName . ']');
                            }else{
                                $sunriseCron->setSchedule('* * * * * *');
                                $sunriseCron->save();
                                $sunriseCronId = $sunriseCron->getId();
                            }
                            if (!is_object($sunsetCron)) {
                                $sunsetCron = new cron();
                                $sunsetCron->setClass('shutters');
                                $sunsetCron->setFunction('sunsetEvent');
                                $sunsetCron->setOption(array('shutterId' => $eqLogic->getId()));
                                $sunsetCron->setDeamon(0);
                                $sunsetCron->setOnce(0);
                                $sunsetCron->setTimeout(2);
                                $sunsetCron->setSchedule('* * * * * *');
                                $sunsetCron->save();
                                $sunsetCronId = $sunsetCron->getId();
                                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : sunset cron [' . $sunsetCronId . '] successfully created for shutter [' . $eqLogicName . ']');
                            }else{
                                $sunsetCron->setSchedule('* * * * * *');
                                $sunsetCron->save();
                                $sunsetCronId = $sunsetCron->getId();
                            }

                            $heliotropeSunriseHour = 0000;
                            $heliotropeSunsetHour = 2400;

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
                                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : cmd [' . $cmdId  . '] from heliotrope [' . $heliotropeId . '] successfully added to listener [' . $heliotropeEventListenerId . '] for shutter [' . $eqLogicName . ']');
                                        } else {
                                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : cmd [' . $cmdId  . '] from heliotrope [' . $heliotropeId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                                        }
                                    }
                                    $heliotropeEventListener->save();
                                    switch ($heliotropeZoneEqLogic->getConfiguration('dawnType', null)) {
                                        case 'astronomicalDawn':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'aubeast');
                                            break;
                                        case 'nauticalDawn':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'aubenau');
                                            break;
                                        case 'civilDawn':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'aubeciv');
                                            break;
                                        case 'sunrise':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'sunrise');
                                            break;
                                        default:
                                            $cmd = null;
                                            break;
                                    }
                                    if (is_object($cmd)) {
                                        $heliotropeSunriseHour = $cmd->execCmd();
                                    }
                                    
                                    switch ($heliotropeZoneEqLogic->getConfiguration('duskType', null)) {
                                        case 'astronomicalDusk':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'crepast');
                                            break;
                                        case 'nauticalDusk':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'crepnau');
                                            break;
                                        case 'civilDusk':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'crepciv');
                                            break;
                                        case 'sunset':
                                            $cmd = cmd::byEqLogicIdAndLogicalId($heliotropeId, 'sunset');
                                            break;
                                        default:
                                            $cmd = null;
                                            break;
                                    }
                                    if (is_object($cmd)) {
                                        $heliotropeSunsetHour = $cmd->execCmd();
                                    }

                                    $conditionManagement = $eqLogic->getCmd('info', 'shutter:sunriseConditionStatus')->execCmd();
                                    if ($conditionManagement !== 'Enable' && $conditionManagement !== 'Disable' ) {
                                        $eqLogic->checkAndUpdateCmd('shutter:sunriseConditionStatus', 'Enable');
                                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() :[sunriseCondition] management set to [Enable] for shutter [' . $eqLogicName . ']');
                                    }
                                    $conditionManagement = $eqLogic->getCmd('info', 'shutter:sunsetConditionStatus')->execCmd();
                                    if ($conditionManagement !== 'Enable' && $conditionManagement !== 'Disable' ) {
                                        $eqLogic->checkAndUpdateCmd('shutter:sunsetConditionStatus', 'Enable');
                                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() :[sunsetCondition] management set to [Enable] for shutter [' . $eqLogicName . ']');
                                    }
                                    $conditionManagement = $eqLogic->getCmd('info', 'shutter:azimutConditionStatus')->execCmd();
                                    if ($conditionManagement !== 'Enable' && $conditionManagement !== 'Disable' ) {
                                        $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', 'Enable');
                                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() :[azimutCondition] management set to [Enable] for shutter [' . $eqLogicName . ']');
                                    }
                                } else {
                                    $sunriseCron->remove();
                                    log::add('shutters', 'debug', 'shutters::updateEventsManagement() : heliotrope [' . $heliotropeId . '] configured in heliotropeZone [' . $heliotropeZoneId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                                }
                            } else {
                                self::removeEventsHandler(['heliotropeEventListener' => $heliotropeEventListener]);
                                $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', '');
                                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : no heliotrope configured in heliotropeZone [' . $heliotropeZoneId . '] for shutter [' . $eqLogicName . ']');
                            }

                            $sunriseHour = $heliotropeZoneEqLogic->getConfiguration('sunriseHour', null);
                            if (!is_numeric($sunriseHour) || $sunriseHour < self::$_heliotropeHours['minSunriseHour'] || $sunriseHour > self::$_heliotropeHours['maxSunriseHour']) {
                                $sunriseHour = self::$_heliotropeHours['maxSunriseHour'];
                            }
                            if ($heliotropeZoneEqLogic->getConfiguration('sunriseHourType', null) === 'min' && $heliotropeSunriseHour > $sunriseHour) {
                                    $sunriseHour = $heliotropeSunriseHour;
                            }
                            $schedule = substr($sunriseHour, -2) . ' ' . substr($sunriseHour, 0, -2) . ' * * * *';
                            $sunriseCron->setSchedule($schedule);
                            $sunriseCron->setEnable(1);
                            $sunriseCron->save();
                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : sunrise cron [' . $sunriseCronId . '] set to  [' . $schedule . '] for shutter [' . $eqLogicName . ']');

                            $sunsetHour = $heliotropeZoneEqLogic->getConfiguration('sunsetHour', null);
                            if (!is_numeric($sunsetHour) || $sunsetHour < self::$_heliotropeHours['minSunsetHour'] || $sunsetHour > self::$_heliotropeHours['maxSunsetHour']) {
                                $sunsetHour = self::$_heliotropeHours['minSunsetHour'];
                            }
                            if ($heliotropeZoneEqLogic->getConfiguration('sunsetHourType', null) === 'max' && $heliotropeSunsetHour < $sunsetHour) {
                                $sunsetHour = $heliotropeSunsetHour;
                            }
                            $schedule = substr($sunsetHour, -2) . ' ' . substr($sunsetHour, 0, -2) . ' * * * *';
                            $sunsetCron->setSchedule($schedule);
                            $sunsetCron->setEnable(1);
                            $sunsetCron->save();
                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : sunset cron [' . $sunsetCronId . '] set to  [' . $schedule . '] for shutter [' . $eqLogicName . ']');

                            $hour = date("Hi");
                            if ($hour > $sunriseHour && $hour < $sunsetHour) {
                                $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', 'Day');
                                log::add('shutters', 'info', 'shutters::updateEventsManagement() : day management activated for shutter [' . $eqLogicName . ']');
                            } else {
                                $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', 'Night');
                                log::add('shutters', 'info', 'shutters::updateEventsManagement() : night management activated for shutter [' . $eqLogicName . ']');
                            }

                        } else {
                            self::removeEventsHandler($heliotropeEventsHandler);
                            $eqLogic->checkAndUpdateCmd('shutter:sunriseConditionStatus', '');
                            $eqLogic->checkAndUpdateCmd('shutter:sunsetConditionStatus', '');
                            $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', '');
                            $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', '');
                            log::add('shutters', 'debug', 'shutters::updateEventsManagement() : heliotropeZone [' . $heliotropeZoneId . '] isn\'t activated for shutter [' . $eqLogicName . ']');
                        } 
                    } else {
                        self::removeEventsHandler($heliotropeEventsHandler);
                        $eqLogic->checkAndUpdateCmd('shutter:sunriseConditionStatus', '');
                        $eqLogic->checkAndUpdateCmd('shutter:sunsetConditionStatus', '');
                        $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', '');
                        $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', '');
                        log::add('shutters', 'debug', 'shutters::updateEventsManagement() : heliotropeZone [' . $heliotropeZoneId . '] doesn\'t exist for shutter [' . $eqLogicName . ']');
                    }
                } else {
                    self::removeEventsHandler($heliotropeEventsHandler);
                    $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', '');
                    $eqLogic->checkAndUpdateCmd('shutter:sunriseConditionStatus', '');
                    $eqLogic->checkAndUpdateCmd('shutter:sunsetConditionStatus', '');
                    $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', '');
             }
            } else {
                self::removeEventsHandler($heliotropeEventsHandler);
                foreach (self::$_externalConditions as $condition) {
                    $eqLogic->checkAndUpdateCmd('shutter:' . $condition . 'Status', '');
                }    
                $eqLogic->checkAndUpdateCmd('shutter:sunriseConditionStatus', '');
                $eqLogic->checkAndUpdateCmd('shutter:sunsetConditionStatus', '');
                $eqLogic->checkAndUpdateCmd('shutter:azimutConditionStatus', '');
                $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', '');
                log::add('shutters', 'debug', 'shutters::updateEventsManagement() : shutter [' . $eqLogicName . '] isn\'t activated');
            } 
        }
        foreach ($crossRef as $eqLogicId => $usedBy) {
            $eqLogic = shutters::byId($eqLogicId);
            $eqLogic->setConfiguration('usedBy', $usedBy);
            $eqLogic->save(true);
        }
    }

    public static function externalConditionsEvents($_options)
    {
        $shutterId = $_options['shutterId'];
        $shutter = shutters::byId($shutterId);
        $cmdId = $_options['event_id'];
        $cmdValue = $_options['value'];
        log::add('shutters', 'debug', 'shutters::externalConditionsEvents() : event received for shutter [' . $shutterId . '] from cmd [' . $cmdId . '] cmd value => ' . $cmdValue);
        //shutters::main($shutterId);
        self::getExternalConditions($shutter);
   }

    public static function heliotropeZoneEvents($_options)
    {
        $shutterId = $_options['shutterId'];
        $shutter = shutters::byId($shutterId);
        $cmdId = $_options['event_id'];
        $cmdValue = $_options['value'];
        log::add('shutters', 'debug', print_r($_options, true));
        log::add('shutters', 'debug', 'shutters::heliotropeZoneEvents() : event received for shutter [' . $shutterId . '] from cmd [' . $cmdId . '] cmd value => ' . $cmdValue);
        self::getHeliotropeConditions($shutter);
    }

    public static function sunriseEvent($_options)
    {
        $eqLogic = shutters::byId($_options['shutterId']);
        $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', 'Day');
        log::add('shutters', 'info', 'shutters::sunriseEvent() : day management activated for shutter [' . $_options['shutterId'] . ']');
    }

    public static function sunsetEvent($_options)
    {
        $eqLogic = shutters::byId($_options['shutterId']);
        $eqLogic->checkAndUpdateCmd('shutter:cycleDayNight', 'Night');
        log::add('shutters', 'info', 'shutters::sunriseEvent() : night management activated for shutter [' . $_options['shutterId'] . ']');
    }

    private static function main($_shutterId)
    {
        if (empty($_shutterId)) {
            return;
        }
        $shutter = shutters::byId($_shutterId);
        if (!is_object($shutter)) {
            log::add('shutters', 'debug', 'shutters::main() : shutter doesn\'t exist');
            return;
        }

        $shutterName = $shutter->getName();

        if (!$shutter->getIsEnable()) {
            log::add('shutters', 'debug', 'shutters::main() : shutter [' . $shutterName . '] isn\'t activated');
            return;
        }

        $activeCondition = '';
        $externalConditions = self:: getExternalConditions($shutter);
        $heliotropeConditions = self::getHeliotropeConditions($shutter);
        $positionSetPoint = $shutter->getCmd('info', 'shutter:positionSetPoint')->execCmd();
        $cycleDayNight = $shutter->getCmd('info', 'shutter:cycleDayNight')->execCmd();
    
        $primaryConditions = explode(',', $externalConditions['primaryConditionsPriority']);
        if (!empty($primaryConditions)) {
            foreach ($primaryConditions as $condition) {
                if ($shutter->getCmd('info', 'shutter:' . $condition . 'Status')->execCmd() === 'enable') {
                    if ($externalConditions[$condition]['status'] === true) {
                        switch ($condition) {
                            case 'fireCondition':
                                $activeCondition = 'fireCondition';
                                $positionSetPoint = 100;
                                break;
                            case 'absenceCondition':
                                $activeCondition = 'absenceCondition';
                                $positionSetPoint = 0;
                                break;
                            case 'firstUserCondition':
                                $activeCondition = 'firstUserCondition';
                                $positionSetPoint = intval($externalConditions[$condition]['positionSetPoint']);
                                break;
                            case 'secondUserCondition':
                                $activeCondition = 'secondUserCondition';
                                $positionSetPoint = intval($externalConditions[$condition]['positionSetPoint']);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }
       
    }

    private static function removeEventsHandler(Array $_EventsHandler = []) {
        if (isset($_EventsHandler['conditionsEventListener'])) {
            if (is_object($conditionsEventListener)) {
                $_EventsHandler['conditionsEventListener']->emptyEvent();
                $_EventsHandler['conditionsEventListener']->save();
                $_EventsHandler['conditionsEventListener']->remove();
            }
        }
        if (isset($_EventsHandler['heliotropeEventListener'])) {
            if (is_object($_EventsHandler['heliotropeEventListener'])) {
            $_EventsHandler['heliotropeEventListener']->emptyEvent();
            $_EventsHandler['heliotropeEventListener']->save();
            $_EventsHandler['heliotropeEventListener']->remove();
            }
        }
        if (isset($_EventsHandler['sunriseCron'])) {
            if (is_object($_EventsHandler['sunriseCron'])) {
                $_EventsHandler['sunriseCron']->remove();
            }
        }
        if (isset($_EventsHandler['sunriseCron'])) {
            if (is_object($_EventsHandler['sunriseCron'])) {
                $_EventsHandler['sunriseCron']->remove();
            }
        }
    }

    private static function getExternalConditions(object $_shutter)
    {
        $return = [];

        $eqLogicId = str_replace('#', '', $_shutter->getConfiguration('externalConditionsId', null));
        if (!empty($eqLogicId) && $eqLogicId !== 'none') {
            $eqLogic = shutters::byId($eqLogicId);
            if (is_object($eqLogic)) {
                if ($eqLogic->getIsEnable()) {
                    foreach (self::$_externalConditions as $condition) {
                        $cmdId = str_replace('#', '', $eqLogic->getConfiguration($condition, null));
                        if (!empty($cmdId)) {
                            $cmd = cmd::byId($cmdId);
                            if (is_object($cmd)) {
                                switch ($condition) {
                                    case 'fireCondition':
                                    case 'absenceCondition':
                                    case 'presenceCondition':
                                        if ($cmd->execCmd() === $eqLogic->getConfiguration($condition . 'Status', null)) {
                                            $return[$condition] = ['status' => true];
                                        } else {
                                            $return[$condition] = ['status' => false];
                                        }
                                        break;
                                    case 'outdoorLuminosityCondition':
                                        $luminosity = intval($cmd->execCmd());
                                        $luminosityMin = intval($eqLogic->getConfiguration('outdoorLuminosityThreshold', 0));
                                        $luminosityMax = $luminosityMin + intval($eqLogic->getConfiguration('outdoorLuminosityHysteresis', 0));
                                        $luminosityConditionStatus = $eqLogic->getConfiguration('outdoorLuminosityConditionCache', false);
                                        if ($luminosity < $luminosityMin || ($luminosityConditionStatus && $luminosity <= $luminosityMax)) {
                                            $return[$condition] = ['status' => true];
                                            $eqLogic->setConfiguration('outdoorLuminosityConditionCache', true);
                                            $eqLogic->save(true);
                                        } else {
                                            $return[$condition] = ['status' => false];
                                            $eqLogic->setConfiguration('outdoorLuminosityConditionCache', false);
                                            $eqLogic->save(true);
                                        }
                                        break;
                                    case 'outdoorTemperatureCondition':
                                        $temperature = intval($cmd->execCmd());
                                        $temperatureMin = intval($eqLogic->getConfiguration('outdoorTemperatureThreshold', 0));
                                        $temperatureMax = $temperatureMin + intval($eqLogic->getConfiguration('outdoorTemperatureHysteresis', 1));
                                        $temperatureConditionStatus = $eqLogic->getConfiguration('outdoorTemperatureConditionCache', false);
                                        if ($temperature < $temperatureMin || ($temperatureConditionStatus && $temperature <= $temperatureMax)) {
                                            $return[$condition] = ['status' => true];
                                            $eqLogic->setConfiguration('outdoorTemperatureConditionCache', true);
                                            $eqLogic->save(true);
                                        } else {
                                            $return[$condition] = ['status' => false];
                                            $eqLogic->setConfiguration('outdoorTemperatureConditionCache', false);
                                            $eqLogic->save(true);
                                        }
                                        break;
                                    case 'firstUserCondition':
                                    case 'secondUserCondition':
                                        if ($cmd->execCmd() === $eqLogic->getConfiguration($condition . 'Status', null)) {
                                            $return[$condition] = ['status' => true, 'positionSetPoint' => $eqLogic->getConfiguration($condition . 'Action', null),
                                            'name' => $eqLogic->getConfiguration($condition . 'Name', null)];
                                        } else {
                                            $return[$condition] = ['status' => false];
                                        }
                                        break;
                                    default:
                                        break;
                                } 
                            }
                        }
                    }
                }
            }
        }
        log::add('shutters', 'debug', 'shutters::getExternalConditions() : ' . print_r($return, true));
        return $return;
    }

    private static function getHeliotropeConditions(object $_shutter)
    {
        $return = [];

        $eqLogicId = str_replace('#', '', $_shutter->getConfiguration('heliotropeZoneId', null));
        if (!empty($eqLogicId) && $eqLogicId !== 'none') {
            $eqLogic = shutters::byId($eqLogicId);
            if (is_object($eqLogic)) {
                if ($eqLogic->getIsEnable()) {
                    $heliotropeId = str_replace('#', '', $eqLogic->getConfiguration('heliotrope', null));
                    if(!empty($heliotropeId) && $heliotropeId !== 'none'){
                        $heliotrope=eqlogic::byId($heliotropeId);
                        if(is_object($heliotrope)) {
                            $cmd = $heliotrope->getCmd('altitude');
                            if (is_object($cmd)) {
                                $return['altitude'] = ['status' => intval($cmd->execCmd())];
                            }
                            $cmd = $heliotrope->getCmd('azimuth360');
                            if (is_object($cmd)) {
                                $return['azimuth360'] = ['status' => intval($cmd->execCmd())];
                            }
                        }
                    }
                    $return['season'] = $eqLogic->getConfiguration('season', null);
                    if ($eqLogic->getConfiguration('wallAngleUnit', 'deg') === 'deg') {
                        $return['wallAngle'] = ['status' => intval($eqLogic->getConfiguration('wallAngle', 0))];
                    } else {
                        $return['wallAngle'] = ['status' => intval($eqLogic->getConfiguration('wallAngle', 0)) * 0.9];
                    }
                }
            }
        }
        log::add('shutters', 'debug', 'shutters::getHeliotropeConditions() : ' . print_r($return, true));
        return $return;
    }

    private static function checkSeason()
    {
        $season = new Season();
        return $season->getSeason();
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

        switch ($thisEqType) {
            case 'externalConditions':
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

    public function postSave()
    {
        $thisEqType = $this->getConfiguration('eqType', null);
        $thisName = $this->getName();
        log::add('shutters', 'debug', 'shutters::postSave() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        if(!empty($thisEqType)) {
            $this->loadCmdFromConfFile($thisEqType);
        }

        switch ($thisEqType) {
            case 'externalConditions':
                self::updateEventsManagement();
                break;
            case 'heliotropeZone':
                self::updateEventsManagement();
                break;
            case 'shuttersGroup':
                break;
            case 'shutter':
                self::updateEventsManagement();
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

        switch ($thisEqType) {
            case 'externalConditions':
                $this->removeEvents();
                break;
            case 'heliotropeZone':
                $this->removeEvents();
                break;
            case 'shuttersGroup':
                break;
            case 'shutter':
                $this->removeEventsListener();
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
        $thisEqType = $this->getConfiguration('eqType', null);

        if (empty($thisEqType)) {
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
			//log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : no commands configuration file to import for ['. $thisName . ']');
			return;
		}
		$content = file_get_contents($file);
		if (!is_json($content)) {
			//log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file is not JSON formatted for ['. $thisName . ']');
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			//log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands configuration file isn\'t well formatted for ['. $thisName . ']');
			return;
		}

        foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $existingCmd) {
				if ((isset($command['logicalId']) && $existingCmd->getLogicalId() === $command['logicalId'])
				    || (isset($command['name']) && $existingCmd->getName() === $command['name'])) {
                    //log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : cmd [' . $command['logicalId'] . '] already exist for ['. $thisName . ']');
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
                //log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : cmd [' . $command['logicalId'] . '] successfully added for ['. $thisName . ']');
            }
        }
        //log::add('shutters', 'debug', 'shutters::loadCmdFromConfFile() : commands successfully imported for ['. $thisName . ']');
    }

    private function removeEvents()
    {
        $thisName = $this->getName();
        $thisId = $this->getId();
        $thisEqType = $this->getConfiguration('eqType', null);
        log::add('shutters', 'debug', 'shutters::removeEvents() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');

        foreach (eqLogic::byType('shutters', false) as $eqLogic) {
            if (!is_object($eqLogic) || $eqLogic->getConfiguration('eqType', null) !== 'shutter') {
                continue;
            }
            if ($thisId === $eqLogic->getConfiguration('externalConditionsId', null)) {
                $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutterId' => $eqLogic->getId()));
                if (is_object($listener)) {
                    $listener->emptyEvent();
                    $listener->save();
                    $listener->remove();
                    log::add('shutters', 'debug', 'shutters::removeEvents() : external conditions events listener [' . $listener->getId() . '] successfully removed for shutter [' . $eqLogic->getName() . ']');
                }
            }
            if ($thisId === $eqLogic->getConfiguration('heliotropeZoneId', null)) {
                $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $eqLogic->getId()));
                if (is_object($listener)) {
                    $listener->emptyEvent();
                    $listener->save();
                    $listener->remove();
                    log::add('shutters', 'debug', 'shutters::removeEvents() : heliotrope events listener [' . $listener->getId() . '] successfully removed for shutter [' . $eqLogic->getName() . ']');
                }
                $cron =cron::byClassAndFunction('shutters', 'sunriseEvent', array('shutterId' => $eqLogic->getId()));
                if (is_object($cron)) {
                    $cron->remove();
                    log::add('shutters', 'debug', 'shutters::removeEvents() : sunrise cron [' . $cron->getId() . '] successfully removed for shutter [' . $eqLogic->getName() . ']');
                }
                $cron =cron::byClassAndFunction('shutters', 'sunsetEvent', array('shutterId' => $eqLogic->getId()));
                if (is_object($cron)) {
                    $cron->remove();
                    log::add('shutters', 'debug', 'shutters::removeEvents() : sunset cron [' . $cron->getId() . '] successfully removed for shutter [' . $eqLogic->getName() . ']');
                }
            }
            log::add('shutters', 'debug', 'shutters::removeEvents() : [' . $thisName . '] events successfully removed for shutter [' . $eqLogic->getName() . ']');
        }
    }

    private function removeEventsListener()
    {
        $thisName = $this->getName();
        $thisEqType = $this->getConfiguration('eqType', null);
        log::add('shutters', 'debug', 'shutters::removeEventListener() : eqLogic[' . $thisName . '] eqType [' . $thisEqType . ']');
        $listener = listener::byClassAndFunction('shutters', 'externalConditionsEvents', array('shutterId' => $this->getId()));
        if (is_object($listener)) {
            $listener->emptyEvent();
            $listener->save();
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeEventsListener() : external conditions events listener [' . $listener->getId() . '] successfully removed for shutter [' . $thisName . ']');
        }

        $listener = listener::byClassAndFunction('shutters', 'heliotropeZoneEvents', array('shutterId' => $this->getId()));
        if (is_object($listener)) {
            $listener->emptyEvent();
            $listener->save();
            $listener->remove();
            log::add('shutters', 'debug', 'shutters::removeEventsListener() : heliotrope events listener [' . $listener->getId() . '] successfully removed for shutter [' . $thisName . ']');
        }

        $cron = cron::byClassAndFunction('shutters', 'sunriseEvent', array('shutterId' => $this->getId()));
        if (is_object($cron)) {
            $cron->remove();
            log::add('shutters', 'debug', 'shutters::removeEventsListener() : sunrise cron [' . $cron->getId() . '] successfully removed for shutter [' . $thisName . ']');
        }

        $cron = cron::byClassAndFunction('shutters', 'sunsetEvent', array('shutterId' => $this->getId()));
        if (is_object($cron)) {
            $cron->remove();
            log::add('shutters', 'debug', 'shutters::removeEventsListener() : sunset cron [' . $cron->getId() . '] successfully removed for shutter [' . $thisName . ']');
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
