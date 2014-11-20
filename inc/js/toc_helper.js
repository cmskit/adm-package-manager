
/**
* nice toc-script from http://www.quirksmode.org
*/

function getElementsByTagNames(list, obj) {
	if (!obj) var obj = document;
	var tagNames = list.split(',');
	var resultArray = new Array();
	for (var i=0;i<tagNames.length;i++) {
		var tags = obj.getElementsByTagName(tagNames[i]);
		for (var j=0;j<tags.length;j++) {
			resultArray.push(tags[j]);
		}
	}
	var testNode = resultArray[0];
	if (!testNode) return [];
	if (testNode.sourceIndex) {
		resultArray.sort(function (a,b) {
				return a.sourceIndex - b.sourceIndex;
		});
	}
	else if (testNode.compareDocumentPosition) {
		resultArray.sort(function (a,b) {
				return 3 - (a.compareDocumentPosition(b) & 6);
		});
	}
	return resultArray;
}

function createTOC() {
	var d = document.createElement('span');//dummy
	var y = document.createElement('div');
	y.id = 'innertoc';
	var a = y.appendChild(document.createElement('span'));
	a.onclick = showhideTOC;
	a.id = 'contentheader';
	a.innerHTML = '&lt;&lt;';
	var z = y.appendChild(document.createElement('div'));
	z.style.display = 'none';
	z.onclick = showhideTOC;
	var toBeTOCced = getElementsByTagNames('h1,h2,h3,h4,h5');
	var toBeTOCcedLength = toBeTOCced.length;
	
	// write the first headline to the title (for bookmarking)
	if (toBeTOCcedLength > 0) document.title = toBeTOCced[0].innerHTML;
	
	
	
	// if toc is too long make it scrollable
	var h = 'innerHeight' in window 
		   ? window.innerHeight
		   : document.documentElement.offsetHeight;
	if (toBeTOCcedLength > (h/25)) {
		y.setAttribute('style','max-height:'+(h-25)+'px;overflow:auto')
	}
	
	if (!toBeTOCcedLength || toBeTOCcedLength < 2) return d;

	for (var i=0; i<toBeTOCcedLength; ++i)
	{
		var tmp = document.createElement('a');
		tmp.innerHTML = toBeTOCced[i].innerHTML;
		tmp.className = 'page';
		z.appendChild(tmp);
		tmp.className += ' ind'+toBeTOCced[i].nodeName;
		var headerId = toBeTOCced[i].id || 'link' + i;
		tmp.href = '#' + headerId;
		toBeTOCced[i].id = headerId;
	}
	return y;
}

var TOCstate = 'none';

function showhideTOC() {
	TOCstate = (TOCstate == 'none') ? 'block' : 'none';
	var newText = (TOCstate == 'none') ? '&lt;&lt;' : '&gt;&gt;';
	document.getElementById('contentheader').innerHTML = newText;
	document.getElementById('innertoc').lastChild.style.display = TOCstate;
}
