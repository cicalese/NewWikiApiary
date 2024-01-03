from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki
from scraper import scrape_site
from utils import log_message


def get_wikis(session):
	stmt = select(Wiki).where(Wiki.w8y_wi_is_defunct == False)
	return session.scalars(stmt)


def run():
	with Session(engine) as session:
		for wiki in get_wikis(session):
			url = wiki.w8y_wi_api_url.decode('utf8')
			page_id = wiki.w8y_wi_page_id
			last_scrape_id = wiki.w8y_wi_last_sr_id
			log_message(session, f'Scraping {url}')
			sr_id = scrape_site(url, page_id, session)
			if sr_id:
				wiki.w8y_wi_last_sr_id = sr_id
				session.add(wiki)
				session.commit()


if __name__ == '__main__':
	run()
