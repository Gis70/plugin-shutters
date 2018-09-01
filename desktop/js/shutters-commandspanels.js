/**
 * Display commands panels for eqLogic
 * @param {object} _eqLogic
 */
function displayCommandsPanels(_eqLogic) {
    var el = $('#commandsPanels');
    el.empty();
    switch (_eqLogic.configuration.eqType) {
        case 'externalConditions':
            var div = '<div class="panel-group">';
            div += '<div class="panel panel-default">';
            div += '<div class="panel-heading">';
            div += '<h4 class="panel-title">{{Commandes de gestion des conditions externes}}</h4>';
            div += '</div>';
            div += '<div class="panel-body">';
            div += '<table id="cmdTable" class="table table-bordered">';
            div += '<thead>';
            div += '<tr>';
            div += '<th style="width: 50px;">{{Id}}</th>';
            div += '<th style="width: 200px;">{{Nom}}</th>';
            div += '<th style="width: 250px;">{{Description}}</th>';
            div += '<th style="width: 50px;">{{Type}}</th>';
            div += '<th style="width: 50px;">{{Sous type}}</th>';
            div += '<th style="width: 50px;">{{Configuration}}</th>';
            div += '<th style="width: 30px;">{{Supprimer}}</th>';
            div += '</tr>';
            div += '</thead>';
            div += '<tbody>';
            div += '</tbody>';
            div += '</table>';
            div += '</div>';
            div += '</div>';
            div += '</div>';

            break;
    
        case 'heliotropeZone':
            var div = '<div class="panel-group">';
            div += '<div class="panel panel-default">';
            div += '<div class="panel-heading">';
            div += '<h4 class="panel-title">{{Commandes de gestion héliotrope}}</h4>';
            div += '</div>';
            div += '<div class="panel-body">';
            div += '<table id="cmdTable" class="table table-bordered">';
            div += '<thead>';
            div += '<tr>';
            div += '<th style="width: 50px;">{{Id}}</th>';
            div += '<th style="width: 200px;">{{Nom}}</th>';
            div += '<th style="width: 250px;">{{Description}}</th>';
            div += '<th style="width: 50px;">{{Type}}</th>';
            div += '<th style="width: 50px;">{{Sous type}}</th>';
            div += '<th style="width: 50px;">{{Configuration}}</th>';
            div += '<th style="width: 30px;">{{Supprimer}}</th>';
            div += '</tr>';
            div += '</thead>';
            div += '<tbody>';
            div += '</tbody>';
            div += '</table>';
            div += '</div>';
            div += '</div>';
            div += '</div>';

            break;
            
        default:
            break;
    }
    el.append(div);

}
