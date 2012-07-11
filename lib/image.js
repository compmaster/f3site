/*

1. Znajd¼ linki prowadz±ce do obrazków
	- a[rel=lightbox]
	- a img
	- a[href=png/jpg/]
	- a img wewn±trz okre¶lonej warstwy div
2. Nie istnieje jeszcze lightbox, zatem:
	- wywo³aj funkcjê create()
		- stwórz przyciemnienie
		- stwórz bia³± warstwê
		- stwórz obiekt img i przypnij do bia³ej warstwy
		- ustaw: build = true
3. Poka¿ obraz
	- je¶li za³adowany
		- podepnij przyciemnienie
		- wycentruj obraz
		- wprowad¼ href
		- wprowad¼ title
	- je¶li nie
		- wy¶wietl przyciemnienie
		- powiadom o ³adowaniu


*/

Lightbox = {

	//If active
	active: false,

	//If built
	built: false,

	//Detect links automatically
	autolink: true,

	//Fit to screen
	screen: true,

	//Search links in
	inside: 'main',

	//Images bank
	bank: {},

	//Display lightbox
	view: function(file)
	{
		if(!this.built) this.create();
		document.body.appendChild(this.overlay);
		document.body.appendChild(this.lightbox);
	},

	//Hide lightbox
	hide: function()
	{
		document.body.removeChild(this.overlay);
		document.body.removeChild(this.lightbox);
	},

	//Initialize the lightbox - first use
	create: function(src)
	{
		//Create overlay
		this.overlay = document.createElement('div');
		this.overlay.className = 'overlay';
		this.overlay.onclick = function() { Lightbox.hide() };

		//Create main lightbox layer
		this.lightbox = document.createElement('div');
		this.lightbox.className = 'lightbox';
		this.lightbox.style.zIndex = '99';
		this.lightbox.style.position = 'absolute';
		this.lightbox.style.backgroundColor = 'white'; //temp
		this.lightbox.style.width = '250px';
		this.lightbox.style.height = '250px';
		this.lightbox.style.top = (screen.);

		//Create full image object
		this.img = document.createElement('img');
		this.img.onload = function()
		{
			
		};

		//Append objects into overlay
		this.lightbox.appendChild(this.img);
	},
}