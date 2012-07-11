function put(o)
{
	opener.focus();
	opener.FM.focus();
	opener.FM.value = document.getElementsByTagName('tt')[0].innerHTML + o.parentNode.parentNode.getElementsByTagName('a')[0].innerHTML;
};

function add()
{
	var files = document.forms[0].elements;
	var last = files[files.length - 3];
	var node = document.createElement('input');
	node.type = 'file';
	node.name = 'file[]';
	node.multiple = 1;
	node.size = last.size;
	last.parentNode.insertBefore(node, last.nextSibling);
	node.focus()
}