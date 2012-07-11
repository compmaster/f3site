function Tabs(opt)
{
	this.tabs = [];
	this.activeTab = null;
	this.bodies = opt.o.children;
	this.length = this.bodies.length;
	this.onchange = opt.onchange || null;
	this.onadd = opt.onadd || null;
	this.title = opt.title || '%n';
	this.tabBox = document.createElement('div');
	
	for(var i=0; i<this.bodies; i++)
	{
		var tab = document.createElement('span');
		tab.innerHTML = this.bodies[i].getAttribute('data-title');
		tabBox.appendChild(tab);
	}
}

//Add new tab
Tabs.prototype.add = function(pos)
{
	if(this.onadd)
	{
		this.onadd();
	}
	var body = document.createElement('div');
	if(pos === undefined)
	{
		this.activeTab.parentNode.appendChild(body);
	}
	else
	{
		this.activeTab.parentNode.insertBefore(body, this.bodies[pos]);
	}
};