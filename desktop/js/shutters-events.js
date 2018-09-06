/**
 * Init events
 */
function initEvents () {

    /**
     * Select a command
     */
    $('.listCmd').off('click').on('click', function () {
        var l2key = $(this).attr('data-l2key');
        var dataType = $(this).attr('data-type');
        var dataSubType = $(this).attr('data-subtype');
        if (dataSubType === undefined) {
            dataSubType = '';
        }
        var el = $('input[data-l1key=configuration][data-l2key=' + l2key + ']');
        jeedom.cmd.getSelectModal({cmd: {type: dataType, subType: dataSubType}}, function (result) {
            el.val(result.human);
        });
    });

    /**
     * Delete a command
     */
    $('.delCmd').off('click').on('click', function () {
        var l2key = $(this).attr('data-l2key');
        var cmd = $('input[data-l1key=configuration][data-l2key=' + l2key + ']').val();
        var el = $(this).closest('div.form-group').find('input.eqLogicAttr');
        bootbox.confirm('{{Effacer la  commande }}' + cmd + '{{ peut engendrer une modification du fonctionnement de vos volets. Confirmez vous la suppression?}}', function (result) {
            if (result) {
                el.each(function() {
                    var value = $(this).attr('value');
                    $(this).val(value);
                });
            }
        }) 
    });
  
    /**
     * Get status of a command 'info'
     */
    $('.getCmdStatus').off('click').on('click', function () {
        var l2key = $(this).attr('data-l2key');
        var cmdl2key = $(this).attr('data-cmdl2key');
        var cmd = $('input[data-l1key=configuration][data-l2key=' + cmdl2key + ']').val();
        var el = $('input[data-l1key=configuration][data-l2key=' + l2key + ']');
        if (cmd.length === 0) {
            return;
        }
        bootbox.confirm('{{Avant de récupérer le statut de la condition }}' + cmd + '{{, êtes vous sûr que celle-ci est bien active?}}', function (result) {
            if (result) {
                el.val(getCmdStatus(cmd));
            }
        }) 
    });

    /**
     *  Display value of input range
     */
    $('input[type=range]').on('change mousemove', function () {
        $(this).parent().next('span.input-range-value').html($(this).val() + '%');
    });

    /**
     *  Heliotrope zone settings events
     */
    $('[data-l1key=configuration][data-l2key=dawnType]').off('change').on('change', function () {
        displaySelectedDawnOrDusk($(this).val());
    });
    $('[data-l1key=configuration][data-l2key=duskType]').off('change').on('change', function () {
        displaySelectedDawnOrDusk($(this).val());
    });
    $('[data-l1key=configuration][data-l2key=wallAngle]').off('change').on('change', function () {
        refreshWallPlan($('[data-l1key=configuration][data-l2key=wallAngle]').val(), $('[data-l1key=configuration][data-l2key=wallAngleUnit]').val());
    });
    $('[data-l1key=configuration][data-l2key=wallAngleUnit]').off('change').on('change', function () {
        updateAngleRange($('[data-l1key=configuration][data-l2key=wallAngleUnit]').val());
        refreshWallPlan($('[data-l1key=configuration][data-l2key=wallAngle]').val(), $('[data-l1key=configuration][data-l2key=wallAngleUnit]').val());
    });

    /**
     *  Shutters group settings events
     */ 
    $('[data-l1key=configuration][data-l2key=shuttersGroupId], [data-l1key=configuration][data-l2key=groupHeritage]').off('change').on('change', function () {
        var shuttersGroupId = $('[data-l1key=configuration][data-l2key=shuttersGroupId]').val();
        var groupHeritage = $('[data-l1key=configuration][data-l2key=groupHeritage]').val();
        if (!Number.isInteger(Number.parseInt(shuttersGroupId, 10))) {
            $('[data-l1key=configuration][data-l2key=groupHeritage]').attr('disabled', true).val('both');
        } else {
            $('[data-l1key=configuration][data-l2key=groupHeritage]').attr('disabled', false);
        }
        if(Number.isInteger(Number.parseInt(shuttersGroupId, 10))
        && (groupHeritage === 'both' || groupHeritage === 'externalConditions')) {
            $('[data-l1key=configuration][data-l2key=externalConditionsId]').attr('disabled', true);
        } else {
            $('[data-l1key=configuration][data-l2key=externalConditionsId]').attr('disabled', false);
        }
        if(Number.isInteger(Number.parseInt(shuttersGroupId, 10))
        && (groupHeritage === 'both' || groupHeritage === 'heliotropeZone')) {
            $('[data-l1key=configuration][data-l2key=heliotropeZoneId]').attr('disabled', true);
        } else {
            $('[data-l1key=configuration][data-l2key=heliotropeZoneId]').attr('disabled', false);
        }
    });

    /**
     *  Shutter settings events
     */ 
    $('[data-l1key=configuration][data-l2key=heliotropeZoneId]').off('change').on('change', function () {
        var heliotropeZoneId = $(this).val();
        if(Number.isInteger(Number.parseInt(heliotropeZoneId, 10))) {
            $('fieldset[data-displaygroup=azimutPlan]').css('visibility', 'visible');
            var eqLogic = getEqLogic(heliotropeZoneId);
            var wallAngle = convertAngleToDegree(eqLogic.configuration.wallAngle, eqLogic.configuration.wallAngleUnit);
            var incomingAngle = Number.parseInt($('[data-l1key=configuration][data-l2key=incomingAngle]').val(), 10);
            var outgoingAngle = Number.parseInt($('[data-l1key=configuration][data-l2key=outgoingAngle]').val(), 10);
            sessionStorage.setItem('wallAngle', wallAngle);
            refreshAzimutPlan(incomingAngle, outgoingAngle, wallAngle);
        } else {
            $('fieldset[data-displaygroup=azimutPlan]').css('visibility', 'hidden');
        }
    });
    $('[data-l1key=configuration][data-l2key=shutterPositionType]').off('change').on('change', function () {
        displaySettings($(this).attr('data-displaygroup'), $(this).val());
    });
    $('[data-l1key=configuration][data-l2key=shutterCmdType]').off('change').on('change', function () {
        displaySettings($(this).attr('data-displaygroup'), $(this).val());
    });
    $('[data-l1key=configuration][data-l2key=incomingAngle], [data-l1key=configuration][data-l2key=outgoingAngle]').off('change').on('change', function () {
        var incomingAngle = Number.parseInt($('[data-l1key=configuration][data-l2key=incomingAngle]').val(), 10);
        var outgoingAngle = Number.parseInt($('[data-l1key=configuration][data-l2key=outgoingAngle]').val(), 10);
        var wallAngle = Number.parseInt(sessionStorage.getItem('wallAngle'), 10);
        refreshAzimutPlan(incomingAngle, outgoingAngle, wallAngle);
    });
    $('#timeGraph').on('mousemove', function(event) {
        $('.cursor-tooltip').css({
            top: event.pageY + 20,
            left: event.pageX
        });
    })
    
}