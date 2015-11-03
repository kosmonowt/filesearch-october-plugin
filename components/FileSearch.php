<?php namespace Kosmoskosmos\FileSearch\Components;

use Input;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use KosmosKosmos\FileSearch\Classes\Finder;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class FileSearch extends ComponentBase
{

	protected $result = null;

    public function componentDetails()
    {
        return [
            'name'        => 'FileSearch Component',
            'description' => 'kosmoskosmos.filesearch::lang.component.description',
        ];
    }

    public function defineProperties()
    {
	    return [
			    "page" => [
					    'title'         => 'kosmoskosmos.filesearch::lang.component.page.title',
					    'description'   => 'kosmoskosmos.filesearch::lang.component.page.description',
				        'default'       => "/search.html",
					    'type'          => 'string'
			    ],
		        "name" => [
			        "title"             => 'kosmoskosmos.filesearch::lang.component.name.title',
			        "description"       => 'kosmoskosmos.filesearch::lang.component.name.description',
			        "default"           => "q",
			        "type"              => "string"
		        ]
	    ];
    }

	public function getGETName() {
		return $this->properties["name"];
	}

	public function search() {
		$q =  $this->getGETName();
		return Input::get($q,false);
	}

	public function searchResult() {
		return $this->result;
	}

	public function hasResult() {
		return (bool) count($this->result);
	}

    public function onRun() {

	    $q =  $this->getGETName();
	    $url = $this->properties['page'];
	    $url = trim($url,"\t\r\n\0\x0B/");

	    if (Request::path() == $url) {

		    if (Input::get($q,false)) {

			    // Found a search query.
			    $Finder = new Finder(Input::get($q));
			    $Finder->find();
			    $this->result = $Finder->getResult();

		    }

	    } elseif (Input::get($q,false)) {

		    $uri = Request::path();

		    if ($url != $uri) {

			    return Redirect::to("$url?$q=".Input::get($q,false));

		    }

	    }

    }

}