from pywikibot import Site, Category
from pywikibot.pagegenerators import CategorizedPageGenerator
from sqlalchemy.orm import sessionmaker
from models import engine, ScrapeRecord
import mwparserfromhell
import requests
import time

class Scraper:

	site = None
	session = None

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

	def get_siteinfo(self, url):
		query = [
			'?action=query',
			'&meta=siteinfo',
			'&siprop=statistics|extensions|skins',
			'&format=json'
		]
		try:
			response = requests.get(url + "".join(query))
			if response.status_code == 200:
				return response.json()
			else:
				print(f"Request failed with status code: {response.status_code}")
				return None
		except requests.RequestException as e:
			print(f"Request exception: {e}")
			return None

	def scrape_site(self, page):
		url = self.get_url_from_page(page)
		if url != None:
			print(url)
			json_data = self.get_siteinfo(url)
			if json_data:
				print(json_data)
			scrape = ScrapeRecord(
				w8y_sr_page_id = page.pageid,
				w8y_sr_api_url = bytes(url, 'utf8'),
				w8y_sr_timestamp = b'7',
				w8y_sr_is_alive = True,
				w8y_sr_mw_version = b'1.39'
			)
			#FIXME: DB type needs to be updated
			#w8y_sr_timestamp = time.time_ns(),
			self.session.add(scrape)
			self.session.commit()

	def main(self):
		for page in self.get_wikis():
			self.scrape_site(page)

if __name__ == '__main__':
	scrape = Scraper()
	scrape.main()
