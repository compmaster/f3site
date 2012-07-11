//Icon position, BBCode, end BBCode, HTML, end HTML
var tags = [
	[0, '[b]', '[/b]', '<b>', '</b>'],
	[-22, '[i]', '[/i]', '<i>', '</i>'],
	[-44, '[u]', '[/u]', '<u>', '</u>'],
	[-66, '[big]', '[/big]', '<big>', '</big>'],
	[-88, '[small]', '[/small]', '<small>', '</small>'],
	[-110, '== ', ' ==', '<h3>', '</h3>'],
	[-132, '[sub]', '[/sub]', '<sub>', '</sub>'],
	[-154, '[sup]', '[/sup]', '<sup>', '</sup>'],
	[-176, '[center]', '[/center]', '<div style="text-align: center">', '</div>'],
	[-198, '[right]', '[/right]', '<div style="text-align: right">', '</div>'],
	[-220, '[quote]', '[/quote]', '<blockquote>', '</blockquote>'],
	[-242, '[code]', '[/code]', '<pre>', '</pre>'],
	[-264],
	[-286],
	[-308],
	[-330],
	[-352],
],

//Special character table
symbol = [
	'°', '§', '¤', '÷', '×', '&frac12;',
	'Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü',
	'®', '©', '™', '•', '&lt;', '&gt;',
	'«', '»', '&lArr;', '&rArr;', '·', 'µ',
	'&#945;', '&#946;', '&#947;', '&#949;', '&#916;', '&#937;',
	'€', '&hearts;', '‰', '&#9835;', '†'
],

//Colors
color = [
	'white', '#c9c9c9', 'yellow', 'orange', 'red', '#9de9f9', '#7eebaa', 'teal',
	'black', 'gray', 'olive', 'gold', 'brown', 'blue', 'green', 'navy'
];

//Konstruktor
function Editor(o, usebbcode)
{
	this.o = o;
	this.bbcode = usebbcode;
	this.create();
}

//Create editor
Editor.prototype.create = function()
{
	var that = this,
	IE7 = navigator.userAgent.indexOf('MSIE') > 0 && charAt(navigator.userAgent.indexOf('MSIE')+5) < 8,
	out = document.createElement('div');
	out.className = 'editor';

	for(var i=0; i<tags.length; i++)
	{
		if(this.bbcode && tags[i] && tags[i][1] === false) continue;
		var button = document.createElement('span');
		if(IE7)
		{
			button.style.display = 'block';
			button.style.styleFloat = 'left'
		}
		button.style.backgroundPosition = 'center ' + tags[i][0] + 'px';
		button.style.padding = '3px 12px'; //TODO CSS
		button.item = i;
		button.title = tips[i];
		button.onclick = function() { that.format(this.item); };
		out.appendChild(button)
	}
	this.o.parentNode.insertBefore(out,this.o);

	//Keyboard shortcuts
	this.o.onkeydown = function(e)
	{
		if(e == undefined) e = event;
		if(e.ctrlKey && !e.altKey)
		{
			switch(e.keyCode)
			{
				case 66: that.format(0); break; //B
				case 73: that.format(1); break; //I
				case 85: that.format(2); break; //U
				case 81: that.format(10); break; //Q
				case 76: that.format(14); break; //L
				case 72: that.format(5); break; //H
				case 13: BBC(this, document.documentElement.baseURI ? '<br />\n' : '<br>\n', ''); break; //BR
				case 80: if(!that.bbcode) BBC(this, '<p>', '</p>'); break; //P
				default: return true
			}
			return false
		}
	};
	
	//Find preview button
	if(this.o.form && this.o.form.preview)
	{
		addEvent('click', function() { that.preview() }, this.o.form.preview)
	}
};

//Protect against exit
Editor.prototype.protect = function(text)
{
	var self = this;
	onbeforeunload = function(e)
	{
		if(self.o.value != 0)
		{
			if(e) e.returnValue = text||lang.leave;
			return text||lang.leave;
		}
	};
	addEvent('submit', function() { onbeforeunload = null }, this.o.form)
};

//Insert tag
Editor.prototype.format = function(i)
{
	if(tags[i][1])
	{
		if(this.bbcode)
			BBC(this.o, tags[i][1], tags[i][2]);
		else
			BBC(this.o, tags[i][3], tags[i][4]);
	}
	else switch(i)
	{
		case 13:
			this.showChars();
			break;
		case 12:
			this.showColors();
			break;
		case 14:
			this.link();
			break;
		case 15:
			this.mail();
			break;
		case 16:
			var a = prompt(lang.img);
			if(a)
				if(this.bbcode)
					BBC(this.o, '[img]', '[/img]\n', a);
				else
					BBC(this.o,'<img src="','">');
	}
};

//Show emoticons
Editor.prototype.emots = function(x)
{
	if(!window.emots && !Editor.gotEmots)
	{
		var that = this;
		include('cache/emots.js', function() { that.emots() });
		Editor.gotEmots = true;
		return;
	}
	if(this.emo)
	{
		this.emo.style.display = x===false || x===0 ? 'none' : '';
		return
	}

	//Build emoticon table
	var o = this.o,
	num = emots.length,
	out = document.createElement('div');
	out.className = 'editor emots';

	for(var i=0; i<num; ++i)
	{
		var img = document.createElement('img');
		img.src = 'img/emo/'+emots[i][1];
		img.alt = emots[i][2];
		img.width = 16;
		img.title = emots[i][0];
		img.onclick = function() { BBC(o, '', '', this.alt); };
		out.appendChild(img)
	}
	this.emo = this.o.parentNode.insertBefore(out, this.o.nextSibling)
};

//Show color table
Editor.prototype.showColors = function()
{
	if(!this.colorBox)
	{
		var self = this,
		t = document.createElement('table');
		t.style.cursor = 'pointer';
		t.className='hint';
		t.cellSpacing = 1;
		
		for(var j=10; j<=100; j+=20)
		{
			var tr = document.createElement('tr');
			for(var i=0; i<=330; i+=30)
			{
				var td = document.createElement('td');
				td.style.backgroundColor = 'hsl('+i+','+j+'%,50%)';
				td.innerHTML = ' ';
				td.width = td.height = 20;
				td.item = 'hsl('+i+','+j+'%,50%)';
				td.onclick = function()
				{
					if(self.bbcode)
					{
						BBC(self.o, '[color='+this.item+']', '[/color]')
					}
					else
					{
						BBC(self.o, '<span style="color: '+this.item+'">', '</span>')
					}
				};
				tr.appendChild(td);
			}
			t.appendChild(tr);
		}
		this.colorBox = t;
		document.body.appendChild(t)
	}
	hint(this.colorBox, cx-90, cy, 1);
};

//Build special character picker
Editor.prototype.showChars = function()
{
	if(!this.charBox)
	{
		var self = this,
		t = document.createElement('table');
		t.style.cursor = 'pointer';
		t.cellSpacing = 1;
		t.className = 'hint';
		
		for(var i=33; i<=256; i+=16)
		{
			var tr = document.createElement('tr');
			for(var j=0; j<16; ++j)
			{
				var td = document.createElement('td');
				td.innerHTML = '&#'+(i+j)+';';
				td.width = td.height = 20;
				td.onclick = function()
				{
					if(self.bbcode)
					{
						BBC(self.o, '[color='+this.innerHTML+']', '[/color]')
					}
					else
					{
						BBC(self.o, '<span style="color: '+this.innerHTML+'">', '</span>')
					}
				};
				tr.appendChild(td);
			}
			t.appendChild(tr);
		}
		this.charBox = t;
		document.body.appendChild(t)
	}
	hint(this.charBox, cx-90, cy, 1);
};

Editor.prototype.link = function()
{
	var url = prompt(lang.adr, 'http://');
	if(url && url != 'http://')
	{
		if(this.o.selectionStart != this.o.selectionEnd)
		{
			var title = ''
		}
		else
		{
			var input = prompt(lang.adr2), title = input ? input : url
		}
		if(this.bbcode)
		{
			BBC(this.o, (title == url) ? '[url]' : '[url='+url+']', '[/url]', title)
		}
		else
		{
			BBC(this.o, '<a href="'+encodeURI(url)+'">', '</a>', title)
		}
	}
	else this.o.focus()
};

Editor.prototype.mail = function()
{
	if(this.o.selectionStart != this.o.selectionEnd)
	{
		var input = undefined
	}
	else
	{
		var input = prompt(lang.mail)
	}
	if(input != '')
	{
		if(this.bbcode)
		{
			BBC(this.o, '[mail]', '[/mail]', input)
		}
		else
		{
			BBC(this.o, '<a href="mailto:'+input+'">', '</a>', input)
		}
	}
	else this.o.focus()
};

//Show preview
Editor.prototype.preview = function(opt,where,text)
{
	//Tekst
	if(text == undefined) text = this.o.value;

	//DIV
	if(this.box == undefined && !where)
	{
		this.box = document.createElement('div');
		this.box.className = 'preview';
		this.o.form.parentNode.insertBefore(this.box, this.o.form)
	}

	//Brak opcji
	if(opt == undefined) opt = {NL:1};

	//Nowe linie
	this.box.style.whiteSpace = (opt.NL != undefined && opt.NL == false) ? '' : 'pre-wrap';

	//HTML
	if(this.bbcode)
	{
		text = text.replace(/&/g, '&amp;');
		text = text.replace(/</g, '&lt;');
		text = text.replace(/>/g, '&gt;')
		for(var re,i=0; i<12; i++)
		{
			re = (tags[i][1]+'(.*?)'+tags[i][2]).replace(/[\[\]]+/g,'\\$&');
			text = text.replace(RegExp(re,'gi'), tags[i][3]+'$1'+tags[i][4])
		}
		var furl = function(match,txt)
		{
			var url = txt;
			if(url.indexOf('www.')===0) url = 'http://'+url;
			if(txt.length>30) txt = txt.substr(0,21)+'...'+txt.substr(-8);
			return txt.link(url)
		};
		var fsurl = function(match,space,text)
		{
			var last = text.slice(-1), post = '';
			while(last==='.' || last===',')
			{
				post += last;
				text = text.slice(0,-1);
				last = text.slice(-1);
			}
			return space + furl(match,text) + post
		};
		var ffurl = function(match,url,txt)
		{
			if(url.indexOf('www.')===0) url = 'http://'+url;
			return txt.link(url)
		};
		var fimg = function(match,tag,url)
		{
			if(url.indexOf(document.baseURI)===-1 && url.indexOf('.php')===-1)
			{
				if(tag==='video')
				{
					return '<video controls src="'+url+'" style="width: 100%"><a href="'+url+'">video</a></video>'
				}
				return '<img src="'+url+'" alt="Image" style="max-width: 100%">'
			}
			return 'Bad '+tag
		};
		text = text.replace(/\[color=([A-Za-z0-9#].*?)\](.*?)\[\/color\]/gi, '<span style="color:$1">$2</span>');
		text = text.replace(/\[url]([^\]].*?)\[\/url\]/gi, furl);
		text = text.replace(/\[url=([^\]]+)\](.*?)\[\/url\]/gi, ffurl);
		text = text.replace(/(^|[\n ])(www\.[a-z0-9\-]+\.[\w\#$%\&~\/.\-;:=,?@\[\]+]*)/gim, fsurl);
		text = text.replace(/(^|[\n ])([\w]+?:\/\/[\w\#$%\&~\/.\-;:=,?@\[\]+]*)/gim, fsurl);
		text = text.replace(/\[email\]([\w\-\.@]+?)\[\/email\]/g, '<a href="mailto:$1">$1</a>');
		text = text.replace(/([\n ])([\w\-\.]+@[\w\-]+\.([\w\-]+\.)*[\w]+)/g, '$1<a href="mailto:$2">$2</a>');
		text = text.replace(/\[(video)\](https?:\/\/[^\s<>"].*?)\[\/video\]/gi, fimg);
		text = text.replace(/\[(img)\](https?:\/\/[^\s<>"].*?)\[\/img\]/gi, fimg);
	}

	//Emotikony
	if(opt.EMOTS || this.bbcode)
	{
		for(var i in emots)
		{
			while(text.indexOf(emots[i][2]) !== -1 && emots[i][2] != '')
			{
				text = text.replace(emots[i][2],'<img src="img/emo/'+emots[i][1]+'" />');
			}
		}
	}

	//Wyświetl
	this.o.focus();
	this.box.innerHTML = text;
	this.box.scrollIntoView()
};