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
            div += '<table class="table table-bordered" data-cmdgroup="externalConditions" data-cmdtype="action">';
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

            el.append(div);
            $('table[data-cmdgroup=externalConditions][data-cmdtype=action]').sortable({items: ".cmd", axis: "y", tolerance: "intersect", containment: "table[data-cmdgroup=externalConditions][data-cmdtype=action]", placeholder: "ui-state-highlight", forcePlaceholderSize: true, cursor: "move"});

            break;
    
        case 'heliotropeZone':
            var div = '<div class="panel-group">';
            div += '<div class="panel panel-default">';
            div += '<div class="panel-heading">';
            div += '<h4 class="panel-title">{{Commandes de gestion héliotrope}}</h4>';
            div += '</div>';
            div += '<div class="panel-body">';
            div += '<table class="table table-bordered" data-cmdgroup="heliotropeZone" data-cmdtype="action">';
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

            el.append(div);
            $('table[data-cmdgroup=heliotropeZone][data-cmdtype=action]').sortable({items: ".cmd", axis: "y", tolerance: "intersect", containment: "table[data-cmdgroup=heliotropeZone][data-cmdtype=action]", placeholder: "ui-state-highlight", forcePlaceholderSize: true, cursor: "move"});

            break;

        case 'shutter':
            var div = '<div id="cmdPanel" class="panel-group">';

            div += '<div class="panel panel-default">';
            div += '<div class="panel-heading">';
            div += '<h4 class="panel-title">';
            div += '<a data-toggle="collapse" data-parent="#cmdPanel" href="#cmdPanel1"> {{Commandes de gestion des conditions externes}} </a>';
            div += '</h4>';
            div += '</div>';
            div += '<div id="cmdPanel1" class="panel-collapse collapse in">';
            div += '<div class="panel-body">';
            div += '<table class="table table-bordered" data-cmdgroup="externalConditions" data-cmdtype="action">';
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

            div += '<div class="panel panel-default">';
            div += '<div class="panel-heading">';
            div += '<h4 class="panel-title">';
            div += '<a data-toggle="collapse" data-parent="#cmdPanel" href="#cmdPanel2"> {{Commandes de statut des gestions de conditions externes}} </a>';
            div += '</h4>';
            div += '</div>';
            div += '<div id="cmdPanel2" class="panel-collapse collapse">';
            div += '<div class="panel-body">';
            div += '<table class="table table-bordered" data-cmdgroup="externalConditions" data-cmdtype="info">';
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

            div += '</div>';

            el.append(div);
            $('table[data-cmdgroup=externalConditions][data-cmdtype=action]').sortable({items: ".cmd", axis: "y", tolerance: "intersect", containment: "table[data-cmdgroup=externalConditions][data-cmdtype=action]", placeholder: "ui-state-highlight", forcePlaceholderSize: true, cursor: "move"});
            $('table[data-cmdgroup=externalConditions][data-cmdtype=info]').sortable({items: ".cmd", axis: "y", tolerance: "intersect", containment: "table[data-cmdgroup=externalConditions][data-cmdtype=info]", placeholder: "ui-state-highlight", forcePlaceholderSize: true, cursor: "move"});

            break;

        default:
            break;
    }

}
