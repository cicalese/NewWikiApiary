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

	def scrape_site(self, url, page_id, last_scrape_id):
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
		mw_version = re.sub('^MediaWiki ', '', general['generator'])
		db_version = general['dbtype'] + ': ' + general['dbversion']
		php_version = general['phpversion'] + '(' + general['phpsapi'] + ')'
		language = general['lang']
		try:
			logo = general['logo']
			favicon = general['favicon']
		except:
			logo = ''
			favicon = ''

		keys = [ 'generator', 'dbtype', 'dbversion', 'phpversion', 'phpsapi', 'lang', 'logo', 'favicon' ]
		for key in keys:
			try:
				print(key + ': ' + str(general[key]))
			except:
				pass

		statistics = query['statistics']

		vr_id = None

		scrape = ScrapeRecord(
			w8y_sr_page_id = page_id,
			w8y_sr_api_url = bytes(url, 'utf8'),
			w8y_sr_timestamp = timestamp,
			w8y_sr_is_alive = True,
			w8y_sr_mw_version = bytes(mw_version, 'utf8'),
			w8y_sr_db_version = bytes(db_version, 'utf8'),
			w8y_sr_php_version = bytes(php_version, 'utf8'),
			w8y_sr_language = bytes(language, 'utf8'),
			w8y_sr_logo = bytes(logo, 'utf8'),
			w8y_sr_favicon = bytes(favicon, 'utf8'),
			w8y_sr_general = bytes(json.dumps(general), 'utf8'),
			w8y_sr_statistics = bytes(json.dumps(statistics), 'utf8'),
			w8y_sr_vr_id = vr_id
		)
		return scrape
