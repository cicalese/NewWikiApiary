from models import ScrapeRecord
import re
import requests
import time

class Scraper:

	def get_siteinfo(self, url):
		query = [
			'action=query',
			'meta=siteinfo',
			'siprop=general|statistics|extensions|skins',
			'format=json'
		]
		try:
			response = requests.get(url + '?' + '&'.join(query))
			if response.status_code == 200:
				response.encoding = 'utf-8-sig'
				return response.json()
			else:
				print(f'Request failed with status code: {response.status_code}')
				return None
		except requests.RequestException as e:
			print(f'Request exception: {e}')
			return None

	def scrape_site(self, url, page_id):
		data = self.get_siteinfo(url)
		if not data or not 'query' in data:
			return None
		query = data['query']
		if not 'general' in query:
			return None
		general = query['general']
		if 'generator' in general:
			mw_version = re.sub('^MediaWiki ', '', general['generator'])
		else:
			mw_version = 'unknown'
		print(mw_version)
		#for key,value in general.items():
		#	print(key + ': ' + str(value))
		scrape = ScrapeRecord(
			w8y_sr_page_id = page_id,
			w8y_sr_api_url = bytes(url, 'utf8'),
			w8y_sr_timestamp = time.time(),
			w8y_sr_is_alive = True,
			w8y_sr_mw_version = bytes(mw_version, 'utf8')
		)
		return scrape
