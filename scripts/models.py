import os
from sqlalchemy import create_engine
from sqlalchemy.ext.declarative import declarative_base

username = os.environ.get('WIKIAPIARY_DB_USERNAME')
password = os.environ.get('WIKIAPIARY_DB_PASSWORD')
host = os.environ.get('WIKIAPIARY_DB_HOST')
port = os.environ.get('WIKIAPIARY_DB_PORT', default='3306')
db_schema = os.environ.get('WIKIAPIARY_DB_SCHEMA')
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

class MwStatistics(Base):
	__table__ = Base.metadata.tables['w8y_mw_statistics']

class SmwStatistics(Base):
	__table__ = Base.metadata.tables['w8y_smw_statistics']
