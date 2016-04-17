var Comments = {
	onload: function() {
		var table = document.querySelector('.comments');
		var pages = table.querySelector('.pages');
	},
	onclick: function(e) {
		e = e||event;
		var target = e.target||e.srcElement;
		switch(target.alt || target.id) {
			case 'E': Comments.edit(target.href || target.parentNode.href); break;
			case '+': Comments.accept(target.href || target.parentNode.href); break;
			case '-': Comments.del(target.href || target.parentNode.href); break;
			case 'addComment': Comments.form(target.href, target); break;
			default: return; //TODO:pages
		}
		if(e.preventDefault) e.preventDefault();
		return false;
	},
	edit: function(url) {
		var dialog = new Dialog('-','',600,450);
		dialog.load(url);
	},
	del: function(url) {
		if(confirm(lang.del)) {
			new Request(url, null, {done: function(x) {
				if(x === 'OK') {
					o.parentNode.parentNode.parentNode.removeChild(o.parentNode.parentNode);
				}
			}}).post({act:'del'});
		}
	},
	form: function(url, o) {
		var http = new Request(url);
		var div = document.createElement('div');
		div.innerHTML = loadgif.outerHTML;
		http.done = function(x)
		{
			if(x.indexOf('<form') == -1)
			{
				http.scripts = 0;
				onbeforeunload = null;
				$('comments').innerHTML = x;
				if(window.prettyPrint) prettyPrint();
			}
			else
			{
				div.innerHTML = x;
				var f = document.forms['comm'];
				f.prev.onclick = function() { return http.sendForm(this) };
				f.save.onclick = function() { return http.sendForm(this) };
				if(f.name) f.name.focus(); else f.text.focus();
			}
		};
		http.scripts = 1;
		http.send();
		o.parentNode.replaceChild(div, o);
	}
};

$('comments').onclick = Comments.onclick;
//$('addComment').onclick = Comments.form;

/*var Comments =
{
	form: function(href,box)
	{
		if(this.f) {}
		else
		{
			if(!box)
			{
				box = document.createElement('div');
				$('comments').parentNode.insertBefore(box, $('comments').nextSibling)
			}
			var http = new Request(href);
			http.done = function(x)
			{
				box.innerHTML = x
				//$('com').innerHTML = x;
				//var f = document.forms['comm'];
				//f.prev.onclick = function() { return http.sendForm(this) };
				//f.save.onclick = function() { return http.sendForm(this) };
				//if(f.name) f.name.focus(); else f.text.focus();
				//http.scripts = 1;
			}
			http.send()
		}
	}
};

function comment(cite)
{
	if(!window.comForm)
	{
		
	}
	var http = new Request(o.href, $('com'));

	//Przejmij kontrole nad formularzem
	http.done = function(x)
	{
		if(x.indexOf('<form') == -1)
		{
			http.scripts = 0;
			onbeforeunload = null;
			$('comments').innerHTML = x;
			if(window.prettyPrint) prettyPrint();
		}
		else
		{
			$('com').innerHTML = x;
			var f = document.forms['comm'];
			f.prev.onclick = function() { return http.sendForm(this) };
			f.save.onclick = function() { return http.sendForm(this) };
			if(f.name) f.name.focus(); else f.text.focus();
			http.scripts = 1;
		}
	};
	http.send();

	//Zastap link tekstem: czekaj
	o.parentNode.innerHTML = lang.wait;
	return false;
}

//Akcja komentarza
function coma(act,o)
{
	if(act == 'ok' || confirm(lang.del))
	{
		new Request(o.href, null, {post: 1, done: function(x)
		{
			if(x == 'OK')
			{
				if(act == 'del') o.parentNode.parentNode.parentNode.removeChild(o.parentNode.parentNode);
				else o.parentNode.removeChild(o);
			}
			else alert(x);
		} }
		).post({act:act});
	}
	return false;
}

$('comments').onclick = function(e)
{
	e = e||event;
	var tag = e.target||e.srcElement, ajax;
	switch(tag.alt || tag.className)
	{
		case '+':
			ajax = new Request(tag.parentNode.href, null, {
				fail: function(x) { alert(x); show(tag) }
			});
			show(tag);
			ajax.post({act:'ok'});
			if(e.preventDefault) e.preventDefault();
			return false;
		case '-':
			if(confirm(lang.del))
			{
				ajax = new Request(tag.parentNode.href, null, {
					fail: function(x) { alert(x); show(tag.parentNode.parentNode.parentNode) },
					done: function(x) { if(x!='OK') { alert(x); show(tag.parentNode.parentNode.parentNode) } }
				});
				show(tag.parentNode.parentNode.parentNode);
				ajax.post({act:'del'});
			}
			if(e.preventDefault) e.preventDefault(); return false; break;
		case 'E':
			var dialog = new Dialog(lang.edit,'',600,400);
			dialog.load(tag.parentNode.href);
			if(e.preventDefault) e.preventDefault(); return false; break;
		case 'addComment':
			Comments.form(tag.href, $('comForm'));
			if(e.preventDefault) e.preventDefault();
			return false;
		case 'cite':
			
	}
}

*/
var loadgif = new Image;
loadgif.src = 'img/icon/ajax.gif';
loadgif.style.display = 'block';
loadgif.style.margin = 'auto';

include('cache/emots.js');
include('lib/editor.js');
include('lang/' + document.documentElement.lang + '/edit.js');