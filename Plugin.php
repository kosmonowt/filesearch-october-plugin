<?php namespace KosmosKosmos\FileSearch;

use System\Classes\PluginBase;

/**
 * FileSearch Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'FileSearch',
            'description' => 'Suchformular',
            'author'      => 'KosmosKosmos',
            'icon'        => 'icon-zoom'
        ];
    }


    public function registerComponents() {
        return [
                'KosmosKosmos\FileSearch\Components\FileSearch' => "fileSearch"
        ];
    }


}