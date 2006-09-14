var d=document;
var field = '';
var conf = new Array();
conf['data'] = new Array();
var tabArray = new Array();
//var origClass = new String();
var logRefresh = '';
var moveCount = 0;
var delCount = 0;
var detect = navigator.userAgent.toLowerCase();
var cleanMsgs = true;

//*******************************
// Utility functions
//*******************************
function browserChk(string) {
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}
function clear(element) {
	d.getElementById(element).innerHTML = '';
}
function cleanMessages() {
	if (d.getElementById('messages') != undefined) {
		d.getElementById('messages').style.display = 'none';
		clear('messages');
	}
}
function rmNode(element) {
	node = d.getElementById(element);
	node.parentNode.removeChild(node);
}
function clearFieldValues(formName, elementList, setZero) {
	setZero ? setZero=true : setZero=false;
	elementArr = elementList.split(',');
	for(var i = 0;i<elementArr.length;i++) {
		element = eval('d.' + formName + '.' +elementArr[i]);
		if (element != undefined) {
			if (element.type == 'select-one') {
				element.selectedIndex = '0';
			} else {
				if (setZero) {
					element.value = '0';
				} else {
					element.value = '';
				}
			}
		}
	}
}

function setConf(pos, value, delPart, delData) {
	var confPath = '';
	var confArr = pos.split('=>');

	for(var i = 0;i<confArr.length-1;i++) {
		confPath += "['" + confArr[i] + "']";
	}
	if (delPart) {
		eval('conf' + confPath + ' = new Array()');
	}
	eval("conf" + confPath + "['" + confArr.pop() + "'] = value");

	if (delData) {
		conf['data'] = new Array();
		conf['formdata'] = new Array();
		moveCount = 0;
		delCount = 0;
	}
}

function resetCharListElements() {
	var charElements = d.getElementById('char-list').getElementsByTagName('A');
	for (count=0;count<charElements.length; count++){
		d.getElementById('char-list').getElementsByTagName('A')[count].className='';
	}
}
/*
function openConfirm(message) {
	Check = confirm(message);
	if(Check == false) {
		return false;
	} else {
		return true;
	}
}
*/
function openConfirm(message, ctrue, cfalse) {
	Check = confirm(message);
	if(Check == true) {
		// Delay 1ms to work without problems on IE
		window.setTimeout(ctrue, 1);
	} else {
		window.setTimeout(cfalse, 1);
	}
}


function showLayer(prefix, selectedId, totalCount) {
	if (selectedId >= 0 && totalCount) {
		for(k=0; k<totalCount; k++) {
			element = d.getElementById(prefix + k);
			if(k == selectedId) {
				element.style.display = 'block';
			} else {
				element.style.display = 'none';
			}
		}
	}
}


function tabActivate(tabId) {
	fmsg = false;
	if(tabId) {
		for(i=0; i<tabArray.length;i++) {
			element = d.getElementById(tabArray[i]);
			menuElement = d.getElementById(tabArray[i]+'_MENU');
			//alert(reg.className);
			if (menuElement != undefined) {
				menuElement.className = 'tab-ina';
				if(i == tabId) {
					element.style.display = 'block';
					tabOrigClass = 'tab-act';
					menuElement.className = tabOrigClass;

					if (i > 0 && typeof(ifupload) == 'object') {
						try {
							parent.ifupload.setIframeSize();
						} catch (e) {
						}
					}
				} else {
					element.style.display = 'none';
				}
			} else {
				if (!fmsg) {
					alert('You have defined tabs in the template of this site, but forgot to insert the ###REGISTER_NAVIGATION### marker. Without this marker, the page can not be displayed correctly!');
					fmsg = true;
				}
			}
		}
	}
}
function tabMouseOver(obj) {
	if (obj.className != 'tab-act') {
		tabOrigClass = obj.className;
		obj.className = 'tab-over';
	}
}
function tabMouseOut(obj) {
	if (obj.className != 'tab-act') {
		obj.className = tabOrigClass;
		tabOrigClass = '';
	}
}

function addToList(list, add) {
	if (list) {
		list = list.split(',');
		// Check if already in list somehow
		for(i=0; i<list.length; i++) {
			if (list[i] == add) return;
		}
		list.push(add);
		list = list.join(',');
	} else {
		list = add;
	}
	return list;
}
function rmFromList(list, rm) {
	if (list) {
		list = list.split(',');
		// Check if already in list somehow
		for(i=0; i<list.length; i++) {
			if (list[i] == rm) break;
		}
		list.splice(i, 1);
		list = list.join(',');
	}
	return list;
}

function checkFormFieldsUndefined(theform, fieldList) {
	fieldList = fieldList.split(',');
	for( i=0; i< fieldList.length; i++) {
		if (eval('theform.' + fieldList[i]) == undefined || eval('theform.' + fieldList[i]) == 'undefined') return false;
	}
	return true;
}

/*
// Small test for later version
window.onkeyup = keyPressEvent;
function keyPressEvent(theEvent) {
	if (d.getElementById("char-list")) {
		updateGroups(String.fromCharCode(theEvent.which));
	}
}
*/

//*******************************
// Marking functions
//*******************************
function multiMvDelChkbUpdate(elem, move, del) {
	if (conf['data']['id'] == undefined || conf['data']['id'].length == 0) {
		moveCount = 0;
		delCount = 0;
	}
	if (elem.checked) {
		conf['data']['id'] = addToList(conf['data']['id'], elem.value);
		if (move) moveCount++;
		if (del) delCount++;
	} else {
		conf['data']['id'] = rmFromList(conf['data']['id'], elem.value);
		if (move) moveCount--;
		if (del) delCount--;
	}

	updateMultiMvDelBtn();
}
function updateMultiMvDelBtn() {
	mvBtn = d.getElementById('mmove');
	delBtn = d.getElementById('mdel');
	delOnClick = d.getElementById('mdel').getAttribute('onclick').replace(/%count%/, delCount);
	d.getElementById('mdel').setAttribute('onclick', delOnClick);
	if (mvBtn) { if (moveCount > 0) mvBtn.style.display='block'; else mvBtn.style.display='none'; }
	// && moveCount >= delCount
	if (delBtn) { if (delCount > 0) delBtn.style.display='block'; else delBtn.style.display='none'; }
	// && delCount >= moveCount
}
function multiMvDelChkAllNone(idList, mCount, dCount) {
	// Reset count in multi del message
	delOnClick = d.getElementById('mdel').getAttribute('onclick').replace(delCount, '%count%');
	d.getElementById('mdel').setAttribute('onclick', delOnClick);
	chkbState = conf['data']['id'] == undefined || idList.length != conf['data']['id'].length ? true : false;
	conf['data']['id'] = chkbState ? idList : '';
	if (chkbState) {
		moveCount = mCount;
		delCount = dCount;
	} else {
		moveCount = 0;
		delCount = 0;
	}
	updateMultiMvDelBtn();
	elem = d.getElementsByName('mmvdel');
	for(i=0; i<elem.length; i++) {
		elem[i].checked = chkbState;
	}
}

//*******************************
// General functions (show)
//*******************************
function showData(csite) {
	conf['csite'] = csite;
	conf['part'] = 'showData';
	callPhpDefaultRequestHandler();
}
function updateData(csite) {
	conf['csite'] = csite;
	conf['part'] = 'updateData';
	callPhpDefaultRequestHandler();
	resetCharListElements();
	chr = conf['show']['char'];
	if (chr != undefined && chr != 'dchar') {
		d.getElementsByName(chr)[0].className='char-active';
	}
}
function getUsersOfGroup(formName, fieldName, fieldValue, showAll, exclude) {
	field = eval('d.' + formName +'.' + fieldName);
	conf['part'] = 'getusersofgroup';
	conf['data']['fieldValue'] = fieldValue;
	conf['data']['showAll'] = showAll ? true : false;
	conf['data']['exclude'] = exclude ? exclude : false;
	callPhpDefaultRequestHandler();
}
function getIpsOfType(formName, fieldName, type) {
	field = eval('d.' + formName +'.' + fieldName);
	conf['part'] = 'getipsoftype';
	conf['data']['type'] = type;
	callPhpDefaultRequestHandler();
}
function getDomainExample(id) {
	if (id > '0') {
		conf['data']['id'] = id;
		conf['part'] = 'getdomainexample';
		callPhpDefaultRequestHandler();
	} else {
		updateDomainExample('');
	}
}
function grapFormData(theform) {
	theData = new Array();
	theData['hidden'] = new Array();
	theData['submit'] = new Array();
	theData['data'] = new Array();

	for( var i=0; i< theform.elements.length; i++) {
		eElem = theform.elements[i];
		eType = eElem.type;
		eName = eElem.name;
		eValue = eElem.value;

		switch (eType) {
			case 'checkbox':
				theData['data'][escape(eName)] = theform.elements[i].checked ? eValue : '';
			case 'hidden':
				theData['hidden'][escape(eName)] = eValue;
				break;
			case 'submit':
				theData['submit'][escape(eName)] = eValue;
				break;
			case 'select-multiple':
				theData['data'][escape(eName)] = new Array();
				var k=0;
				for (var j = 0; j < eElem.length; j++) {
					if (eElem.options[j].selected == true) {
						theData['data'][escape(eName)][k] = eElem.options[j].value;
						k++;
					}
				}
				break;
			default:
				theData['data'][escape(eName)] = eValue;
				break;
		}
	}
	return theData;
}
function cryptPassFields(fieldList) {
	fieldArr = fieldList.split(',');
	for(var i = 0;i<fieldArr.length;i++) {
		password = eval('d.formdata.' + fieldArr[i]);
		if (password.value)	{
			password.value = MD5(password.value);
		}
	}
}
function toggleFields(field, onCheck, onUncheck, formName) {
	if (!formName) formName = 'formdata';
	// split the single field configs
	var partsArr;
	if (field.type == 'checkbox') {
		field.checked ? partsArr = onCheck.split(',') : partsArr = onUncheck.split(',');
	} else {
		partsArr = onCheck.split(',');
	}

	if (partsArr != '') {
		for(var i = 0;i<partsArr.length;i++) {
			// split the config parts:
			// Checkbox, Radio: ( [0]=>fieldName [1]=>enabled [2]=>checked )
			// Select: ( [0]=>fieldName [1]=>enabled [2]=>selectedIndex )
			// Others: ( [0]=>fieldName [1]=>enabled [2]=>value )
			fieldArr = partsArr[i].split('|');

			changeField = eval('d.' + formName + '.' + fieldArr[0]);
			if (typeof(changeField) != undefined && typeof(changeField) != 'undefined') {
				fieldArr[1] == '0' ? changeField.disabled = true : changeField.disabled = false;
				if (typeof(fieldArr[2]) != undefined && typeof(fieldArr[2]) != 'undefined' && fieldArr[2] != '') {
					if (changeField.type == 'checkbox' || changeField.type == 'radio') {
						fieldArr[2] == '1' ? changeField.checked = true : changeField.checked = false;
					} else if (changeField.type == 'select-one') {
						changeField.selectedIndex = fieldArr[2];
					} else if (changeField.type == 'text') {
						changeField.value = fieldArr[2];
					}
					changeField.onchange ? changeField.onchange() : '';
				}
			} else {
				// Can also be an id in some cases (but then we hide it, don't disable it)
				changeField = eval('d.getElementById(\'' + fieldArr[0] + '\')');
				if (changeField != null) {
					fieldArr[1] == '0' ? changeField.style.display = 'none' : changeField.style.display = 'block';
					if (fieldArr[0].substr(0,2) == 'TAB') {
						// On "Tabs" we also have to toggle the next cell
						fieldArr[1] == '0' ? changeField.nextSibling.style.display = 'none' : changeField.nextSibling.style.display = 'block';
					}
				}
			}
		}
	}
}

//*******************************
// Group functions
//*******************************
function addEditGroup(id) {
	if (id) conf['data']['id'] = id;
	conf['part'] = 'addEditGroup';
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function deleteGroup(id) {
	conf['part'] = 'deleteGroup';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function processGroup(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processGroup';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// User functions
//*******************************
function addEditUser(id) {
	if (id) conf['data']['id'] = id;
	conf['part'] = 'addEditUser';
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function moveUser(id) {
	conf['part'] = 'moveUser';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function deleteUser(id) {
	conf['part'] = 'deleteUser';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function processUser(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processUser';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// Template functions
//*******************************
function addEditTemplate(id) {
	if (id) conf['data']['id'] = id;
	conf['part'] = 'addEditTemplate';
	domain = '';
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function moveTemplate(id) {
	conf['part'] = 'moveTemplate';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function deleteTemplate(id) {
	conf['part'] = 'deleteTemplate';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function processTemplate(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processTemplate';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// Domains functions
//*******************************
function addEditDomain(id) {
	if (id) conf['data']['id'] = id;
	conf['part'] = 'addEditDomain';
	domain = '';
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function addDomainWithTemplate() {
	conf['part'] = 'addDomainWithTemplate';
	callPhpDefaultRequestHandler();
}
function moveDomain(id) {
	conf['part'] = 'moveDomain';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function deleteDomain(id) {
	conf['part'] = 'deleteDomain';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function processDomain(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processDomain';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// Preferences functions
//*******************************
function editUserPrefs(csite) {
	conf['csite'] = csite;
	conf['part'] = 'editUserPrefs';
	callPhpDefaultRequestHandler();
}
function processUserPref(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processUserPref';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// Logging functions
//*******************************
function showLogMessages() {
	conf['part'] = 'showLogMessages';
	callPhpDefaultRequestHandler();
}
function updateLogMessages(formdata) {
	conf['part'] = 'updateLogMessages';
	if (checkFormFieldsUndefined(formdata, 'filter_group,filter_user,filter_part,filter_type,filter_date,filter_max,filter_message,refresh')) {
		theData = grapFormData(formdata);
		conf['formdata'] = theData;
	} else {
		setLogRefresh(0);
		return false;
	}
	callPhpDefaultRequestHandler();
}
function setLogRefresh(refresh) {
	if (refresh == 0) {
		window.clearInterval(logRefresh);
	} else {
		refresh = refresh * 1000;
		logRefresh = window.setInterval("updateLogMessages(d.forms[0])", refresh);
	}
}

//*******************************
// Tools functions
//*******************************
function showTools() {
//	conf['csite'] = csite;
	conf['data']['rInfo'] = '';
	conf['part'] = 'showTools';
	callPhpDefaultRequestHandler();
}
function processIpChange(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processIpChange';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// Sysinfo
//*******************************
function updateShortSysinfo() {
	var u = new tupa_sysinfo(shortSysinfoHandler);
	u.updateshortsysinfo();
}
function showSysinfo() {
	conf['part'] = 'showSysinfo';
	callPhpDefaultRequestHandler();
}

//*******************************
// Backup
//*******************************
function showBackup() {
	conf['part'] = 'showBackup';
	callPhpDefaultRequestHandler();
}
function processBackup(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processBackup';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}

//*******************************
// System
//*******************************
function showSystemConfig() {
	conf['part'] = 'showSystemConfig';
	callPhpDefaultRequestHandler();
}
function processSystemConfig(formdata) {
	theData = grapFormData(formdata);
	conf['part'] = 'processSystemConfig';
	conf['formdata'] = theData;
	callPhpDefaultRequestHandler();
}
function getLangMgrUpdate(rInfo) {
//	conf['csite'] = csite;
	if (rInfo) conf['data']['rInfo'] = rInfo;
	conf['part'] = 'getLangMgrUpdate';
	callPhpDefaultRequestHandler();
//	conf['data']['rInfo'] = '';
}
function deleteSysLang(id) {
	conf['part'] = 'deleteSysLang';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function updateSysLang(id) {
	conf['part'] = 'updateSysLang';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function installSysLang(id) {
	conf['part'] = 'installSysLang';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}

function getSkinMgrUpdate(rInfo) {
	if (rInfo) conf['data']['rInfo'] = rInfo;
	conf['part'] = 'getSkinMgrUpdate';
	callPhpDefaultRequestHandler();
}
function deleteSysSkin(id) {
	conf['part'] = 'deleteSysSkin';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function updateSysSkin(id) {
	conf['part'] = 'updateSysSkin';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}
function installSysSkin(id) {
	conf['part'] = 'installSysSkin';
	if (id) conf['data']['id'] = id;
	callPhpDefaultRequestHandler();
	if (id) conf['data']['id'] = '';
}

//***************************************
// Request / Event handler functions
//***************************************
function callPhpDefaultRequestHandler() {
	sessTimer.loginRefreshed();
	pageLoader(1);
	var h = new tupa_general(contentHandler);
	h.timeout = 120000;
	h.defaultrequesthandler(conf);
}
var contentHandler = {
	// Function must have same name as remote method
	defaultrequesthandler: function(resultArr) {
		switch(conf['part']) {
			case 'showData':
				defaultHandler(resultArr['content']);
				chr = conf['show']['char'];
				if (!resultArr['messages'] && chr != undefined && chr != 'dchar') {
					d.getElementsByName(chr)[0].className='char-active';
				}
				updateMultiMvDelBtn();
				break;
			case 'updateData':
				defaultHandler(resultArr['content'], 'div-show');
				updateMultiMvDelBtn();
				break;
			case 'updateLogMessages':
				defaultHandler(resultArr['content'], 'div-show');
				break;
			case 'addEditGroup':
			case 'deleteGroup':
			case 'deleteUser':
			case 'moveUser':
			case 'addEditTemplate':
			case 'moveTemplate':
			case 'addEditDomain':
			case 'moveDomain':
			case 'addDomainWithTemplate':
			case 'editUserPrefs':
			case 'showLogMessages':
			case 'showSysinfo':
			case 'showBackup':
			case 'showTools':
			case 'showSystemConfig':
				defaultHandler(resultArr['content']);
				break;
			case 'addEditUser':
				userAddEditHandler(resultArr['content']);
				break;
			case 'processGroup':
			case 'processUser':
			case 'processDomain':
			case 'processTemplate':
				conf['data']['id'] = '';
				moveCount = 0;
				delCount = 0;
				break;
			case 'updateShortSysinfo':
				updateShortSysinfoHandler(resultArr['content']);
				break;
			case 'getusersofgroup':
			case 'getipsoftype':
				updateSelectField(resultArr['content']);
				cleanMsgs = false;
				break;
			case 'getdomainexample':
				defaultHandler(resultArr['content'], 'div-domain-ex-records');
//				updateDomainExample(resultArr['content']);
				break;
			case 'getLangMgrUpdate':
				defaultHandler(resultArr['content'], 'TAB_systemLangManager');
				cleanMsgs = false;
				break;
			case 'getSkinMgrUpdate':
				defaultHandler(resultArr['content'], 'TAB_systemSkinManager');
				cleanMsgs = false;
				break;
		}
		resultArr['messages'] ? messageHandler(resultArr['messages']) : '';
		pageLoader(0);
	}
};
var shortSysinfoHandler = {
	updateshortsysinfo: function(result) {
		clear('sysinfo');
		d.getElementById('sysinfo').innerHTML = result;
	}
};

function defaultHandler(result, divId) {
	if (!divId) divId = 'content';
	clear(divId);
	d.getElementById(divId).innerHTML = result;
}
/*
function defaultHandler(result) {
	clear('content');
	d.getElementById('content').innerHTML = result;
}
function updateHandler(result) {
	clear('div-show');
	d.getElementById('div-show').innerHTML = result;
}
function updateDomainExample(result) {
	clear('div-domain-ex-records');
	d.getElementById('div-domain-ex-records').innerHTML = result;
}
*/
function userAddEditHandler(result) {
	clear('content');
	d.getElementById('content').innerHTML = result;
	if (d.formdata.cmd.value == 'update') {
		formElements = d.formdata.elements;
		for (i=0;i<formElements.length;i++) {
			chkb = formElements[i];
			chkb.checked && chkb.onchange ? chkb.onchange() : '';
		}
	}
}
function messageHandler(result) {
	if (cleanMsgs) cleanMessages();
	for(i=0;i<result.length;i++) {
 		addMessage(result[i]['type'], result[i]['content']);
		if (result[i]['additionalTask']) eval(result[i]['additionalTask']);
	 }
	 cleanMsgs = true;
}
function addMessage(type, content) {
	if (content && d.getElementById('messages') != undefined) {
		errorElem = d.createHTMLElement( 'li', { className: type, 'innerHTML': content } );
		d.getElementById('messages').appendChild( errorElem );
		d.getElementById('messages').style.display = "block";
	}
}

function updateSelectField(result) {
	field.length = '0';
	for( var i=0; i< result.length; i++) {
//		if (result[i]['name'] && result[i]['firstname']) {
//			field[i] = new Option(result[i]['name'] + ' ' + result[i]['firstname'], result[i]['id'], false);
		if (result[i]['username']) {
			field[i] = new Option(result[i]['username'], result[i]['id'], false);
		} else if (result[i]['content']) {
			field[i] = new Option(result[i]['content'], result[i]['value'], false);
		} else {
			field[i] = new Option(result[i], result[i], false);
		}
	}
}


//***************************************
// Help Layer
//***************************************
function findPosX(obj) {
	curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj) {
	curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop;
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

function showHelpLayer(obj, content, header) {
	layer = 'helpLayer';
	layerContent = '<table>';
	if (header) layerContent += '<tr><td class="header">' + header + '</td></tr>';
	layerContent += '<tr><td class="content">' + content + '</td></tr></table>';
	newX = findPosX(obj);
	newY = findPosY(obj);
	newX += obj.width + 5;
	x = d.getElementById(layer);
	x.style.top = newY + 'px';
	x.style.left = newX + 'px';
	x.innerHTML = layerContent;
	x.style.display = 'block';
}
function hideHelpLayer() {
	layer = 'helpLayer';
	x = d.getElementById(layer);
	x.style.display = 'none';
}


isIE=d.all;
isHot=false;

function ddInit(e){
	topDog=isIE ? "BODY" : "HTML";
	whichDog=isIE ? d.all.helpLayer : d.getElementById('helpLayer');
	hotDog=isIE ? event.srcElement : e.target;
//	while (hotDog.id!="helpHeader"&&hotDog.tagName!=topDog){
//		hotDog=isIE ? hotDog.parentElement : hotDog.parentNode;
//	}
	if (hotDog.id=="helpHeader"){
		offsetx=isIE ? event.clientX : e.clientX;
		offsety=isIE ? event.clientY : e.clientY;
		nowX=parseInt(whichDog.style.left);
		nowY=parseInt(whichDog.style.top);
		ddEnabled=true;
		document.onmousemove=dd;
	}
}

function dd(e){
	if (!ddEnabled) return;
	newX = isIE ? nowX+event.clientX-offsetx : nowX+e.clientX-offsetx;
	whichDog.style.left=newX +'px';
	whichDog.style.top=isIE ? nowY+event.clientY-offsety : nowY+e.clientY-offsety+'px';
	return false;
}

function hideMe(){
	whichDog.style.display='none';
}

function showHelpPopup(content, header){
	layer = 'helpLayer';
	if (!header) header = '';
	layerContent = '<table>';
	layerContent += '<tr class="header"><td style="cursor:move" width="100%" id="helpHeader"><ilayer width="100%" onSelectStart="return false"><layer width="100%" onMouseover="isHot=true" onMouseout="isHot=false">' + header + '</layer></ilayer></td><td><a href="javascript:void(0);" onClick="hideHelpLayer();return false">X</a></td></tr>';
	layerContent += '<tr><td colspan="2" class="content">' + content + '</td></tr></table>';
	x = d.getElementById(layer);
	x.innerHTML = layerContent;
	whichDog.style.display='block';
}

document.onmousedown=ddInit;
document.onmouseup=Function("ddEnabled=false");