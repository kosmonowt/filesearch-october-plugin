# FILE SEARCH PLUGIN

This plugin intends to perform a file search in the *contents* and *pages* of the plugin.
It can parse the *editable* plugin and the *{% component %}*-tags to locate component files.
It relies on symfony's FileSearch-API and uses simple caching as result cache.

Since I believe that this plugin might be good for many sites running on OctoberCMS I'd really appreciate helping hands in developing the plugin a little more.

Especially what I'd love to have is some help in parsing the TWIG and PARTIAL-tags (although we won't need the partial's information probably).

## Classes

Technically (since I consider this piece of software in late Alpha-state because of still poor twig-parsing)
it consists of the following parts:

- Finder
- PageHelper
- SearchResult

The *FileSearch component* calls an instance of *Finder* to retrieve a search result.

*Finder* searches the content- and page-directories to find pages.
Each valid result in this resultset will be stored in a *SearchResult*-instance.
*PageHelper* just gives access to some commonly used functions.

## Most important Todos:

- Enhanced twig-parsing
- Localisation
- Excluding TAGS in search (i.E. search for *'alt'* might deliver img **alt='bla'**)

## LICENSE:

MIT License

## Icon:
http://buatoom.com

## Any help and pull requests appreciated!

2015, Andreas Kosmowicz
andreas@kosmoskosmos.de
