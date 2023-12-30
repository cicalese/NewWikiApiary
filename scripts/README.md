# Instructions for running scripts

## Installation
Before running the scripts, you must install the following dependencies:

```
apt install python3-dev python3-pip python3-wheel -y
pip3 install sqlalchemy
pip3 install pymysql
pip3 install setuptools
pip3 install requests
pip3 install mwparserfromhell
pip3 install pywikibot
```

Then, create a `user-config.py` file in the current directory containing
the following, replacing `<lang>`, `<family>`, `<username>`, and `<url>`
as appropriate:

```
put_throttle = 0
mylang = '<lang>'
family = '<family>'
usernames['<lang>']['<family>'] = '<username>'
family_files['<family>'] = '<url>'
```

Set the following environment variables:

```
WIKIAPIARY_DB_USERNAME
WIKIAPIARY_DB_PASSWORD
WIKIAPIARY_DB_HOST
WIKIAPIARY_DB_PORT
WIKIAPIARY_DB_SCHEMA
```

## See also
- [Pywikibot documentation](https://doc.wikimedia.org/pywikibot/stable/)
- [mwparserfromhell](https://github.com/earwig/mwparserfromhell/)
- [SQLAlchemy](https://docs.sqlalchemy.org)
