<?php namespace KosmosKosmos\FileSearch\Classes;

use Cms\Classes\Page;
use Cms\Classes\SectionParser;
use League\Flysystem\Exception;
use October\Rain\Support\Facades\Twig;
use KosmosKosmos\FileSearch\Classes\PageHelper;

/**
 *
 * Class SearchResult
 * Meant to hold a search result.
 * Holds all information to a search result.
 *
 * Take
 * getUrl() to retrieve the URL of the search result
 * getExcerpt() to retrieve the Excerpt of the search result. Use $length to determine the length of the result to throw out
 * getContents() to retrieve the raw content of the search result.
 *
 * @package KosmosKosmos\FileSearch\Classes
 * @author Andreas Kosmowicz, 2015, ak@kosmoskosmos.de
 */
class SearchResult {

	protected $file = null;
	protected $term = null;
	protected $excerpt = null;
	protected $contents = null;
	protected $url = null;
	protected $title = null;
	protected $relevance = 1;

	public function __construct($term = null, $file = null) {
		if (!is_null($term)) $this->setTerm($term);
		if (!is_null($file)) {
			if (get_class($file) == "stdClass") {
				$this->url = $file->url;
				$this->excerpt = $file->excerpt;
				$this->title = $file->title;
				$this->relevance = $file->relevance;
				$this->contents = "";
			} else {
				$this->setFile($file);
			}
		}
	}

	/**
	 * Setter of the SplFile Object that can be found in this object
	 * @param $file
	 */
	public function setFile($file) {
		$this->file = $file;
	}

	/**
	 * Setter for the search term that can be found in this object
	 * @param $term
	 */
	public function setTerm($term) {
		$this->term = $term;
	}

	/**
	 * Increases Relevance of search result.
	 * @param int|null      $relevance
	 * @param bool|true $add - if true, relevance is increased with $relevance. Otherwise it is replaced with it
	 */
	public function setRelevance($relevance = null,$add = true) {

		if (is_null($relevance)) $this->relevance++;
		elseif ($add) $this->relevance += $relevance;
		else $this->relevance = $relevance;
	}

	/**
	 * Returns the contents of a search result
	 * @return null|string
	 * @throws Exception
	 */
	public function getContents() {

		if (is_null($this->contents)) {

			if (is_null($this->file)) throw new Exception("No file in Search result. Please initialize with a file!");

			$this->contents = $this->file->getContents();

		}

		return $this->contents;
	}

	/**
	 * Overwrites the contents.
	 * @param $contents
	 */
	public function setContents($contents) {
		$this->contents = $contents;
	}

	/**
	 * Returns the instance of the SplFile Object
	 * @return Object
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Returns the search term.
	 * @return null
	 */
	public function getTerm() {
		return $this->term;
	}

	/**
	 * Returns the url that holds this search result.
	 * @return string
	 */
	public function getUrl() {

		if (is_null($this->url)) {

			$this->url = PageHelper::getUrl($this->getContents());

		}

		return $this->url;
	}

	/**
	 * Returns the number of hits of that keyword in the page
	 * @return int
	 */
	public function getRelevance() {
		return $this->relevance;
	}

	/**
	 * Increases the number of hits of that keyword in the page
	 * @return int
	 */
	public function upRelevance() {
		$this->relevance++;
		return $this->relevance;
	}

	/**
	 * Gives out an excerpt of the search term in the file.
	 *
	 * @param null $length - not working yet: Lenght of excerpt, with the term in the middle
	 *
	 * @return string - the excerpt
	 * @throws Exception
	 */
	public function getExcerpt($length = 100) {
		if (is_null($this->excerpt)) {

			$contentsParsed = SectionParser::parse($this->getContents());
			$contents = $contentsParsed['markup'];
			$this->excerpt = strip_tags($contents);

		}

		$preExcerpt = "";

		$startPos = stripos($this->excerpt, $this->term) - ($length/2);
		if ($startPos<=0) {
			$startPos = 0;
		} else {
			$preExcerpt = "...";
		}

		if(strlen($this->excerpt) > $length) {
			$excerpt   = substr($this->excerpt, $startPos, $length-3);
			$lastSpace = strrpos($excerpt, ' ');
			$excerpt   = substr($excerpt, 0, $lastSpace);
			$excerpt  .= '...';
			$excerpt   = $preExcerpt.$excerpt;
		} else {
			$excerpt = $this->excerpt;
		}

		$excerpt = trim(str_ireplace($this->term,"<strong>".$this->term."</strong>",$excerpt));

		if (!strlen($excerpt)) {
			$excerpt = "...<strong>".$this->term."</strong>...";
		}

		return $excerpt;
	}

	/**
	 * Returns the title of the result page as found in code section of page
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getTitle() {
		if (is_null($this->title)) {
			$this->title = PageHelper::getTitle($this->getContents());
		}

		return $this->title;
	}

	/**
	 * Serialization for Caching results
	 *
	 * @return string
	 */
	public function toJson() {
		$set = [];
		$set["title"] = $this->getTitle();
		$set['excerpt'] = $this->getExcerpt();
		$set['url'] = $this->getUrl();
		$set['relevance'] = $this->getRelevance();
		$set['contents'] = $this->getContents();

		return json_encode($set);

	}

}