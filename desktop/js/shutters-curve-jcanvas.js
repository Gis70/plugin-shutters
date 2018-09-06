    var graph = {
        xOrigin: 50,
        yOrigin: 350,
        xAxisLength: 300,
        xAxisExtraLength: 20,
        yAxisLength: 300,
        yAxisExtraLength: 20,
        yValueStepPoint: 1,
        curveType: 'ascending', // possible type: free, ascending, downward
        zeroPointDraggable: false,
        xAxisPointNumber: 4,
        xMinScale: 0,
        xMaxScale: 100,
        xAxisColor: '#b3b3b3',
        yAxisStepNumber: 10,
        yMinScale: 0,
        yMaxScale: 50,
        yAxisColor: '#b3b3b3',
        xUnit: '%',
        xUnitFontSize: '10pt',
        xUnitColor: '#7d7d7d',
        yUnit: 'sec',
        yUnitFontSize: '10pt',
        yUnitColor: '#7d7d7d',
        xValueFontSize: '8pt',
        xValueColor: '#7d7d7d',
        yValueFontSize: '8pt',
        yValueColor: '#7d7d7d',
        xValuexOffset: 5,
        xValueyOffset: 10,
        yValuexOffset: -5,
        yValueyOffset: -5,
        curveColor: '#d9534f',
        pointColor: '#FEE200',
        // Calculated values
        xUnitLength: 0,
        yUnitLength: 0,
        xStepLength: 0,
        yStepLength: 0,
        xStepValue: 0,
        yStepValue: 0,
        xAxisStartPoint: 0,
        xAxisEndPoint: 0,
        yAxisStartPoint: 0,
        yAxisEndPoint: 0
    };

    var curve = {
        type: 'line',
        name: 'curve',
        strokeStyle: graph.curveColor,
        strokeWidth: 2
    };

    var keyRegex = new RegExp(/^[xy]\d{1,2}$/);

    var shutterMvtTimeValues = {};
    var timeCurveYValues = [];

/**
 * Draw graph
 * @param {string} _xAxisPointNumber 
 */
function drawTimeGraph (_xAxisPointNumber = '4') {
    var myGraph = $('#timeGraph');
    graph.xAxisPointNumber = Number.parseInt(_xAxisPointNumber);

    graph.xUnitLength = Math.round(graph.xAxisLength / (graph.xMaxScale - graph.xMinScale));
    graph.xStepValue = Math.round((graph.xMaxScale - graph.xMinScale) / graph.xAxisPointNumber);
    graph.xStepLength = graph.xStepValue * graph.xUnitLength;
    graph.yUnitLength = Math.round(graph.yAxisLength / (graph.yMaxScale - graph.yMinScale));
    graph.yStepValue = Math.round((graph.yMaxScale - graph.yMinScale) / graph.yAxisStepNumber);
    graph.yStepLength = graph.yStepValue * graph.yUnitLength;
    graph.xAxisStartPoint = graph.xOrigin + (graph.xMinScale * graph.xUnitLength);
    graph.xAxisEndPoint = graph.xOrigin + (graph.xMaxScale * graph.xUnitLength);
    graph.yAxisStartPoint = graph.yOrigin - (graph.yMinScale * graph.yUnitLength);
    graph.yAxisEndPoint = graph.yOrigin - (graph.yMaxScale * graph.yUnitLength);
    if(((Object.keys(shutterMvtTimeValues).length / 2) - 1 )!= graph.xAxisPointNumber) {
        for (prop in curve){
            if(curve.hasOwnProperty(prop) && keyRegex.test(prop)) {
                delete curve[prop];
            }  
        };
        shutterMvtTimeValues = {};
        timeCurveYValues = [];
        for (var i = 0; i <= graph.xAxisPointNumber; i++) {
            curve['x' + (i + 1)] = graph.xAxisStartPoint + (i * graph.xStepLength);
            curve['y' + (i + 1)] = graph.yOrigin;
            timeCurveYValues.push(graph.yOrigin);
            shutterMvtTimeValues['x' + (i + 1)] = graph.xMinScale + (i * graph.xStepValue);
            shutterMvtTimeValues['y' + (i + 1)] = 0;
        }
        sessionStorage.setItem('retrieveCurveValues', false);
    }
    updateTimeTable(shutterMvtTimeValues);
  
    myGraph.addLayer({
        type: 'line',
        name: 'xAxis',
        strokeStyle: graph.xAxisColor,
        strokeWidth: 2,
        rounded: true,
        endArrow: true,
        arrowRadius: 5,
        arrowAngle: 90,
        x1: graph.xAxisStartPoint - graph.xAxisExtraLength,
        y1: graph.yOrigin,
        x2: graph.xAxisEndPoint + graph.xAxisExtraLength,
        y2: graph.yOrigin
    })
    .addLayer({
        type: 'line',
        name: 'yAxis',
        strokeStyle: graph.yAxisColor,
        strokeWidth: 2,
        rounded: true,
        endArrow: true,
        arrowRadius: 5,
        arrowAngle: 90,
        x1: graph.xOrigin,
        y1: graph.yAxisStartPoint + graph.yAxisExtraLength,
        x2: graph.xOrigin,
        y2: graph.yAxisEndPoint - graph.yAxisExtraLength
    })
    .addLayer({
        type: 'text',
        name: 'xAxisUnit',
        fillStyle: graph.xUnitColor,
        x: graph.xAxisEndPoint + graph.xAxisExtraLength + 20,
        y: graph.yOrigin,
        fontSize: graph.xUnitFontSize,
        align: 'center',
        text: graph.xUnit
    })
    .addLayer({
        type: 'text',
        name: 'yAxisUnit',
        fillStyle: graph.yUnitColor,
        x: graph.xOrigin,
        y: graph.yAxisEndPoint - graph.yAxisExtraLength - 10,
        fontSize: graph.yUnitFontSize,
        align: 'center',
        text: graph.yUnit
    })

    for (var i = 0; i <= graph.yAxisStepNumber; i++) {
        myGraph.addLayer({
            type: 'line',
            strokeStyle: graph.yAxisColor,
            strokeWidth: 1,
            strokeDash: [5],
            strokeDashOffset: 0,
            x1: graph.xAxisStartPoint - 5,
            y1: graph.yAxisStartPoint - (i * graph.yStepLength),
            x2: graph.xAxisEndPoint + 5,
            y2: graph.yAxisStartPoint - (i * graph.yStepLength)
            })
            .addLayer({
            type: 'text',
            fillStyle: graph.xUnitColor,
            x: graph.xOrigin + graph.yValuexOffset,
            y: graph.yAxisStartPoint - (i * graph.yStepLength) + graph.yValueyOffset,
            fontSize: graph.yValueFontSize,
            align: 'right',
            respectAlign: true,
            text: graph.yMinScale + (i * graph.yStepValue)
        });
    }

    for (var i = 0; i <= graph.xAxisPointNumber; i++) {
        myGraph.addLayer({
            type: 'line',
            strokeStyle: graph.xAxisColor,
            strokeWidth: 1,
            strokeDash: [5],
            strokeDashOffset: 0,
            x1: graph.xAxisStartPoint + (i * graph.xStepLength),
            y1: graph.yAxisStartPoint + 5,
            x2: graph.xAxisStartPoint + (i * graph.xStepLength),
            y2: graph.yAxisEndPoint - 5
            })
            .addLayer({
            type: 'text',
            fillStyle: graph.xUnitColor,
            x: graph.xAxisStartPoint + (i * graph.xStepLength) + graph.xValuexOffset,
            y: graph.yOrigin + graph.xValueyOffset,
            fontSize: graph.xValueFontSize,
            align: 'left',
            respectAlign: true,
            text: graph.xMinScale + (i * graph.xStepValue)
        });
    }

    myGraph.addLayer(curve);

    for (var i = 0; i <= graph.xAxisPointNumber; i++) {
        var pointName = 'point' + (i + 1);
        var xValue = graph.xAxisStartPoint + (i * graph.xStepLength);
        myGraph.addLayer({
            type: 'arc',
            name: pointName,
            draggable: true,
            restrictDragToAxis: 'y',
            strokeStyle: graph.pointColor,
            strokeWidth: 1,
            fillStyle: graph.pointColor,
            radius: 6,
            x: xValue,
            y: graph.yOrigin,
            updateDragY: function(layer, y) {
                return calculateYValue(layer, y);
            },
            drag: function(layer) {
                updateTimeCurve(layer);
                displayTooltip(scaleValue(layer.y));
            },
                mouseover: function(layer) {
                displayTooltip(scaleValue(layer.y));
            },
                mouseout: function() {
                hideTooltip();
            }
        })
        if (!graph.zeroPointDraggable && xValue == graph.xOrigin) {
            myGraph.setLayer(pointName, {
            draggable: false,
            })
        }
    }

    myGraph.drawLayers();
}

/**
 * Redraw time curve
 * @param {array} _timeValues 
 */
function redrawTimeCurve (_timeValues = []) {
    var myGraph = $('#timeGraph');
    for (var i = 0; i < _timeValues.length; i++) {
        myGraph.setLayer('point' + (i + 1), {
        y: Number.parseInt(_timeValues[i], 10)
        });
        curve[ 'y' + (i + 1)] = Number.parseInt(_timeValues[i], 10);
        }
    myGraph.setLayer('curve', curve).drawLayers();
}

/**
 * Calculate Y value
 * @param {*} _layer 
 * @param {*} _y 
 */
function calculateYValue(_layer = {}, _y = 0) {
    var myGraph = $('#timeGraph');
    var yMin = 0;
    var yMax = 0;
    var pointIndex = Number.parseInt(_layer.name.match(/\d+/), 10);
    var yStep = graph.yValueStepPoint * graph.yUnitLength;
    var offset = Math.ceil(yStep / 2);
    var yValue = Math.round(_y / yStep) * yStep - offset;
    switch (graph.curveType) {
        case 'ascending':
            if (pointIndex == 1) {
                yMin = myGraph.getLayer('point' + (pointIndex + 1)).y;
                yMax = graph.yAxisStartPoint;
            } else if (pointIndex == graph.xAxisPointNumber + 1) {
                yMin = graph.yAxisEndPoint;
                yMax = myGraph.getLayer('point' + (pointIndex - 1)).y;
            } else {
                yMin = myGraph.getLayer('point' + (pointIndex + 1)).y;
                yMax = myGraph.getLayer('point' + (pointIndex - 1)).y;
            }
            break;
        case 'downward':
            if (pointIndex == 1) {
                yMin = graph.yAxisEndPoint;
                yMax = myGraph.getLayer('point' + (pointIndex + 1)).y;
            } else if (pointIndex == graph.xAxisPointNumber + 1) {
                yMin = myGraph.getLayer('point' + (pointIndex - 1)).y;
                yMax = graph.yAxisStartPoint;
            } else {
                yMin = myGraph.getLayer('point' + (pointIndex - 1)).y;
                yMax = myGraph.getLayer('point' + (pointIndex + 1)).y;
            }
            break;
        default:
            yMin = graph.yAxisEndPoint;
            yMax = graph.yAxisStartPoint;
    }
    if (yValue <= yMin) {
        yValue = yMin;
    }
    if (yValue >= yMax) {
        yValue = yMax;
    }
    return yValue;
}

/**
 * Update time curve
 * @param {string} _layer 
 */
function updateTimeCurve(_layer = '') {
    var myGraph = $('#timeGraph');
    var pointIndex = Number.parseInt(_layer.name.match(/\d+/), 10);
    curve[ 'y' + pointIndex] = timeCurveYValues[pointIndex -1 ] = _layer.y;
    shutterMvtTimeValues[ 'y' + pointIndex] = scaleValue(_layer.y);
    myGraph.setLayer('curve', curve).drawLayers();
    updateTimeTable(shutterMvtTimeValues);
}

/**
 * Update time table
 * @param {object} _timeValues 
 */
function updateTimeTable(_timeValues = {}) {
    var tr ='';
    for(i = 1; i <= (Object.keys(shutterMvtTimeValues).length / 2); i++) {
        tr += '<tr>';
        tr += '<td class="text-center">' + _timeValues['x' + i] + '</td>';
        tr += '<td class="text-center">' + _timeValues['y' + i] + '</td>';
        tr += '</tr>';
    }
    $('#shutterMvtTimeTable tbody').empty().append(tr);
}

/**
 * Scale cursor position to time value
 * @param {integer} _value 
 */
function scaleValue(_value = 0) {
    var scaledValue = Math.round((graph.yOrigin - _value) * (graph.yMaxScale - graph.yMinScale) / graph.yAxisLength);
    return scaledValue;
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    $('.cursor-tooltip').css('visibility', 'hidden');
}

/**
 * Display message in tooltip
 * @param {string*} message 
 */
function displayTooltip(message = '') {
    $('.cursor-tooltip').html(message).css('visibility', 'visible');
}

