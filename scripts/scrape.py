from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki
from scraper import Scraper

def get_wikis():
	stmt = select(Wiki)
	return session.scalars(stmt)

def scrape_site(scraper, wiki, session):
	url = wiki.w8y_wi_api_url.decode('utf8')
	print(url)
	scrape = scraper.scrape_site(url, wiki.w8y_wi_page_id)
	if scrape:
		session.add(scrape)
		session.commit()

with Session(engine) as session:
	scraper = Scraper()
	for wiki in get_wikis():
		scrape_site(scraper, wiki, session)
