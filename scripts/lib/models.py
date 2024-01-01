import os
from sqlalchemy import create_engine
from sqlalchemy.ext.declarative import declarative_base

username = os.environ['WIKIAPIARY_DB_USERNAME']
password = os.environ['WIKIAPIARY_DB_PASSWORD']
host = os.environ['WIKIAPIARY_DB_HOST']
port = os.environ['WIKIAPIARY_DB_PORT']
db_schema = os.environ['WIKIAPIARY_DB_SCHEMA']
engine = create_engine(
	f'mysql+pymysql://{username}:{password}@{host}:{port}/{db_schema}'
)

Base = declarative_base()
Base.metadata.reflect(engine)

class Wiki(Base):
	__table__ = Base.metadata.tables['w8y_wikis']

class ScrapeRecord(Base):
	__table__ = Base.metadata.tables['w8y_scrape_records']

class VersionRecord(Base):
	__table__ = Base.metadata.tables['w8y_version_records']

class Extension(Base):
	__table__ = Base.metadata.tables['w8y_extensions']

class Skin(Base):
	__table__ = Base.metadata.tables['w8y_skins']
