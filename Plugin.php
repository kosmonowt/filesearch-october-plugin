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
            'description' => 'kosmoskosmos.filesearch::lang.plugin_description',
            'author'      => 'KosmosKosmos',
            'icon'        => 'icon-search'
        ];
    }


    public function registerComponents() {
        return [
                'KosmosKosmos\FileSearch\Components\FileSearch' => "fileSearch"
        ];
    }


}
