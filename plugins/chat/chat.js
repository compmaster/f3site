//Create new CHATROOM
function Chat(opt)
{
	var self = this;
	opt = opt || {};

	//Input field
	this.input = opt.inbox || $('chatIn');

	//Output box - may be table or div
	this.output = opt.outbox || $('chatOut');

	//Topic box
	this.topicBox = opt.topic || $('chatTopic');

	//Scrolled chat box - div recommended
	this.chatBox = this.output.parentNode;

	//Check if boxes exist
	if(!this.output) throw new Exception('Set "outbox" option as output element!');
	if(!this.input)  throw new Exception('Set "inbox" option as <input> element!');

	//Chatroom name
	this.room = opt.room || 'main';

	//Disallow HTML
	this.HTML = opt.HTML || false;

	//Your nickname
	this.nick = opt.nick || 'Guest';

	//Default nickname - if not specified
	this.anonym = '';

	//Last date and post ID
	this.lastTime = '';
	this.lastID = 0;

	//Maximum message length
	this.maxLength = opt.maxLength || 500;

	//Get messages every...
	this.timer = new Timer(function() { self.http.get() }, opt.interval || 8);

	//Default tags
	this.oneTag = opt.itemTag || 'tr';
	this.timeTag = opt.timeTag || 'td';
	this.nickTag = opt.nickTag || 'td';
	this.msgTag = opt.msgTag || 'td';

	//Default classes
	this.timeClass = opt.timeClass || 'chatTime';
	this.nickClass = opt.nickClass || 'chatNick';
	this.msgClass  = opt.msgClass || 'chatMsg';

	//AJAX request object
	this.http = new Request(opt.url || 'request.php?go=chat');

	//Response handler
	this.http.done = function(x)
	{
		x = getJSON(x);
		for(var i in x) self.insert(x[i])
	};

	//If failed
	this.http.fail = function(x) { alert(x) };

	//Input ENTER
	this.input.onkeydown = function(e)
	{
		if(e == undefined) e = event;
		if(e.keyCode == 13 && !e.shiftKey)
		{
			if(e.preventDefault) e.preventDefault();
			self.post();
			return false
		}
	};

	//Autofocus input
	this.input.focus();
	this.timer.start();
}

//Post a message
Chat.prototype.post = function()
{
	//Msg may NOT be empty
	if(this.input.value == '') return false;

	//Reset the timer
	this.timer.reset();

	//Post message and last time
	this.http.post({
		msg:  this.input.value,
		last: this.lastID
	});

	//Insert new post now
	this.insert({
		msg:  this.purify(this.input.value),
		nick: this.nick
	})

	//Clear input text
	this.input.value = '';
};

//Change nickname
Chat.prototype.setNick = function(x)
{
	this.nick = x
};

//Add message onto the board
Chat.prototype.insert = function(x)
{
	var one = document.createElement(this.oneTag),
	msg = document.createElement(this.msgTag),
	time = document.createElement(this.timeTag),
	nick = document.createElement(this.nickTag);

	//Message
	msg.className = this.msgClass;
	msg.innerHTML = x.msg || '';

	//Date object
	var date = x.date ? new Date(x.date) : new Date;
	var date = date.toLocaleTimeString().substr(0,5);

	//Do not show the same date
	if(this.lastTime == date) date = ''; else this.lastTime = date;

	//Post time
	time.className = this.timeClass;
	time.innerHTML = date;

	//Nickname
	nick.className = this.nickClass;
	nick.innerHTML = x.uid>0 ? this.profile(x.nick) : (x.nick || x.anonym);

	//Check if output box should be scrolled down
	var height = this.chatBox.offsetHeight + this.chatBox.scrollTop;
	var scroll = this.chatBox.scrollHeight < height;
	
	//Place the message
	one.appendChild(time);
	one.appendChild(nick);
	one.appendChild(msg);
	this.output.appendChild(one);

	//Set last ID
	this.lastID = x;

	//Scroll down the output box
	if(scroll) this.chatBox.scrollTop = this.chatBox.scrollHeight;
};

//Purify text - no HTML
Chat.prototype.purify = function(x)
{
	x = x.replace(/&/g, '&amp;');
	x = x.replace(/</g, '&lt;');
	return x.replace(/>/g, '&gt;');
};

//Generate link to profile
Chat.prototype.profile = function(nick, uid)
{
	return '<a href="user/' + encodeURI(nick) + '">' + nick + '</a>'
};

//Clear chat
Chat.prototype.clear = function()
{
	this.output.innerHTML = ''
}

//Interval overloader
function Timer(code, time)
{
	this.code = code;
	this.time = time || 10;
}
Timer.prototype.start = function(time)
{
	this.id = setInterval(this.code, (time || this.time)*1000)
};
Timer.prototype.stop = function()
{
	clearInterval(this.id)
};
Timer.prototype.reset = function(time)
{
	this.stop();
	this.start();
}