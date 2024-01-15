import os
from sqlalchemy import engine as sa_engine
from sqlalchemy import create_engine
from sqlalchemy.ext.declarative import declarative_base

url = sa_engine.URL.create(
	drivername = "mysql+pymysql",
	username = os.environ['WIKIAPIARY_DB_USERNAME'],
	password = os.environ['WIKIAPIARY_DB_PASSWORD'],
	host = os.environ['WIKIAPIARY_DB_HOST'],
	port = os.environ['WIKIAPIARY_DB_PORT'],
	database = os.environ['WIKIAPIARY_DB_SCHEMA']
)
engine = create_engine(url)

Base = declarative_base()
Base.metadata.reflect(engine)


class Wiki(Base):
	__table__ = Base.metadata.tables['w8y_wikis']


class ScrapeRecord(Base):
	__table__ = Base.metadata.tables['w8y_scrape_records']


class Skin(Base):
	__table__ = Base.metadata.tables['w8y_skins']


class Extension(Base):
	__table__ = Base.metadata.tables['w8y_extensions']


class Log(Base):
	__table__ = Base.metadata.tables['w8y_log']
