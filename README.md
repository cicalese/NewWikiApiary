## Installation

Grab in instance from https://github.com/cicalese/NewWikiApiary and create a "WikiApiary" folder in your Wiki extensions
folder and extract the files there.

Run the [update script](https://www.mediawiki.org/wiki/Manual:Update.php) which will automatically create the necessary database tables that this extension needs.

Navigate to Special:Version on your wiki to verify that the extension is successfully installed.

## Scripts
Please have a look at the [README.md](scripts/README.md) in the scripts folder.

## Parser function

### Config
The Parser function has 1 config setting:
```php
$wgWikiApiary['debug'] = false;  // False by default
```
When set to true all steps will be var_dump'd on top of the page for sysops only.

### Usage
The parser function **w8y** has several action of which currently only is active: **action=query**.

It takes 5 arguments : return, from, where, limit and format ( currently only csv ).

if limit is omitted, limit = 10.

if format is omitted, format = csv ( every row result is separated by a ','', every column result by a ';')

Example :
```wikitext
{{#w8y:action=query
|return=w8y_wi_api_url, w8y_wi_last_sr_id
|from=w8y_wikis
|where=w8y_wi_page_id=1,w8y_wi_is_defunct=1}}
```
The example above _gets_ **w8y_wi_api_url** and **w8y_wi_last_sr_id** _from_ table **w8y_wikis** _where_ **w8y_wi_page_id=1** _and_ **w8y_wi_is_defunct=1** with limit=10 and format=csv