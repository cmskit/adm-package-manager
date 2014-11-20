<?php
$model = '';
if (isset($_GET['project']))
{
	require '../../../header.php';
	@include $frontend . '/objects/__modelxml.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>cms-kit xml2json</title>
<meta charset="utf-8" />
<style>
	body{background: #eee;font:.9em "Trebuchet MS", sans-serif;}
	a, a:visited{text-decoration:none;color:blue;}
	textarea {
		float:left;
		width: 48%;
		height: 400px;
	}
	hr {
		clear: both;
	}
</style>

<script src="JsonXml.js"></script>
</head>
<body>
<textarea id="a"><?php echo htmlspecialchars($model)?></textarea>
<textarea id="b"></textarea>
<hr />
<input type="button" value="transfer" onclick="transfer()" />
<script>
function unesc(str)
{
	return decodeURIComponent((str + '').replace(/\+/g, '%20'));
}
function esc(str)
{
	str = (str + '').toString();
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
	replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}

function transfer() {
	var xml = document.getElementById('a').value;//.replace(/\t/g,'');
	
	var objects = xmlJsonClass.xml2json(parseXml(xml), '', true);
	document.getElementById('b').value = JSON.stringify(objects, null, "  ").replace(/"@/g,'"');
}

function parseXml(xml)
{
	var dom = null;
	if (window.DOMParser)
	{
		try { 
			dom = (new DOMParser()).parseFromString(xml, "text/xml"); 
		} catch (e) {
			dom = null;
		}
	}
	else if (window.ActiveXObject)
	{
		try {
			dom = new ActiveXObject('Microsoft.XMLDOM');
			dom.async = false;
			if (!dom.loadXML(xml)) {
				alert(dom.parseError.reason + dom.parseError.srcText);// parse error ..
			}
		} catch (e) {
			dom = null;
		}
	}
	else
	{
		alert("could_not_parse_XML!");
	}
	return dom;
};
</script>
</body>
</html>
