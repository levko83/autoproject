/* ======================================== */
/* init */

window.onload = function () {onLoad()};

onLoad = function () {
	initLegend();
	initCalculator();
	initDiscCalculator();
}


/* ======================================== */
/* legend */

initLegend = function () {
	if (
		document.getElementById
		&& document.getElementById('shemeLegend')
	) {
		oSheme = {
			'nLegend' : document.getElementById('shemeLegend'),
			'aLabels' : new Array()
		}
		initLegendLabel('W');
		initLegendLabel('H');
		initLegendLabel('D');
		initLegendLabel('DD');
	}
}


initLegendLabel = function(sLabel) {
	if (
		oSheme
		&& document.getElementById
		&& document.getElementById('labelSheme' + sLabel)
	) {
		document.getElementById('labelSheme' + sLabel).onmouseover = function() {legendMouseOver(sLabel);};
		document.getElementById('labelSheme' + sLabel).onmouseout = function() {legendMouseOut();};
		oSheme.aLabels.push(sLabel);
		if (document.getElementById('sheme' + sLabel)) {
			doPreload(document.getElementById('sheme' + sLabel).src);
		}
	}
}


legendMouseOver = function(sLabel) {
	legendMouseOut();
	addClass(oSheme.nLegend, 'label' + sLabel);
}


legendMouseOut = function() {
	for (i in oSheme.aLabels) {
		removeClass(oSheme.nLegend, 'label' + oSheme.aLabels[i]);
	}
}


/* ======================================== */
/* calculator */

initCalculator = function() {
	oCalculator = {
		'oldWidth' : document.getElementById('oldWidth'),
		'oldDiameter' : document.getElementById('oldDiameter'),
		'oldProfile' : document.getElementById('oldProfile'),
		'newWidth' : document.getElementById('newWidth'),
		'newDiameter' : document.getElementById('newDiameter'),
		'newProfile' : document.getElementById('newProfile'),

		'oldW' : document.getElementById('oldW'),
		'newW' : document.getElementById('newW'),
		'deltaW' : document.getElementById('deltaW'),

		'oldH' : document.getElementById('oldH'),
		'newH' : document.getElementById('newH'),
		'deltaH' : document.getElementById('deltaH'),

		'oldD' : document.getElementById('oldD'),
		'newD' : document.getElementById('newD'),
		'deltaD' : document.getElementById('deltaD'),

		'oldDD' : document.getElementById('oldDD'),
		'newDD' : document.getElementById('newDD'),
		'deltaDD' : document.getElementById('deltaDD'),

		'deltaC' : document.getElementById('deltaC')
	}

	oSpeedometer = new Array();
	var i=10;
	while (document.getElementById('speed' + i)) {
		oSpeedometer[i] = document.getElementById('speed' + i);
		i += 10;
	}

	applyCalculateEvent(oCalculator.oldWidth);
	applyCalculateEvent(oCalculator.oldDiameter);
	applyCalculateEvent(oCalculator.oldProfile);
	applyCalculateEvent(oCalculator.newWidth);
	applyCalculateEvent(oCalculator.newDiameter);
	applyCalculateEvent(oCalculator.newProfile);

	doCalculate();
}

applyCalculateEvent = function (nNode) {
	nNode.onchange = function () {doCalculate();};
}



doCalculate = function() {
	var iOldW = oCalculator.oldWidth.value;
	var iNewW = oCalculator.newWidth.value;

	var iOldD = Math.round(oCalculator.oldDiameter.value * 25.4);
	var iNewD = Math.round(oCalculator.newDiameter.value * 25.4);

	var iOldDD = Math.round(oCalculator.oldWidth.value*oCalculator.oldProfile.value*0.02 + oCalculator.oldDiameter.value*25.4);
	var iNewDD = Math.round(oCalculator.newWidth.value*oCalculator.newProfile.value*0.02 + oCalculator.newDiameter.value*25.4);

	var iOldH = Math.round((iOldDD - iOldD)/2);
	var iNewH = Math.round((iNewDD - iNewD)/2);
	var iSpeedCoeff = iNewDD/iOldDD;
	var iDeltaW = iNewW - iOldW
	var iDeltaH = iNewH - iOldH
	var iDeltaD = iNewD - iOldD
	var iDeltaDD = iNewDD - iOldDD
	var iDeltaC = iDeltaDD/2

	setTextValue(oCalculator.oldW, iOldW);
	setTextValue(oCalculator.newW, iNewW);
	if (iNewW>iOldW){iDeltaW='+'+iDeltaW};
	setTextValue(oCalculator.deltaW, iDeltaW);

	setTextValue(oCalculator.oldH, iOldH);
	setTextValue(oCalculator.newH, iNewH);
	if (iNewH>iOldH){iDeltaH='+'+iDeltaH};
	setTextValue(oCalculator.deltaH, iDeltaH);

	setTextValue(oCalculator.oldD, iOldD);
	setTextValue(oCalculator.newD, iNewD);
	if (iNewD>iOldD){iDeltaD='+'+iDeltaD};
	setTextValue(oCalculator.deltaD, iDeltaD);

	setTextValue(oCalculator.oldDD, iOldDD);
	setTextValue(oCalculator.newDD, iNewDD);
	if (iNewDD>iOldDD){iDeltaDD='+'+iDeltaDD};
	setTextValue(oCalculator.deltaDD, iDeltaDD);

	if (iNewDD>iOldDD){iDeltaC='+'+iDeltaC};
	setTextValue(oCalculator.deltaC, iDeltaC);

	for (i in oSpeedometer) {
		setTextValue(oSpeedometer[i], Math.round(i*iSpeedCoeff * 10)/10);
	}
}




/* ======================================== */
/* disc */

initDiscCalculator = function () {
	if (
		document.getElementById
		&& document.getElementById('tireWidth')
		&& document.getElementById('tireDiameter')
		&& document.getElementById('tireProfile')
		&& document.getElementById('discDiameter')
		&& document.getElementById('discWidthMin')
		&& document.getElementById('discWidthMax')
	) {
		oDiscCalculator = {
			'tireWidth' : document.getElementById('tireWidth'),
			'tireDiameter' : document.getElementById('tireDiameter'),
			'tireProfile' : document.getElementById('tireProfile'),
			'discDiameter' : document.getElementById('discDiameter'),
			'discWidthMin' : document.getElementById('discWidthMin'),
			'discWidthMax' : document.getElementById('discWidthMax')
		}
		applyCalculateDiscEvent(oDiscCalculator.tireWidth);
		applyCalculateDiscEvent(oDiscCalculator.tireDiameter);
		applyCalculateDiscEvent(oDiscCalculator.tireProfile);

		doCalculateDisc();
	}
}


applyCalculateDiscEvent = function (nNode) {
	nNode.onchange = function () {doCalculateDisc();};
}

doCalculateDisc = function() {
	if (oDiscCalculator) {
		var iWidth = oDiscCalculator.tireWidth.value;
		var iProfile = oDiscCalculator.tireProfile.value;
		var iDiameter = oDiscCalculator.tireDiameter.value;

		iWidthMin = (Math.round(((iWidth*((iProfile < 50) ? 0.85 : 0.7))*0.03937)*2))/2;
		iWidthMax = (iWidthMin+1.5);

		setTextValue(oDiscCalculator.discDiameter, iDiameter);
		setTextValue(oDiscCalculator.discWidthMin, iWidthMin);
		setTextValue(oDiscCalculator.discWidthMax, iWidthMax);
	}
}





/* ======================================== */
/* useful */

setTextValue = function (nNode, sValue) {
	sValue = String(sValue);
	sValue = sValue.replace(/\./, ',');
	nNode.innerHTML = sValue;
}

isClass = function(nNode, sClassName) {
	return (nNode.className.indexOf(sClassName) >= 0);
}

addClass = function(nNode, sClassName) {
	if (nNode.className) {
		var aClass = nNode.className.split(' ');
		for (var i in aClass) {
			if (sClassName == aClass[i]) {
				sClassName = '';
			}
		}
		if (sClassName) {
			aClass.push(sClassName);
		}
		nNode.className = aClass.join(' ');
	}
	else {
		nNode.className = sClassName;
	}
}

removeClass = function(nNode, sClassName) {
	if (nNode.className) {
		var aClass = nNode.className.split(' ');
		for (var i in aClass) {
			if (sClassName == aClass[i]) {
				aClass.splice(i,1);
				break;
			}
		}
		nNode.className = aClass.join(' ');
	}
}


doPreload = function(sImg) {
	var oPreload = new Image();
	oPreload.src = sImg;
}
