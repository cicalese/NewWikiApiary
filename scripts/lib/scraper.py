from models import ScrapeRecord
import json
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
		timestamp = time.time()

		if (not data or not 'query' in data or not 'general' in data['query']
			or not 'statistics' in data['query']):
			scrape = ScrapeRecord(
				w8y_sr_page_id = page_id,
				w8y_sr_api_url = bytes(url, 'utf8'),
				w8y_sr_timestamp = timestamp,
				w8y_sr_is_alive = False
			)
			return scrape

		query = data['query']
		general = query['general']
		statistics = query['statistics']
		if 'generator' in general:
			print(general['generator'])
			mw_version = re.sub('^MediaWiki ', '', general['generator'])
		else:
			mw_version = 'unknown'
		print(mw_version)

		for key,value in statistics.items():
			print(key + ': ' + str(value))

		scrape = ScrapeRecord(
			w8y_sr_page_id = page_id,
			w8y_sr_api_url = bytes(url, 'utf8'),
			w8y_sr_timestamp = timestamp,
			w8y_sr_is_alive = True,
			w8y_sr_statistics = bytes(json.dumps(statistics), 'utf8'),
			w8y_sr_mw_version = bytes(mw_version, 'utf8')
		)
		return scrape
