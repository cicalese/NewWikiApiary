from pywikibot import Site, Category
from pywikibot.pagegenerators import CategorizedPageGenerator
import mwparserfromhell
from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki
from utils import log_message


def get_wikis():
	site = Site()
	category = Category(site, 'Wiki')
	return CategorizedPageGenerator(category, content=True)


def get_url_from_page(wikipage):
	templates = mwparserfromhell.parse(wikipage.text).filter_templates()
	if len(templates) > 0:
		template = templates[0]
		if template.name.matches('Wiki') and template.has('url'):
			return str(template.get('url').value)
	return None


def run():
	with Session(engine) as session:
		for page in get_wikis():
			url = get_url_from_page(page)
			if url is not None:
				stmt = select(Wiki).where(Wiki.w8y_wi_page_id == page.pageid)
				wiki = session.scalars(stmt).one_or_none()
				if not wiki:
					log_message(session, f'Syncing page {page.title()} to database')
					wiki = Wiki(
						w8y_wi_page_id=page.pageid,
						w8y_wi_api_url=bytes(url, 'utf8'),
						w8y_wi_last_sr_id=None,
						w8y_wi_is_defunct=False
					)
					session.add(wiki)
					session.commit()


if __name__ == '__main__':
	run()
