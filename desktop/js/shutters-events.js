/**
 * Init events
 */
function initEvents () {

    /**
     * Select a command
     */
    $('.listCmd').on('click', function () {
        var inputId = $(this).attr('data-inputid');
        var dataType = $(this).attr('data-type');
        var dataSubType = $(this).attr('data-subtype');
        if (dataSubType === undefined) {
            dataSubType = '';
        }
        var el = $(this).closest('div.input-group').find('input[data-l1key=configuration][data-l2key=' + inputId + ']');
        jeedom.cmd.getSelectModal({cmd: {type: dataType, subType: dataSubType}}, function (result) {
            console.log(result);
            el.val(result.human);
        });
    });


    /**
     * Delete a command and it's status
     */
    $('.delCmd').on('click', function () {
        var inputId = $(this).attr('data-inputid');
        var cmdElement = $(this).closest('div.input-group').find('input[data-l1key=configuration][data-l2key=' + inputId + ']');
        var cmdStatusElement = $(this).closest('div.form-group').find('input[data-l1key=configuration][data-l2key=' + inputId + 'Status]');
        var cmd = cmdElement.val();
        bootbox.confirm('{{Effacer la  commande }}' + cmd + '{{ peut engendrer une modification du fonctionnement de vos volets. Confirmez vous la suppression?}}', function (result) {
            if (result) {
                cmdElement.val(null);
                cmdStatusElement.val(null);
            }
        }) 
    });

    /**
     * Get status of a command 'info'
     */
    $('.getCmdStatus').on('click', function () {
        var inputId = $(this).attr('data-inputid');
        var cmdInputId = $(this).attr('data-cmdinputid');
        var cmd = $('input[id=' + cmdInputId + ']').val();
        var el = $(this).closest('div.input-group').find('input[data-l1key=configuration][data-l2key=' + inputId + ']');
        if (cmd === null || cmd === '') {
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

    // Heliotrope zone settings events
    $('[data-l1key=configuration][data-l2key=dawnType]').off('change').on('change', function () {
        var el = $(this);
        displaySelectedDawnOrDusk(el.val());
    });
    $('[data-l1key=configuration][data-l2key=duskType]').off('change').on('change', function () {
        var el = $(this);
        displaySelectedDawnOrDusk(el.val());
    });
    $('[data-l1key=configuration][data-l2key=wallAngle]').off('change').on('change', function () {
        refreshWallPlan();
    });
    $('[data-l1key=configuration][data-l2key=wallAngleUnit]').off('change').on('change', function () {
        updateAngleRange()
        refreshWallPlan();
    });

    // Shutters group settings events
    $('[data-l1key=configuration][data-l2key=shuttersGroupLink]').off('change').on('change', function () {
        var el = $(this);
        if (el.val() !== 'none' && el.val() !== null) {
            $('[data-l1key=configuration][data-l2key=shutterExternalInfoLink]').attr('disabled', true);
            $('[data-l1key=configuration][data-l2key=shutterHeliotropeZoneLink]').attr('disabled', true);
        } else {
            $('[data-l1key=configuration][data-l2key=shutterExternalInfoLink]').attr('disabled', false);
            $('[data-l1key=configuration][data-l2key=shutterHeliotropeZoneLink]').attr('disabled', false);
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