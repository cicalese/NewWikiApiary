from pywikibot import Site, Category
from pywikibot.pagegenerators import CategorizedPageGenerator
from sqlalchemy.orm import sessionmaker
import mwparserfromhell
import requests
import time
import sys
sys.path.append('./lib')
from models import engine, ScrapeRecord
from scraper import Scraper

class Scrape:

	site = None
	session = None
	scraper = Scraper()

	def __init__(self):
		self.site = Site()
		DBSession = sessionmaker(bind=engine)
		self.session = DBSession()

	def get_wikis(self):
		category = Category(self.site, 'Wiki')
		return CategorizedPageGenerator(category, content=True)

	def get_url_from_page(self, page):
		templates = mwparserfromhell.parse(page.text).filter_templates()
		if len(templates) > 0:
			template = templates[0]
			if template.name.matches('Wiki') and template.has('url'):
				return str(template.get('url').value)
		return None

	def scrape_site(self, page):
		url = self.get_url_from_page(page)
		if url != None:
			print(url)
			scrape = self.scraper.scrape_site(url, page.pageid)
			self.session.add(scrape)
			self.session.commit()

	def main(self):
		for page in self.get_wikis():
			self.scrape_site(page)

if __name__ == '__main__':
	scrape = Scrape()
	scrape.main()
