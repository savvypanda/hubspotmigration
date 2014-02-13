<?php defined('_JEXEC') or die('Restricted Access');

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');

class HubspotmigrationModelBlogs extends FOFModel {
	private static $authors = array();
	private static $textmaps;
	private $auto_create_authors = false;
	private $use_html_aliases = false;
	private $hb_k2_image_size = '';
	private $hb_path_for_k2_images = '/images';

	public function __construct($config = array()) {
		parent::__construct($config);

		$params = JComponentHelper::getParams('com_hubspotmigration');
		$this->auto_create_authors = $params->get('auto_create_authors',false);
		$this->use_html_aliases = $params->get('use_html_aliases',false);
		$this->hb_path_for_k2_images = $params->get('hb_path_for_k2_images','/images/blog/old-featured-images');
		$this->hb_k2_image_size = $params->get('hb_k2_image_size','src');
	}

	public function buildQuery($overridelimits = false) {
		$query = parent::buildQuery($overridelimits);
		$urlmapped = $this->getState('urlmapped','');
		if($urlmapped) {
			$query->where('hb_blog_link IS NOT NULL');
			$query->where('hb_blog_link<>""');
			switch($urlmapped) {
				case 'same':
					$query->where('k2_link=hb_blog_link');
					break;
				case 'mapped':
					$query->where('link_mapped=1');
					break;
				case 'unmapped':
					$query->where('link_mapped=0');
					$query->where('k2_link<>hb_blog_link');
					break;
			}
		}
		return $query;
	}

	public function onBeforeSave(&$data, &$table) {
		if(!is_array($data)) $data = (array) $data;
		if($data['k2_item_id']) {
			if(!$data['title']) {
				$query = 'SELECT title FROM #__k2_items WHERE id='.$this->_db->quote($data['k2_item_id']);
				$this->_db->setQuery($query);
				$data['title'] = $this->_db->loadResult();
			}

			$data['k2_link'] = $this->getK2ItemRoute($data['k2_item_id']);
			if($data['k2_link'] <> $table->k2_link) {
				$data['link_mapped'] = 0;
			}
		}

		if($table->hubspotmigration_blog_id && $data['edited'] && ($table->k2_item_id <> $data['k2_item_id'] || $table->hb_blog_id <> $data['hb_blog_id'] || $table->blog_group_id <> $data['blog_group_id'])) {
			$data['status'] = 'new';
		}

		return parent::onBeforeSave($data, $table);
	}

	private function getTextMaps() {
		if(!is_array(self::$textmaps)) {
			$query = 'SELECT regex_from, regex_to, is_regex FROM #__hubspotmigration_textmappings WHERE enabled=1 ORDER BY ordering ASC';
			$this->_db->setQuery($query);
			self::$textmaps = $this->_db->loadObjectList();
		}
		return self::$textmaps;
	}

	private function getAuthor($id) {
		if(empty(self::$authors)) {
			$query = 'SELECT juser_id, hb_author_id FROM #__hubspotmigration_authors';
			$this->_db->setQuery($query);
			$dbauthors = $this->_db->loadObjectList();
			foreach($dbauthors as $author) {
				self::$authors[$author->juser_id] = $author->hb_author_id;
			}
		}
		if(!array_key_exists($id, self::$authors)) {
			$juser = JFactory::getUser($id);
			$query = 'SELECT hubspotmigration_author_id, hb_author_id FROM #__hubspotmigration_authors WHERE juser_id IS NULL AND hb_author_name='.$this->_db->quote($juser->name);
			$this->_db->setQuery($query);
			$this->_db->query();
			if($this->_db->getNumRows() > 0) {
				$authordata = $this->_db->loadObject();
				$data = array(
					'hubspotmigration_author_id' => $authordata->hubspotmigration_author_id,
					'juser_id' => $id,
					'juser_name' => $juser->name,
				);
				FOFModel::getTmpInstance('authors','hubspotmigrationModel')->save($data);
				self::$authors[$id] = $authordata->hb_author_id;
			} elseif($this->auto_create_authors) {
				$authordata = array(
					'email' => $juser->email,
					'full_name' => $juser->name,
				);
				$response = HubspotmigrationHelper::postRequest('/content/api/v2/blog-authors',$authordata);
				$http_status = HubspotmigrationHelper::getResponseCode();

				if($http_status == 201) {
					$hbauthor = json_decode($response);
					$hb_author_id = $hbauthor->id;
					$newdata = array(
						'juser_id' => $id,
						'juser_name' => $juser->name,
						'hb_author_id' => $hb_author_id,
						'hb_author_name' => $juser->name,
						'auto_created' => 1,
					);
					FOFModel::getTmpInstance('authors','hubspotmigrationModel')->save($newdata);
					self::$authors[$id] = $hb_author_id;
				} else {
					self::$authors[$id] = false;
				}
			} else {
				self::$authors[$id] = false;
			}
		}
		return self::$authors[$id];
	}

	private function getHubspotFilepath($uri, $destination = '') {
		if(strpos($uri,'http')===0) return $uri;
		$url = '/'.ltrim($uri,'/');
		if(!file_exists(JPATH_SITE.$url)) return $uri;

		$query = 'SELECT hb_file_path FROM #__hubspotmigration_files WHERE jpath='.$this->_db->quote($url);
		$this->_db->setQuery($query);
		$hb_file_path = $this->_db->loadResult();
		if($hb_file_path) return $hb_file_path;

		$filedata = array(
			'folder_paths' => (($destination)?$destination:substr($url,0,strrpos($url, '/'))), //$this->getHubspotFolder($url),
			//'overwrite' => 'false',
			'overwrite' => 'true',
			'files' => '@'.realpath(JPATH_SITE.$url)
		);
		$response = HubspotmigrationHelper::postRequestWithFile('/content/api/v2/files',$filedata);
		$http_code = HubspotmigrationHelper::getResponseCode();

		if($http_code != 201) return false;

		$hubspotfile = json_decode($response)->objects[0];

if(!$hubspotfile->alt_url) return false; //die('<pre>Post file successfully but failed to fetch URL. Response Object: '.var_export($hubspotfile,true).'</pre>');

		$data = array(
			'jpath' => $url,
			'hb_file_id' => $hubspotfile->id,
			'hb_file_path' => $hubspotfile->alt_url,
		);
		FOFModel::getTmpInstance('files','hubspotmigrationModel')->save($data);

		return $hubspotfile->alt_url;
	}

	private function recordError($blog_id, $details, $api_response='') {
		$data = array(
			'hubspotmigration_blog_id' => $blog_id,
			'status' => 'errored',
			'details' => $details
		);
		if($api_response) {
			$data['api_response'] = $api_response;
		}
		$this->save($data);
	}

	/* public function testRepost() {
		$blogdata = array(
			//"is_draft"=>false,
			//"content_group_id"=>"418571918",
			//"blog_author_id"=>"423005905",
			//"name"=>"How to Become Transparent",
			//"slug"=>"how-to-become-transparent",
			"meta_description"=>"Modifying the meta description.",
			//"meta_keywords"=>"",
			//"publish_date"=>'1348515669000',
			//"post_summary"=>'With the age of social media and smart phones, the world is experiencing a new level of transparency that has never been before seen. You can know what the CEO of Pepsi had for breakfast and what TV shows he likes.',
			//"post_body"=>"<div><img src='http://cdn2.hubspot.net/hub/155099/images/blog/old-featured-images/63ae8dd535459e6ddaa9950601158f8d.jpg' alt='How to Become Transparent and Why it Matters' /></div>",
		);

		$result = HubspotmigrationHelper::putRequest('/content/api/v2/blog-posts/420578801', $blogdata);
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 200) {
			return false;
		}

		$entry = json_decode($result);
		$result = HubspotmigrationHelper::postRequest('/content/api/v2/blog-posts/'.$entry->id.'/publish-action',array('action'=>'schedule-publish'));
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 200) {
			return false;
		}

		return true;

	} */

	public function postBlog($hubspotmigration_blog_id) {
		$blogdata = $this->_prepareBlogPost($hubspotmigration_blog_id);
		if($blogdata === false) return true;
		if(!is_array($blogdata)) return false;

		$result = HubspotmigrationHelper::postRequest('/content/api/v2/blog-posts', $blogdata);
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 201) {
			$this->recordError($hubspotmigration_blog_id, 'Post creation failed with response status: '.$http_status, $result);
			return false;
		}

		$entry = json_decode($result);
		$result = HubspotmigrationHelper::postRequest('/content/api/v2/blog-posts/'.$entry->id.'/publish-action',array('action'=>'schedule-publish'));
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 200) {
			$this->recordError($hubspotmigration_blog_id, 'Failed to publish article. Response status: '.$http_status, $result);
			return false;
		}

		$data = array(
			'hubspotmigration_blog_id' => $hubspotmigration_blog_id,
			'status' => 'created',
			'details' => '',
			'hb_blog_id' => $entry->id,
			'hb_blog_link' => ($entry->slug)?'/'.$entry->slug:'',
			'api_response' => $result,
		);
		$this->save($data);
		return true;
	}

	public function repostBlog($hubspotmigration_blog_id) {
		$query = 'SELECT hb_blog_id FROM #__hubspotmigration_blogs WHERE hubspotmigration_blog_id='.$this->_db->quote($hubspotmigration_blog_id);
		$this->_db->setQuery($query);
		$hb_blog = $this->_db->loadObject();
		if(!$hb_blog) return false;
		if(!$hb_blog->hb_blog_id) return $this->postBlog($hubspotmigration_blog_id);

		$blogdata = $this->_prepareBlogPost($hubspotmigration_blog_id);
		if($blogdata === false) return true;
		if(!is_array($blogdata)) return false;

		$result = HubspotmigrationHelper::putRequest('/content/api/v2/blog-posts/'.$hb_blog->hb_blog_id, $blogdata);
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 200) {
			$this->recordError($hubspotmigration_blog_id, 'Failed to update post. Response status: '.$http_status, $result);
			return false;
		}

		$entry = json_decode($result);
		if($entry->processing_status != 'published') {
			$result = HubspotmigrationHelper::postRequest('/content/api/v2/blog-posts/'.$entry->id.'/publish-action',array('action'=>'schedule-publish'));
			$http_status = HubspotmigrationHelper::getResponseCode();

			if($http_status != 200) {
				$this->recordError($hubspotmigration_blog_id, 'Failed to publish post. Response status: '.$http_status, $result);
				return false;
			}
		}

		$data = array(
			'hubspotmigration_blog_id' => $hubspotmigration_blog_id,
			'status' => 'created',
			'details' => '',
			'hb_blog_id' => $entry->id,
			'hb_blog_link' => ($entry->slug)?'/'.$entry->slug:'',
			'api_response' => $result,
		);
		$this->save($data);
		return true;
	}

	private function _prepareBlogPost($hubspotmigration_blog_id) {
		$hb_blog = FOFModel::getTmpInstance('blogs','HubspotmigrationModel')->getItem($hubspotmigration_blog_id);
		$request_data = array('is_draft'=>'false', 'content_group_id'=>$hb_blog->blog_group_id); 

		$query = 'SELECT title, alias, introtext, `fulltext`, created_by, publish_up, metadesc, metakey FROM #__k2_items WHERE id='.$hb_blog->k2_item_id;
		$this->_db->setQuery($query);
		$item = $this->_db->loadObject();
		if(empty($item)) return false;

		$request_data['blog_author_id'] = $this->getAuthor($item->created_by);
		if(!$request_data['blog_author_id']) {
			$this->recordError($hubspotmigration_blog_id, 'Failed to locate or create Hubspot author');
			return false;
		}
		$request_data['name'] = $item->title;
		$request_data['slug'] = $this->getK2ItemRoute($hb_blog->k2_item_id); // $item->alias.(($this->use_html_aliases)?'.html':'');
		$request_data['meta_description'] = $item->metadesc;
		$request_data['meta_keywords'] = $item->metakey;
		$request_data['publish_date'] = strtotime($item->publish_up).'000';

		$introtext = $item->introtext;
		$postcontent = $item->fulltext;
		if(empty($postcontent)) {
			$postcontent = $introtext;
			$introtext = '';
		}
		//$request_data['post_summary'] = $introtext;

		//if(strpos($postcontent, '<![CDATA[')) {
		//	$this->recordError($hubspotmigration_blog_id, 'Blog post contains CDATA escaped characters. This post must be migrated manually.');
		//	return false;
		//}

		$maps = $this->getTextMaps();
		foreach($maps as $map) {
			if($map->is_regex) {
				$postcontent = preg_replace($map->regex_from, $map->regex_to, $postcontent);
			} else {
				$postcontent = str_replace($map->regex_from, $map->regex_to, $postcontent);
			}
		}

		$dom = new DOMDocument;
		$dom->loadHTML($postcontent);

		if(empty($request_data['meta_description'])) {
			if(!empty($introtext)) {
				$introdom = new DOMDocument;
				$introdom->loadHTML($introtext);
			} else {
				$introdom =& $dom;
			}
			$textpath = new DOMXPath($introdom);
			$textnodes = $textpath->query('//*[not(self::script or self::style)]/text()[normalize-space()]');
			$textintros = array();
			foreach($textnodes as $node) {
				$textintros[] = $node->nodeValue;
			}
			$textintro = implode(' ', $textintros);
			if(empty($textintro)) {
				$this->recordError($hubspotmigration_blog_id, 'Could not locate or create a metadescription');
				return false;
			}
			if(strlen($textintro) > 255) {
				$textintro = substr($textintro,0,239).'... (read more)';
			}
			$request_data['meta_description'] = $textintro;
		}

		$images = $dom->getElementsByTagName('img');
		$files = array();
		foreach($images as $img) {
			$files[] = $img->getAttribute('src');
		}
		$anchors = $dom->getElementsByTagName('a');
		foreach($anchors as $a) {
			$files[] = $a->getAttribute('href');
		}
		$files = array_unique($files);
		$filesfrom = array();
		$filesto = array();
		$allowedfiles = array('bmp','css','csv','doc','docx','gif','ico','jpg','jpeg','js','log','pdf','png','psd','tif','txt','xls','xlsx','xml','zip');
		foreach($files as $file) {
			if(strpos($file,'http') !== 0 && in_array(strtolower(substr(strrchr($file,'.'),1)),$allowedfiles)) {
				$filereplacement = $this->getHubspotFilepath($file);
				if(!$filereplacement) {
					$this->recordError($hubspotmigration_blog_id,'Error uploading file '.$file.' to Hubspot.');
					return false;
				}
				if($file != $filereplacement) {
					$filesfrom[] = $file;
					$filesto[] = $filereplacement;
				}
			}
		}
		if(!empty($filesfrom)) {
			$postcontent = str_replace($filesfrom, $filesto, $postcontent);
		}


		$featuredimage = '';
		switch($this->hb_k2_image_size) {
			case 'src':
				$featuredimage = DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'k2'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.md5("Image".$hb_blog->k2_item_id).'.jpg';
				break;
			case 'XL':
			case 'L':
			case 'M':
			case 'S':
			case 'XS':
				$featuredimage = DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'k2'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.md5("Image".$hb_blog->k2_item_id).'_'.$this->hb_k2_image_size.'.jpg';
				break;
		}
		if($featuredimage && file_exists(JPATH_SITE.$featuredimage)) {
			$newfile = $this->getHubspotFilepath($featuredimage, $this->hb_path_for_k2_images);
			if(!$newfile) {
				$this->recordError($hubspotmigration_blog_id,'Error uploading featured image to Hubspot.');
				return false;
			}
			$postcontent = '<div class="blogimg"><img src="'.$newfile.'" alt="'.addslashes($item->title).'" /></div>'.$postcontent;
		}

		$request_data['post_body'] = ($introtext?$introtext."\n<!--more-->\n":'').$postcontent;

		return $request_data;
	}

	public function urlMap($hubspotmigration_blog_id) {
		$hb_blog = FOFModel::getTmpInstance('blogs','HubspotmigrationModel')->getItem($hubspotmigration_blog_id);
		if($hb_blog->link_mapped || $hb_blog->k2_link == $hb_blog->hb_blog_link) return true;

		$request_data = array(
			'route_prefix' => $hb_blog->k2_link,
			'destination' => $hb_blog->hb_blog_link,
		);
		$result = HubspotmigrationHelper::postRequest('/content/api/v2/url-mappings', $request_data);
		$http_status = HubspotmigrationHelper::getResponseCode();

		if($http_status != 201) {
			$this->recordError($hubspotmigration_blog_id, 'Failed creating the new URL mapping',$result);
			return false;
		}

		$data = array(
			'hubspotmigration_blog_id' => $hubspotmigration_blog_id,
			'api_response' => $result,
			'link_mapped' => 1
		);
		FOFModel::getTmpInstance('blogs','HubspotmigrationModel')->save($data);
		return true;
	}

	private function getK2ItemRoute($k2_item_id) {
		return $this->getSiteRoute('index.php?option=com_k2&view=item&layout=item&id='.$k2_item_id);
	}

	private function getSiteRoute($url) {
		if(defined('SH404SEF_IS_RUNNING')) {
			include_once(JPATH_SITE.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'application.php');
			return Sh404sefHelperGeneral::getSefFromNonSef($url, false);
		}

		$router = JApplication::getInstance('site')->getRouter();
		return str_replace('/administrator/','/',$router->build($url)->toString());
	}
}
