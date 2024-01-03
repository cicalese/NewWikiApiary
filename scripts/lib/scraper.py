from models import ScrapeRecord, Skin, Extension
from utils import log_message
import json
import re
import requests
import time


def get_siteinfo(url, session):
	query = [
		'action=query',
		'meta=siteinfo',
		'siprop=general|statistics|extensions',
		'format=json'
	]
	try:
		response = requests.get(url + '?' + '&'.join(query))
		if response.status_code == 200:
			response.encoding = 'utf-8-sig'
			return response.json()
		else:
			log_message(session, f'Request failed with status code: {response.status_code}')
			return None
	except requests.RequestException as e:
		log_message(session, f'Request exception: {e}')
		return None


def create_version_records(components):
	skins = []
	skin_names = []
	extensions = []
	extension_names = []
	for comp in components:
		if 'name' in comp and 'type' in comp:
			name = bytes(comp['name'], 'utf8')
			if 'version' in comp:
				version = bytes(comp['version'], 'utf8')
			else:
				version = None
			if 'url' in comp:
				url = bytes(comp['url'], 'utf8')
			else:
				url = None
			if comp['type'] == 'skin':
				if name not in skin_names:
					skins.append({
						'name': name,
						'version': version,
						'url': url
					})
					skin_names.append(name)
			else:
				if name not in extension_names:
					extensions.append({
						'name': name,
						'version': version,
						'url': url
					})
					extension_names.append(name)
	return {
		'skins': skins,
		'extensions': extensions
	}


def scrape_site(url, page_id, session):
	data = get_siteinfo(url, session)
	timestamp = time.time()

	if not data or 'query' not in data or 'general' not in data['query'] or 'statistics' not in data['query']:
		scrape = ScrapeRecord(
			w8y_sr_page_id=page_id,
			w8y_sr_api_url=bytes(url, 'utf8'),
			w8y_sr_timestamp=timestamp,
			w8y_sr_is_alive=False
		)
		session.add(scrape)
		session.commit()
		return scrape.w8y_sr_sr_id

	query = data['query']

	general = query['general']
	mw_version = re.sub('^MediaWiki ', '', general['generator'])
	db_version = general['dbtype'] + ': ' + general['dbversion']
	php_version = general['phpversion'] + '(' + general['phpsapi'] + ')'
	language = general['lang']
	if 'logo' in general:
		logo = general['logo']
	else:
		logo = ''
	if 'favicon' in general:
		favicon = general['favicon']
	else:
		favicon = ''

	statistics = query['statistics']

	if 'extensions' in query:
		extensions = query['extensions']
		versions = create_version_records(extensions)
	else:
		versions = None

	scrape = ScrapeRecord(
		w8y_sr_page_id=page_id,
		w8y_sr_api_url=bytes(url, 'utf8'),
		w8y_sr_timestamp=timestamp,
		w8y_sr_is_alive=True,
		w8y_sr_mw_version=bytes(mw_version, 'utf8'),
		w8y_sr_db_version=bytes(db_version, 'utf8'),
		w8y_sr_php_version=bytes(php_version, 'utf8'),
		w8y_sr_language=bytes(language, 'utf8'),
		w8y_sr_logo=bytes(logo, 'utf8'),
		w8y_sr_favicon=bytes(favicon, 'utf8'),
		w8y_sr_general=bytes(json.dumps(general), 'utf8'),
		w8y_sr_statistics=bytes(json.dumps(statistics), 'utf8'),
	)
	session.add(scrape)
	session.commit()
	sr_id = scrape.w8y_sr_sr_id

	for skin in versions['skins']:
		session.add(
			Skin(
				w8y_sk_sr_id=sr_id,
				w8y_sk_name=skin['name'],
				w8y_sk_version=skin['version'],
				w8y_sk_doc_url=skin['url']
			)
		)
	for extension in versions['extensions']:
		session.add(
			Extension(
				w8y_ex_sr_id=sr_id,
				w8y_ex_name=extension['name'],
				w8y_ex_version=extension['version'],
				w8y_ex_doc_url=extension['url']
			)
		)

	return sr_id
