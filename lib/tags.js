TAGS={

all: {},
inputs: [],
box: $('tags'),
win: null,

//Show dialog window if user clicks DIV
manage: function(url,edit)
{
	if(!this.win)
	{
		this.win = new Dialog(lang.tags, '', 420, 380);
		this.http = new Request(url[1], this.win.body, {done: function(tags)
		{
			//Parse JSON code
			tags = getJSON(tags);

			//Change AJAX behavior
			TAGS.http.done = function(x)
			{
				x = getJSON(x);
				TAGS.box.removeChild(TAGS.edit);
				TAGS.box.innerHTML = '';

				//Build tags from scratch
				for(var i in x)
				{
					var a = document.createElement('a');
					a.href = url[0] + x[i];
					a.className = 'tags';
					a.innerHTML = x[i] + ' ['+(TAGS.all[x[i]] ? TAGS.all[x[i]] : '1')+'] ';
					TAGS.box.appendChild(a)
				}
				TAGS.box.appendChild(TAGS.edit);
			};

			//Build checkboxes
			for(var i=0, len=tags.length; i<len; i++)
			{
				TAGS.all[tags[i][0]] = tags[i][2];
				TAGS.create(tags[i][0], tags[i][1], tags[i][2], 1)
			}

			//New tag
			var newTag = TAGS.create(lang.add + '...', 0);
			newTag.onclick = function()
			{
				var txt = prompt(lang.addtag);
				if(txt!=null && txt!=0) TAGS.create(txt.substr(0,30), 1, 1, 1, this.parentNode.parentNode);
				this.checked = 0
			};

			//Build OK button
			var input = document.createElement('button');
			input.style.display = 'block';
			input.style.margin = '15px auto';
			input.innerHTML = '<b>OK</b>';
			input.onclick = function()
			{
				for(var i=0, len=TAGS.inputs.length, post=[]; i<len; i++)
				{
					if(TAGS.inputs[i].checked) post.push(TAGS.inputs[i].name)
				}
				TAGS.win.hide();
				TAGS.http.post(post)
			};
			TAGS.win.body.appendChild(input);
			TAGS.inputs[0].focus()
		}});
		this.http.get();
		this.edit = edit;
		addEvent('keydown', function(x) { if(x.keyCode==27) TAGS.win.hide() }, document)
	}
	this.win.show()
},

//Add tag
create: function(name, checked, num, toIndex, before)
{
	var div = document.createElement('div'),
	label = document.createElement('label'),
	input = document.createElement('input');
	input.type = 'checkbox';
	input.checked = checked;
	input.name = name;
	label.appendChild(input);
	label.appendChild(document.createTextNode(num ? name+' ('+num+')' : name));
	div.style.display = 'inline-block';
	div.style.width = '50%';
	div.appendChild(label);

	if(toIndex) this.inputs.push(input);
	if(before) this.win.body.insertBefore(div, before); else this.win.body.appendChild(div);
	return input;
}
}