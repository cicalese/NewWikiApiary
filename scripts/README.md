# Instructions for running scripts

## Installation
Before running the scripts, you must install the following dependencies:

```
apt install python3-dev python3-pip python3-wheel -y
pip3 install setuptools
pip3 install -r requirements.txt
```

Then, create a `user-password.py` file in the current directory containing
the following, replacing `<username>`, `<botname>`, and `<botpassword>`
as appropriate:

```
('<username>', BotPassword('<botname>', '<botpassword>'))
```

Set the following environment variables:

```
WIKIAPIARY_DB_USERNAME
WIKIAPIARY_DB_PASSWORD
WIKIAPIARY_DB_HOST
WIKIAPIARY_DB_PORT
WIKIAPIARY_DB_SCHEMA
WIKIAPIARY_USERNAME
WIKIAPIARY_URL
```

## Scripts
- `create_pages_from_file` reads the file urls.txt in the current directory; the file has one URL per line; it creates corresponding pages in the wiki
- `sync_pages_to_db` creates Wiki records in the database corresponding to the pages that were created in the wiki
- `scrape` scrapes the data for every row in Wikis and stores the data in the database
- `truncate_log` truncates the log to the last 1000 entries

## See also
- [Pywikibot documentation](https://doc.wikimedia.org/pywikibot/stable/)
- [mwparserfromhell](https://github.com/earwig/mwparserfromhell/)
- [SQLAlchemy](https://docs.sqlalchemy.org)
