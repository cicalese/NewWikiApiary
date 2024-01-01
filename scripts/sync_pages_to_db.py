from pywikibot import Site, Category
from pywikibot.pagegenerators import CategorizedPageGenerator
import mwparserfromhell
from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki

def get_wikis():
	site = Site()
	category = Category(site, 'Wiki')
	return CategorizedPageGenerator(category, content=True)

def get_url_from_page(page):
	templates = mwparserfromhell.parse(page.text).filter_templates()
	if len(templates) > 0:
		template = templates[0]
		if template.name.matches('Wiki') and template.has('url'):
			return str(template.get('url').value)
	return None

with Session(engine) as session:
	for page in get_wikis():
		url = get_url_from_page(page)
		if url != None:
			print(url)
			print(page.pageid)
			stmt = select(Wiki).where(Wiki.w8y_wi_page_id == page.pageid)
			wiki = session.scalars(stmt).one_or_none()
			if not wiki:
				wiki = Wiki(
					w8y_wi_page_id = page.pageid,
					w8y_wi_api_url = bytes(url, 'utf8'),
					w8y_wi_last_sr_id = None
				)
				session.add(wiki)
				session.commit()
