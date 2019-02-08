<?php namespace KosmosKosmos\FileSearch\Classes;

use Cache;
use Cms\Classes\Theme;
use October\Rain\Halcyon\Processors\SectionParser;//Cms\Classes\SectionParser;
use League\Flysystem\Exception;
use Symfony\Component\Finder\Finder as FileFinder;

/**
 * Class Finder
 * This class will perform a file-search based on Symphony's Finder Component
 * @package KosmosKosmos\FileSearch\Classes
 * @author Andreas Kosmowicz, 2015, ak@kosmoskosmos.de
 *
 */
class Finder {

	protected $term = null;
	protected $preparedTerm = null;

	// Sanitize a little bit
	protected $ignore = ["{%","component","title","url","layout","is_hidden"];

	protected $contentFiles = null;
	protected $pageFiles = null;

	protected $searchResult = [];

	/**
	 * @param null $term - optional. The search term.
	 */
	public function __construct($term = null) {

		if (!is_null($term)) $this->setTerm($term);

	}

	protected function addToResultSet($result) {

		foreach ($this->searchResult as $resultItem) {

			if ($resultItem->getUrl() == $result->getUrl()) {
				// Search Result existing. Level up in relevance.
				$resultItem->upRelevance();
				return $resultItem;
			}

		}

		// No result found, create one.
		$this->searchResult[] = $result;
	}

	/**
	 * Perpares the search term for regex
	 *
	 * @param $term
	 * @return string
	 */
	protected function prepareTerm($term) {
		$term = preg_quote($term);
		return "/$term/i";
	}


	/**
	 * Setter method for the search term.
	 * Does some cleansing.
	 * @param $term string: The search term.
	 */
	protected function setTerm($term) {

		$term = str_replace($this->ignore,[],$term);
		$this->preparedTerm = $this->prepareTerm($term);
		$this->term = $term;

	}

	/**
	 * Returns the base for the file path to search the files in.
	 * @return string
	 * @throws \SystemException
	 */
	protected function getPath() {
		return themes_path() . "/" . Theme::getActiveTheme()->getDirName() . "/";
	}

	/**
	 * Returns the finder objects to search the content in.
	 * Already containing the content.
	 *
	 * @param $dir - string: directory to look inside
	 * @param $term - (optional) string: Overrides the preset term.
	 *
	 * @return Symfony\Component\Finder\Finder
	 */
	protected function search($dir,$term = null) {

		$finder = new FileFinder();

		if (is_null($term)) $term = $this->preparedTerm;
		$result = $finder->files()->in($dir)->contains($term);

		// todo: strip HTML Comments: preg_replace('/<!--(.*)-->/Uis', '', $html)
		// todo: strip SCRIPT Tags
		// todo: strip HTML ATTRIBUTES etc

		return $result;

	}


	/**
	 * Function that inspects a file with a potential search result.
	 * Checks if the term is inside the markup section (and not part of metadata.
	 *
	 * @param $contents
	 * @param $term - search term optional
	 *
	 * @return bool - true if term was found in markup-part
	 */
	protected function hasTermInContent($contents,$term) {

		$contentsParsed = SectionParser::parse($contents);

		$contents = $contentsParsed['markup'];

		$r = preg_match($term,$contents,$matches);

		return $r == 1;

	}

	/**
	 * Performs a search in the page folder
	 *
	 * @param string|null $term
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function searchPage($term = null) {

		if (is_null($term)) $term = $this->preparedTerm;

		$notContains = ["is_hidden = 1"];

		$finder = $this->search($this->getPath()."pages",$term);

		foreach ($notContains as $containsNot)  $finder->notContains($containsNot);

		$files = [];

			foreach($finder as $file) {

				if ($file) {
					$searchResult = new SearchResult($this->term,$file);
					$contents = $searchResult->getContents();

					if ($this->hasTermInContent($contents,$term)) {
						$files[] = $searchResult;
					}
				}

			}

		return $files;
	}

	/**
	 *
	 * Look for Content in Content Files
	 *
	 * @return Finder
	 */
	protected function searchContent() {

//		echo "search content <br>";

		// Try locating the term in the contents.
		$files = $this->search($this->getPath()."content");

		$result = [];

		// We retrieved all content files holding the search term. Now we need to look if they are linked in page files
		foreach ($files as $file) {
			// Get Filename to search for in page content's component.
			$filename = $file->getFilename();
			// In Case we have multilanguage support and a language suffix in the file
			// We only need the filename without the suffix

			$name = substr($filename,0,strrpos($filename,"."));
			if ($pPos = strpos($name,".")) {
				$name = substr($name,0,$pPos);
				$suffix = substr($filename,strrpos($filename,"."));
				$filename = $name.$suffix;
				// I.E. "welcome.de.html" => "welcome.html"
			}

			$pagesWithTerm = $this->searchPage($this->prepareTerm($filename));


			foreach ($pagesWithTerm as $i => $pageWithTerm) {

				// filter results for component "editable" or content file=""
				$includes = [
				'/{%\s?component\s?["|\']editable["|\']\s?file=["|\']([^"\']+)["|\']\s?%}/is',
				'/{%\s?content\s?["|\']contacts\.htm["\']\s?%}/is'];
				$contentRewritten = preg_replace($includes,$file->getContents(),$pageWithTerm->getContents());
				$contentRewritten = PageHelper::stripTwigTags($contentRewritten);

				$pageWithTerm->setContents($contentRewritten);

				$result[] = $pageWithTerm;

			}


		}

		return $result;

	}

	/**
	 * Needed for serializing the cached resultset
	 * @return string
	 */
	protected function toJson() {
		$set = [];
		foreach($this->searchResult as $result) {
			$set[] = $result->toJson();
		}
		return json_encode($set);
	}

	/**
	 * This function should be used in component to retrieve an array of search results
	 * @return array - SearchResult Objects
	 */
	public function getResult() {
		return $this->searchResult;
	}

	/**
	 * Triggers the search function.
	 * Can use cache if nescessary
	 * @param null $term
	 */
	public function find($term = null) {

		if (!is_null($term)) $this->setTerm($term);

		if (Cache::has("search_".$this->term)) {
			// Use cache if possible

			$cachedResult = json_decode(Cache::get("search_".$this->term));

			foreach($cachedResult as $result) {
				//dd($result);
				$this->searchResult[] = new SearchResult($term,json_decode($result));
			}

		} else {
			// Does a new file query

			$files = $this->searchContent();
			foreach($files as $file) $this->addToResultSet($file);


			$pageFiles = $this->searchPage();
			foreach ($pageFiles as $file) $this->addToResultSet($file);

			Cache::put("search_".$this->term,$this->toJson(),720);

		}

	}

}
