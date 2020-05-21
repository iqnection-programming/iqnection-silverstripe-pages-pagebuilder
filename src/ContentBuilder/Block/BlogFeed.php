<?php

namespace IQnection\PageBuilder\ContentBuilder\Block;

use SilverStripe\Forms;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\FieldType;
use SilverStripe\Control\Controller;

class BlogFeed extends Block
{
	private static $table_name = 'ContentBuilderBlogFeed';
	private static $type_title = 'Blog Feed';
	private static $feed_cache_lifetime = '-1 hour';
	
	private static $db = [
		'FeedURL' => 'Varchar(255)',
		'Limit' => 'Int',
		'ColumnCount' => "Int",
	];
	
	private static $defaults = [
		'Limit' => 10,
		'ColumnCount' => 2
	];
	
	private static $column_counts = [
		1,2,3
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', [
			Forms\TextField::Create('FeedURL','Feed URL'),
			Forms\NumericField::Create('Limit','Limit')
				->setDescription('Be sure your blog provides at least this many posts in the RSS feed'),
			Forms\OptionsetField::Create('ColumnCount','Columns')
				->setSource(array_combine($this->Config()->get('column_counts'),$this->Config()->get('column_counts')))
			]);
		return $fields;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if (!preg_match('/feed\/?$/',$this->FeedURL))
		{
			$this->FeedURL = Controller::join_links($this->FeedURL, 'feed/');
		}
	}
	
	public function updateCSSClasses(&$cssClasses)
	{
		if (intval($this->ColumnCount) > 1)
		{
			$cssClasses[] = 'cols-'.intval($this->ColumnCount);
		}
	}

	public function getXmlFeed()
	{
		$body = false;
		if ($FeedURL = $this->FeedURL)
		{
			if (!preg_match('/feed\/?$/',$FeedURL))
			{
				$FeedURL = Controller::join_links($FeedURL, 'feed/');
			}
			$client = new \GuzzleHttp\Client(['verify' => false]);
			try {
				$response = $client->request('GET',$FeedURL);
			} catch (Exception $e) {
				
			}
			$body = $response->getBody()->getContents();
		}
		$this->invokeWithExtensions('updateXmlFeed',$body);
		return $body;
	}
	
	public function cacheXmlFilePath()
	{
		$cachePath = Director::baseFolder().'/blog-feed-'.md5($this->FeedURL).'.xml';
		$this->invokeWithExtensions('updateCacheXmlFilePath',$cachePath);
		return $cachePath;
	}
	
	public function clearXmlCache()
	{
		if (file_exists($this->cacheXmlFilePath()))
		{
			unlink($this->cacheXmlFilePath());
		}
		return $this;
	}
	
	protected function cachedXmlFeed()
	{
		if ($FeedURL = $this->FeedURL)
		{
			$cachePath = $this->cacheXmlFilePath();;
			if ( (!file_exists($cachePath)) || (filemtime($cachePath) < strtotime($this->Config()->get('feed_cache_lifetime'))) )
			{
				$feed = $this->getXmlFeed();
				file_put_contents($cachePath,$feed);
				return $feed;
			}
		}
		$feed = (file_exists($cachePath)) ? file_get_contents($cachePath) : false;
		$this->invokeWithExtensions('updateCachedXmlFeed',$feed);
		return $feed;
	}
	
	protected $_posts;
	public function getPosts()
	{
		if (is_null($this->_posts))
		{
			$BlogFeed = ArrayList::create();
			$feed = $this->cachedXmlFeed();
			$xml = new \SimpleXMLElement($feed);
			$ns = $xml->getDocNamespaces();
			// parse each item
			foreach($xml->channel->item as $item)
			{
				$Content = preg_replace('/<img[^>]+>/','',$item->children($ns['content']));
				$obj = ArrayData::create([
					'Title' => FieldType\DBField::create_field(FieldType\DBVarchar::class,Convert::xml2raw($item->title)),
					'Link' => FieldType\DBField::create_field(FieldType\DBVarchar::class,Convert::xml2raw($item->link)),
					'Datetime' => FieldType\DBField::create_field(FieldType\DBDatetime::class,date('Y-m-d H:i:s',strtotime(Convert::xml2raw($item->pubDate)))),
					'Author' => FieldType\DBField::create_field(FieldType\DBVarchar::class,Convert::xml2raw($item->children($ns['dc']))),
					'Content' => FieldType\DBField::create_field(FieldType\DBHTMLText::class,$Content),
					'Description' => FieldType\DBField::create_field(FieldType\DBHTMLText::class,Convert::xml2raw($item->description)),
					'CommentsCount' => FieldType\DBField::create_field(FieldType\DBInt::class,Convert::xml2raw($item->children($ns['slash']))),
				]);
				$BlogFeed->push($obj);
			}
			$this->invokeWithExtensions('updateBlogFeed', $BlogFeed, $xml);
			$this->_posts = $BlogFeed;
		}
		return $this->_posts;
	}
}










