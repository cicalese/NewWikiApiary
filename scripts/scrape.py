from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki
from scraper import scrape_site

def get_wikis():
	stmt = select(Wiki).where(Wiki.w8y_wi_is_defunct == False)
	return session.scalars(stmt)

with Session(engine) as session:
	for wiki in get_wikis():
		url = wiki.w8y_wi_api_url.decode('utf8')
		page_id = wiki.w8y_wi_page_id
		last_scrape_id = wiki.w8y_wi_last_sr_id
		print(url)
		sr_id = scrape_site(url, page_id, session)
		if sr_id:
			wiki.w8y_wi_last_sr_id = sr_id
			session.add(wiki)
			session.commit()
