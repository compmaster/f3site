if(!window.HTMLTableElement) HTMLTableElement = {};

//Sorting tables
function Sortable(table, types)
{
	var thead, self = this;
	if(typeof table == 'string')
	{
		table = $(table)
	}
	if(thead = table.tHead)
	{
		this.head = thead.rows[0].cells
	}
	else
	{
		throw new Exception('No THEAD tag')
	}
	for(var i=0; i<this.head.length; i++)
	{
		if(this.head[i].className)
		{
			this.head[i].style.cursor = 'pointer';
			this.head[i].onmousedown = function() { self.sort(this.cellIndex+1, this.className); return false };
		}
	}
	this.table = table;
	this.rows = table.tBodies[0].rows;
	this.body = table.tBodies[0];
	this.data = new Array(this.rows.length);
	this.types = (types instanceof Object) ? types : {};
	this.done = 0;
	this.last = 0;
	this.dir = {};
}

//Prepare data - get all cols in a row
Sortable.prototype.prepare = function()
{
	for(var x = 0; x < this.rows.length; x++)
	{
		var cols = this.rows[x].cells;
		this.data[x] = [this.rows[x]];
		this.dir[x] = 0;
		for(var y = 0; y < cols.length; y++)
		{
			this.data[x].push(cols[y].textContent || cols[y].innerText);
		}
	}
	this.on = 1;
};

//Sort table
Sortable.prototype.sort = function(index, type)
{
	//Prepare data only once
	if(!this.on) this.prepare();

	//Sort the array using callback function - not by the same column
	if(this.last != index) this.data.sort(function(a, b)
	{
		if(type == 'numeric')
		{
			return a[index] - b[index];
		}
		if(a[index] > b[index])
		{
			return 1;
		}
		if(a[index] < b[index])
		{
			return -1;
		}
		return 0
	});

	//Moved rows
	var moved = {}, old, oldNext, i2, len = this.data.length;

	//Move rows
	if(this.dir[index])
	{
		for(var i=len-1; i>=0; i--)
		{
			this.body.appendChild(this.data[i][0]);
		}
		this.dir[index] = 0;
	}
	else
	{
		for(var i=0; i<len; i++)
		{
			this.body.appendChild(this.data[i][0]);
		}
		this.dir[index] = 1;
	}
	this.last = index;
};