<?php
class RSS
{
	public
		$title, //Title of RSS
		$link,  //URL
		$base,  //Base URL
		$desc;  //Description of RSS
	private
		$data = array(),
		$xml;

	function add($x)
	{
		$this->data[] = $x;
	}

	function save($file)
	{
		if(!file_exists($file) && !touch($file))
		{
			throw new Exception(sprintf('File %s does NOT exist!', $file));
		}
		elseif(!is_writable($file))
		{
			throw new Exception(sprintf('Cannot write to %s - CHMOD it 766!', $file));
		}
		$xml = new SimpleXMLElement('<rss version="2.0" xml:base="'.$this->base.'"></rss>');
		$rss = $xml->addChild('channel'); 
		$rss->title = $this->title;
		$rss->link  = $this->link;
		$rss->description = $this->desc;

		foreach($this->data as $x)
		{
			$item = $rss->addChild('item');
			$item->title = $x['title'];
			$item->description = $x['text'];
			$item->link = $x['url'];

			#Optional tags
			if(isset($x['author'])) $item->author = $x['author'];
			if(isset($x['category'])) $item->category = $x['cat'];
			if(isset($x['date'])) $item->pubDate = $x['date'];
			if(isset($x['id'])) { $item->id = $x['ID']; $item->id['isPermaLink'] = 'false'; }
		}
		#Save file
		return $xml->asXML($file);
	}
}