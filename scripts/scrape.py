import pywikibot
from pywikibot.pagegenerators import CategorizedPageGenerator
import mwparserfromhell
from sqlalchemy.orm import sessionmaker
from models import engine, ScrapeRecord

site = pywikibot.Site()
DBSession = sessionmaker(bind=engine)
session = DBSession()

category = pywikibot.Category(site, 'Wiki')
for page in CategorizedPageGenerator(category, content=True):
	templates = mwparserfromhell.parse(page.text).filter_templates()
	if len(templates) > 0:
		template = templates[0]
		if template.name.matches('Wiki') and template.has('url'):
			url = template.get('url').value
			print(url)
			scrape = ScrapeRecord(
				w8y_sr_page_id = page.pageid,
				w8y_sr_api_url = url
			)
			session.add(scrape)

session.commit()
