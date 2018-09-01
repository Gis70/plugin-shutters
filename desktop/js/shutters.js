/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

function printEqLogic(_eqLogic) {
    
    console.log('printEqLogic()');

    if (!isset(_eqLogic)) {
        var _eqLogic = {configuration: {}};
    }
    
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }

    if ($('[data-l1key=configuration][data-l2key=eqType]').val() !== null) {
        $('[data-l1key=configuration][data-l2key=eqType]').attr('disabled', true);
    }

    displaySettingsPanels(_eqLogic);
    displayCommandsPanels(_eqLogic);
    
    switch(_eqLogic.configuration.eqType) {
        case 'externalConditions':
            displayPrimaryConditionsList(_eqLogic);
            break;
        case 'heliotropeZone':
            drawHeliotropePlan();
            drawWallPlan();
            break;
        default:
            break;
    }
        
    $("#cmdTable").sortable({items: ".cmd", axis: "y", tolerance: "intersect", containment: "#cmdTable", placeholder: "ui-state-highlight", forcePlaceholderSize: true, cursor: "move"});
        
    $('#settingsPanels').setValues(_eqLogic, '.eqLogicAttr'); 
    initEvents();
}

function saveEqLogic(_eqLogic) {

    console.log('saveEqLogic()');


   	return _eqLogic;
}

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="configuration" data-l2key="description">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="type"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="subType"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    }
    if (init(_cmd.type) == 'info') {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="remove"><i class="fa fa-minus-circle"></i></a>';
    tr += '</td>';
    tr += '</tr>';

    $('#cmdTable tbody').append(tr);
    $('#cmdTable tbody tr:last').setValues(_cmd, '.cmdAttr');
}

/**
 * Hide tooltip attach to cursor
 */
function hideTooltip () {
    $('.cursor-tooltip').css('visibility', 'hidden');
}

/**
 * Display tooltip attach to cursor
 * @param {string} _message message to display in tooltip
 */
function displayTooltip (_message = '') {
    $('.cursor-tooltip').html(_message).css('visibility', 'visible');
}

/**
 * Display primary conditions list 
 * @param {object} _eqLogic
 */
function displayPrimaryConditionsList(_eqLogic) {
    var primaryConditionsList =[['fireDetectionCondition', '{{Détection incendie}}'], [ 'absenceCondition','{{Absence}}'], [ 'firstUserCondition','{{Condition 1 utilisateur}}'], [ 'secondUserCondition','{{Condition 2 utilisateur}}']];
    var primaryConditionsPriority = ['fireDetectionCondition', 'absenceCondition', 'firstUserCondition', 'secondUserCondition'];

    if (_eqLogic.configuration.primaryConditionsPriority !== undefined && _eqLogic.configuration.primaryConditionsPriority !== null
    && _eqLogic.configuration.primaryConditionsPriority !== '') {
        primaryConditionsPriority = _eqLogic.configuration.primaryConditionsPriority.split(',');
      } 

    $('#primaryConditionsList').find('li').each(function(index){
        for(i = 0; i<primaryConditionsPriority.length; i++){
            if (primaryConditionsPriority[index] === primaryConditionsList[i][0]){
                $(this).attr('data-name',primaryConditionsList[i][0]).append(primaryConditionsList[i][1]);
            }
        }
    });

    $('#primaryConditionsList').sortable({
        handle: ".fa",
        items: "> li",
        axis: "x",
        distance: 10,
        tolerance: "intersect",
        containment: ".conditionsList",
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        cursor: "move",
        stop: function(event, ui){
            $('[data-l1key=configuration][data-l2key=primaryConditionsPriority]').val($('#primaryConditionsList').sortable('toArray', {attribute: 'data-name'}));
        }
    });
}

/**
 * Update angle range according to angle unit
 */
function updateAngleRange () {
    var wallAngle = $('[data-l1key=configuration][data-l2key=wallAngle]');
    if ($('[data-l1key=configuration][data-l2key=wallAngleUnit]').val() == 'gon') {
        wallAngle.attr('max', 400);
        wallAngle.prev().html('0gon');
        wallAngle.next().html('400gon');
   } else {
        wallAngle.attr('max', 360);
        wallAngle.prev().html('0°');
        wallAngle.next().html('360°');
   }
}

/**
 * Display setting fieldset corresponding to object type
 * @param {string} _settingGroup 
 * @param {string} _settingType
 */
function displaySettings (_settingGroup = null, _settingType = null) {
    if (_settingGroup !== null && _settingType !== null) {
        $('fieldset[data-settinggroup=' + _settingGroup + ']').css('display', 'none');
        $('fieldset[data-settinggroup=' + _settingGroup + '][data-settingtype~=' + _settingType + ']').css('display', 'block');
    }
}

/**
 * Update min and max value for input type range
 */
function updateInputRangeMinMax () {
    $('input[type=range]').each(function () {
        var element = $(this);
        element.prev('span.input-group-addon').html(element.attr('min') + '%');
        element.next('span.input-group-addon').html(element.attr('max') + '%');
    })
}

/**
 * Get status from a command of type 'info'
 */
function getCmdStatus(_cmd) {
    var status = '';
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/shutters/core/ajax/shutters.ajax.php',
        data: {
            action: 'getCmdStatus',
            cmd: _cmd
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if (data.result.length != 0) {
                console.log('cmdStatus: ' + data.result);
                status = data.result;
            }
        }
    });
    return status;
}

/**
 * List shutters equipment by type
 */
function listEqByType() {
    var listEqByType = new Object();
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/shutters/core/ajax/shutters.ajax.php',
        data: {
            action: 'listEqByType'
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if (data.result.length != 0) {
                listEqByType = data.result;
            }
        }
    });
    console.log(listEqByType);
    return listEqByType;
}

/**
 * Get eqLogic by Id
 * @param {string} _eqLogicId EqLogic Id
 */
function getEqLogic(_eqLogicId) {
    var eqLogic = new Object();
    $.ajax({
        type: 'POST',
        async: false,
        url: 'plugins/shutters/core/ajax/shutters.ajax.php',
        data: {
            action: 'getEqLogic',
            type: 'shutters',
            id: _eqLogicId
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if (data.result.length != 0) {
                eqLogic = data.result;
            }
        }
    });
    console.log(eqLogic);
    return eqLogic;
}

/**
 * Update select by equipment type in shutter settings
 * @param {object} _eqLogic Shutters equipment
 * @param {object} _listEqByType List of shutters equipment by type
 */
function updateEqLink(_eqLogic, _listEqByType) {
    var optionList =['<option value="none" selected>{{Non affectées}}</option>'];
    for (var i = 0; i < _listEqByType.externalInfo.length; i++) {
        optionList.push('<option value="', _listEqByType.externalInfo[i].id, '">', _listEqByType.externalInfo[i].name, '</option>');
    }
    $('[data-l1key=configuration][data-l2key=shutterExternalInfoLink]').html(optionList.join('')).val(_eqLogic.configuration.shutterExternalInfoLink);
    $('[data-l1key=configuration][data-l2key=shuttersGroupExternalInfoLink]').html(optionList.join('')).val(_eqLogic.configuration.shuttersGroupExternalInfoLink);
    
    optionList =['<option value="none" selected>{{Non affectée}}</option>'];
    for (var i = 0; i < _listEqByType.heliotropeZone.length; i++) {
        optionList.push('<option value="', _listEqByType.heliotropeZone[i].id, '">', _listEqByType.heliotropeZone[i].name, '</option>');
    }
    $('[data-l1key=configuration][data-l2key=shutterHeliotropeZoneLink]').html(optionList.join('')).val(_eqLogic.configuration.shutterHeliotropeZoneLink);
    $('[data-l1key=configuration][data-l2key=shuttersGroupHeliotropeZoneLink]').html(optionList.join('')).val(_eqLogic.configuration.shuttersGroupHeliotropeZoneLink);
    
    optionList =['<option value="none" selected>{{Non affecté}}</option>'];
    for (var i = 0; i < _listEqByType.shuttersGroup.length; i++) {
        optionList.push('<option value="', _listEqByType.shuttersGroup[i].id, '">', _listEqByType.shuttersGroup[i].name, '</option>');
    }
    $('[data-l1key=configuration][data-l2key=shuttersGroupLink]').html(optionList.join('')).val(_eqLogic.configuration.shuttersGroupLink);
}