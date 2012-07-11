/* Mened¿er plików */

var FM = null;
var FMWin = null;

//Otwórz mened¿er plików
function fileman(folder, input)
{
	if(FMWin && !FMWin.closed)

		FMWin.focus();

	else if(input)
	{
		FMWin = okno('fileman.php?dir='+folder, 580, 400, 150, 150);
		window.onunload = function() { FMWin.close() }
	}
	FM = input;
}

//
// *** Fragment formularza do powielania ***
//
function Fragment(box,opt)
{
	var box  = $(box);
	var self = this;
	var opt  = opt || {};

	//Element, w którym znajduj± siê fragmenty
	this.box = box;

	//Lista fragmentów
	this.nodes = box.getElementsByTagName(opt.tag || 'div');
	
	//Aktualna ilo¶æ fragmentów
	this.num = this.nodes.length;

	//Maksymalna ilo¶æ fragmentów
	this.limit = opt.limit || 30;

	//Znacznik aktywowany po wykonaniu akcji
	this.focus = opt.focus || 'input'; 

	//Tryby: clean - czy¶ci pola po skopiowaniu fragmentu
	this.mode = opt.mode || null;

	//Kod HTML, który nale¿y wstawiæ po klikniêciu "Dodaj fragment"
	if(opt.html != undefined) this.html = opt.html;

	//Przesuwanie fragmentów za pomoc± klawiatury - CTRL + ...
	box.onkeydown = function(e)
	{
		e = e || event;
		var o = e.srcElement || e.target;
		if(!e.ctrlKey) return true;

		//Rodzicem przycisków jest element fragmentu
		var node = o.parentNode;

		switch(e.keyCode)
		{
			case 38: self.up(node); break;
			case 40: self.down(node); break;
			default: return true;
		}
		return false;
	};

	//Zdarzenie onclick dla obszaru powtarzanych elementów
	box.onclick = function(e)
	{
		e = e || event;
		var o = e.srcElement || e.target;
		if(!o.alt) return true;

		//Rodzicem przycisków jest element fragmentu
		var node = o.parentNode;

		//Wykryj, który przycisk wci¶niêto
		switch(o.alt)
		{
			case '+': self.copy(node); break;
			case '-': self.del(node);  break;
			case '^': case 'UP': self.up(node); break;
			case 'v': case 'DOWN': self.down(node); break;
			default: return true;
		}
		return false; //Nie wysy³aj formularza
	};
}

Fragment.prototype = {

	//Kopiuj fragment lub dodaj nowy
	copy: function(node)
	{
		if(this.num > this.limit) return false;
		if(this.html)
		{
			var newNode = this.html.cloneNode(true);
			newNode.style.display = 'block'
		}
		else
		{
			var newNode = node.cloneNode(true);
			if(this.mode == 'clean')
			{
				var list = newNode.getElementsByTagName('input');
				for(var i=0; i<list.length; i++) list[i].value = '';
			}
		}
		node.parentNode.insertBefore(newNode, node);
		this.num++;

		//Aktywuj pierwsze pole INPUT
		this.act(newNode);
	},

	//Usuñ fragment i uaktywnij s±siada
	del: function(node)
	{
		if(node.nextSibling)
		{
			this.act(node.nextSibling);
		}
		else if(node.previousSibling)
		{
			this.act(node.previousSibling);
		}
		node.parentNode.removeChild(node);
		this.num--;
	},

	//Przesuñ w górê
	up: function(node)
	{
		if(node.previousSibling)
		{
			node.parentNode.insertBefore(node, node.previousSibling);
		}
	},

	//Przesuñ w dó³
	down: function(node)
	{
		if(node.nextSibling)
		{
			node.parentNode.insertBefore(node.nextSibling, node);
		}
	},

	//Nowy element
	addItem: function()
	{
		if(this.num > this.limit || this.html == undefined) return false;
		var o = this.box.appendChild(this.html.cloneNode(true));
		o.style.display = 'block';
		this.act(o);
		this.num++;
		return o;
	},

	//Uaktywnij pierwszy element we fragmencie
	act: function(node)
	{
		if(node.nodeType == 1)
		{
			var list = node.getElementsByTagName(this.focus);
			if(list.length>0) list[0].focus();
		}
	}
}