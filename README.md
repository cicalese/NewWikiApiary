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
The parser function **w8y** has several actions.

#### action=query

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

#### action=wiki

It takes 1 argument : **pageId**

Example :
```wikitext
{{#w8y:action=wiki
|pageId=9}}
```
The example above will get all information about wiki with pageId 9 and render every information as a table

#### action=stats

It takes 3 arguments : 

**for** ( either extensions, skins, mwversion )

**limit** ( return the top <limit>, default to 10 )

**where** ( full where clause )

Example :
```wikitext
{{#w8y:action=stats
|for=extensions
|limit-10
|where=w8y_wi_api_url not like "%fandom.com/%" }}
```
The example above will get the top 10 most used extensions, not being a fandom website, based on installments.

#### action=extension

This return

It takes two arguments : 

**Extension name** tba

**type** tba

## Lua
Call are the same as for the parser functions.

Create a Module:WikiApiary
```lua
local p = {}

function p.w8y(frame)
  w8y = mw.w8y.w8y( frame.args )
  return w8y
end

return p
```

You can then call the module as follows :

```wikitext
{{#invoke:WikiApiary|w8y|action=wiki|id=9}}
```

```wikitext
{{#invoke:WikiApiary|w8y|action=stats|for=extensions|limit=10}}
```

```wikitext
{{#invoke:WikiApiary|w8y|action=stats|for=skins|limit=15}}
```

The result will be a Lua table 
