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

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once 'shuttersCmd.class.php';
require_once 'shuttersDigitalInfo.class.php';
require_once 'shuttersAnalogInfo.class.php';
if (!class_exists('Season')) {
    require_once dirname(__FILE__) . '/../../core/php/season.class.php';
}

class shutters extends eqLogic
{

    public static function start()
    {
        log::add('shutters', 'info', 'shutters::start() -> Plugin shutters starting');
    }

    public static function stop()
    {
        log::add('shutters', 'info', 'shutters::stop() -> Plugin shutters stopping');
    }


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
    }

    public function preUpdate()
    {
    }

    public function postUpdate()
    {
    }

    public function preRemove()
    {
    }
        
    public function postRemove()
    {
    }


}
