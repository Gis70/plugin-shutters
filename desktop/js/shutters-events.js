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
    $('[data-l1key=configuration][data-l2key=shuttersGroupId]').off('change').on('change', function () {
        var el = $(this);
        if (el.val() !== 'none' && el.val() !== null) {
            $('[data-l1key=configuration][data-l2key=externalConditionsId]').attr('disabled', true);
            $('[data-l1key=configuration][data-l2key=heliotropeZoneId]').attr('disabled', true);
        } else {
            $('[data-l1key=configuration][data-l2key=externalConditionsId]').attr('disabled', false);
            $('[data-l1key=configuration][data-l2key=heliotropeZoneId]').attr('disabled', false);
        }
    });

    // Shutter settings events
    $('[data-l1key=configuration][data-l2key=shutterPositionType]').off('change').on('change', function () {
        var el = $(this);
        displaySettings(el.attr('data-settinggroup'), el.val());
    });
    $('[data-l1key=configuration][data-l2key=shutterCmdType]').off('change').on('change', function () {
        var el = $(this);
        displaySettings(el.attr('data-settinggroup'), el.val());
    });
}