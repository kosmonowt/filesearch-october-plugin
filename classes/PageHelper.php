<?php namespace KosmosKosmos\FileSearch\Classes;

/**
 * Class PageHelper
 *
 * Some helper functions. Quick and dirty for getting faster to the results.
 * If someone will modify this to CORE function facade... even better.
 * DRY ;-)
 *
 * @package KosmosKosmos\FileSearch\Classes
 * @author ak@kosmoskosmos.de 2015
 */
class PageHelper {

	/**
	 * Returns the url of a page file.
	 * @param $contents
	 *
	 * @return bool
	 */
	public static function getUrl($contents) {

		$urlPattern = '/url\s?=\s?"([^"]+)"/i';

		$r = preg_match($urlPattern,$contents,$matches);

		if ($r) {
			return $matches[1];
		}

		return false;


	}

	/**
	 * Returns the url of a page file.
	 * @param $contents
	 *
	 * @return bool
	 */
	public static function getTitle($contents) {

		$urlPattern = '/title\s?=\s?"([^"]+)"/i';

		$r = preg_match($urlPattern,$contents,$matches);

		if ($r) {
			return $matches[1];
		}

		return false;

	}

	/**
	 * Strips all twig-tags {% ... %} from the string
	 *
	 * @param $content - content string
	 *
	 * @return mixed
	 */
	public static function stripTwigTags($content) {

		$replace = ['#{%\s(?!(component "editable"|content)).*%}#si'];
		$content = preg_replace($replace,"",$content);
		$replace = ['#({{)(.+?)(}})#si'];
		$content = preg_replace($replace,"",$content);
		return $content;

	}

}