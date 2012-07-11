//File for edit
//Compress it: http://dean.edwards.name/packe0

//Open centered window
function okno(url, width, height)
{
	return open(url, '', 'scrollbars=yes,width='+width+',height='+height+',top='+(screen.height-height)/2+',left='+(screen.width-width)/2)
}

//Change main CSS
function CSS(x)
{
	if(x)
	{
		var link = document.getElementsByTagName('link')[0];
		link.href = link.href.slice(0,-5) + x + '.css';
		setCookie('CSS', x, 3600)
	}
}

//Include JS file async - loaded event optional
function include(file, loaded)
{
	if(file.indexOf('.css') > 0)
	{
		var js = document.createElement('link');
		js.type = 'text/css';
		js.rel = 'stylesheet';
		js.href = file;
	}
	else
	{
		var js = document.createElement('script');
		js.type = 'text/javascript';
		js.src = file;
	}
	if(loaded)
	{
		if(js.readyState)
		{
			js.onreadystatechange = function()
			{
				if(js.readyState == 'complete' || js.readyState == 'loaded')
				{
					loaded(); js.onreadystatechange = null
				}
			}
		}
		else js.onload = loaded;
	}
	document.getElementsByTagName('head')[0].appendChild(js)
}

//Add event - IE and W3
function addEvent(type, f, o, capture)
{
	if(window.addEventListener)
	{
		(o||window).addEventListener(type, f, capture||false)
	}
	else if(window.attachEvent)
	{
		(o||window).attachEvent('on'+type, f)
	}
	else if(!o['on'+type])
	{
		(o||window)[type] = f
	}
}

//Fast access to element by ID - may be extended in the future
function $(x) { return x.nodeType ? x : document.getElementById(x) }

//Insert code
function BBC(o, left, right, inside)
{
	if(o.selectionStart != undefined)
	{
		var start  = o.selectionStart;
		var end    = o.selectionEnd;
		var scroll = o.scrollTop;
		var before = o.value.substring(0, start);
		var after  = o.value.substring(end, o.textLength);
		var inside = (inside) ? inside : o.value.substring(start, end);

		o.value = before + left + inside + right + after;
		o.selectionEnd = o.selectionStart = before.length + left.length + inside.length;
		o.scrollTop = scroll;
		o.focus()
	}
	else if(document.selection)
	{
		o.focus();
		var sel  = document.selection.createRange(),
		inside   = (inside) ? inside : sel.text;
		sel.text = left + inside + right;
	}
}

//Select all checkboxes
function selAll(o)
{
	var e = o.form.elements;
	for(var i=0; i<e.length; i++)
	{
		if(e[i].name && e[i].name.indexOf('x[')===0) e[i].checked = o.checked;
	}
}

//Make path from title
function genpath(title)
{
	return title.replace(/\W+/g,'_').replace(/__+/g,'_');
}

//Set cookie
function setCookie(name, txt, expires, path)
{
	var date = new Date();
	date.setTime(time = (expires*3600000) + date.getTime()); //expires = iloœæ godzin
	if(path == undefined)
	{
		path = document.getElementsByTagName('base')[0].href;
		path = path.substr(path.indexOf('/', 8));
	}
	document.cookie = name + '=' + escape(txt) + ';path=' + path + ';expires=' + date.toGMTString();
}

//Show or hide element
function show(o, once)
{
	if(typeof o == 'string') o = $(o);
	var x = o.style;
	if(x.display=='none') x.display=''; else if(once==undefined) x.display='none'
}

//Cursor coords
var cx,cy,toHide = [];

//Get mouse coords
document.onmousedown = function(e)
{
	if(e)
	{
		cx = e.pageX;
		cy = e.pageY
	}
	else
	{
		cx = event.clientX + document.documentElement.scrollLeft;
		cy = event.clientY + document.documentElement.scrollTop
	}
	if(cx<0) cx=0;
	if(cy<0) cy=0
};
document.onclick = function()
{
	for(var i=0; i<toHide.length; i++)
	{
		toHide[i].style.visibility = 'hidden';
		toHide.pop(toHide[i])
	}
};

//Show hint next to cursor or at given position
function hint(o, left, top, autoHide)
{
	if(typeof o == 'string')
	{
		o = $(o)
	}
	if(o.style.visibility != 'visible')
	{
		if(top != 0)
		{
			o.style.left = left + 'px';
			o.style.top  = top  + 'px'
		}
		o.style.visibility = 'visible';
		if(autoHide == 1) setTimeout(function() { toHide.push(o) }, 10);
	}
	else o.style.visibility = 'hidden';
}

//Parse JSON
function getJSON(x)
{
	if(window.JSON)
	{
		return JSON.parse(x)
	}
	else
	{
		return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(x.replace(/"(\\.|[^"\\])*"/g, ''))) && eval('('+x+')')
	}
}

//
// *** AJAX REQUESTS ***
//
function Request(url, box, opt)
{
	//Options
	opt = opt || {};

	//Output element - by default middle panel
	this.o = box || $('main');

	//Location URL
	this.url = url;

	//Parse script tags - disabled by default
	this.scripts = opt.scripts || false;

	//While response is downloaded event
	this.loading = opt.loading || null;

	//If request fails event
	this.fail = opt.fail || function(x) { alert(x) };

	//When request done successfully event
	this.done = opt.done || function(x) { this.o.innerHTML = x };

	//Maximum request time
	this.timeout = opt.timeout || 50000;
}

//Send request with GET
Request.prototype.get = function(list)
{
	this.send(list, false);
};

//Send request with POST
Request.prototype.post = function(list)
{
	this.send(list, true);
};

//Send request
Request.prototype.send = function(list, post)
{
	if(!this.http)
	{
		if(window.XMLHttpRequest) //XMLHttpRequest
		{
			this.http = new XMLHttpRequest();
			if(this.http.overrideMimeType) this.http.overrideMimeType('text/html');
		}
		else if(window.ActiveXObject) //IE
		{
			try
			{
				this.http = new ActiveXObject("Msxml2.XMLHTTP")
			}
			catch(e)
			{
				try
				{
					this.http = new ActiveXObject("Microsoft.XMLHTTP")
				}
				catch(e)
				{
					throw new Exception('AJAX is not supported!')
				}
			}
		}
		if(!this.http) throw new Exception('Cannot create AJAX object!');

		//Reference to THIS
		var self = this;

		//When request state changes
		this.http.onreadystatechange = function()
		{
			if(self.http.readyState == 4)
			{
				try
				{
					if(self.http.status == 200 || self.http.status == 0)
					{
						//Fire done event with response text
						self.done(self.http.responseText);

						//Parse <script> tags
						if(self.scripts)
						{
							var script = self.o.getElementsByTagName('script');
							for(var i=0; i<script.length; ++i)
							{
								if(script[i].src)
								{
									var d = document.createElement('script');
									d.src = script[i].src;
									script[i].appendChild(d)
								}
								else eval(script[i].innerHTML);
							}
						}
					}
					else
					{
						self.fail('Server is busy.')
					}
				}
				catch(e)
				{
					self.fail(e)
				}
				document.body.style.cursor = ''
			}
		};
	}
	//When URL not defined assume current location
	if(this.url == '') this.url = location.href;

	//Fire loading event
	if(this.loading) this.loading();

	//Change cursor to progress
	document.body.style.cursor = 'progress';

	//Open connection
	this.http.open(post ? 'POST' : 'GET', this.url, true);

	//Determine POST method
	if(post)
	{
		this.http.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	}
	this.http.setRequestHeader('X-Requested-With','XMLHttpRequest');

	//Send request with or without POST data
	if(typeof list == 'object')
	{
		var name,param = [];
		for(name in list) param.push(encodeURIComponent(name)+'='+encodeURIComponent(list[name]));
		this.http.send(param.join('&'))
	}
	else
	{
		this.http.send()
	}
};

//
// *** SEND FORM WITH AJAX ***
//

//Send form with temporary Request object
function send(o,id,opt)
{
	new Request(o.form.action, id, opt).sendForm(o);
	return false
}

//Take control over stars
function rate(o)
{
	for(var i=0; i<o.v.length; i++)
	{
		o.v[i].onclick = function()
		{
			send(this, '', {done: function(x) { alert(x) }});
			return false
		}
	}
}

//Send form with existing Request object
//Pass SUBMIT field as argument
Request.prototype.sendForm = function(o)
{
	var el = o.form.elements, x, param = {};
	for(var i=0; i<el.length; ++i)
	{
		x = el[i];
		switch(x.type || '')
		{
			case 'radio':
			case 'checkbox':
				if(x.checked) param[x.name] = x.value || 1; //Radio + Checkbox
				break;
			case 'text':
			case 'textarea':
			case 'hidden':
			case 'password':
				param[x.name] = x.value; //Text
				break;
			case 'select':
			case 'select-one':
			case 'select-multiple':
				for(var y=0; y<x.options.length; ++y)
				{
					if(x.options[y].selected) param[x.name] = x.options[y].value //Select
				}
				break;
		}
	}
	if(o.name) param[o.name] = o.value;
	this.post(param);
	o.disabled = 1;
	return false
};

//
// *** DIALOG WINDOWS WITH AJAX SUPPORT ***
//
function Dialog(title, txt, width, height)
{
	this.o = document.createElement('div');
	this.o.className = 'dialog';
	this.bg = document.createElement('div'); //Overlay
	this.bg.className = 'overlay';
	this.title = this.o.appendChild(document.createElement('h3'));
	this.title.ref = this;
	this.title.innerHTML = '<div class="exit" onclick="parentNode.ref.hide()">x</div>' + title;
	this.body = this.o.appendChild(document.createElement('div'));
	this.body.style.height = height - 20 + 'px';
	this.body.style.overflow = 'auto';

	//Width and height
	if(width) this.o.style.width = width + 'px';
	if(height) this.o.style.height = height + 'px';

	//Content
	if(txt) this.body.innerHTML = txt;
}
Dialog.prototype.show = function()
{
	if(this.o.parentNode != document.body)
	{
		document.body.appendChild(this.bg);
		document.body.appendChild(this.o);
		this.o.style.left = (document.documentElement.clientWidth - this.o.clientWidth) / 2 + 'px';
		this.o.style.top = (document.documentElement.clientHeight - this.o.clientHeight) / 2 + 'px'
	}
	curDialog = this
};
Dialog.prototype.hide = function()
{
	if(this.o.parentNode == document.body)
	{
		document.body.removeChild(this.bg);
		document.body.removeChild(this.o)
	}
};
Dialog.prototype.load = function(url, data, post)
{
	new Request(url, this.body, {scripts:1}).send(data,post);
	this.show()
}

//this instanceof arguments.callee ? 'object instance' : 'function call'