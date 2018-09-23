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

class shuttersCmd extends cmd
{
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */
    
	public function execute($_options = array())
	{
		log::add('shutters', 'debug', 'shuttersCmd::execute() : ' . print_r($_options, true));
		$thisId = $this->getId();
		$thisLogicalId = $this->getLogicalId();
		$thisEqLogicId = $this->getEqLogic_id();
		$thisEqLogic = shutters::byId($thisEqLogicId);
		log::add('shutters', 'debug', 'shutters::execute() : receive cmd [' . $thisId . '][' . $thisLogicalId . '] from eqLogic [' . $thisEqLogicId . ']');

		switch ($thisLogicalId) {
			case 'heliotropeZone:dayModeHour':
				$thisEqLogic->setConfiguration('sunriseHour', $_options['slider']);
				$thisEqLogic->save();
				break;
				case 'heliotropeZone:nightModeHour':
				$thisEqLogic->setConfiguration('sunsetHour', $_options['slider']);
				$thisEqLogic->save();
				break;
				case 'heliotropeZone:season':
				$thisEqLogic->setConfiguration('season', $_options['select']);
				$thisEqLogic->save();
				break;
			
			default:
				# code...
				break;
		}
	}

    /*     * **********************Getteur Setteur*************************** */
}
