from models import ScrapeRecord, VersionRecord, SkinLink, SkinData, ExtensionLink, ExtensionData
from sqlalchemy import select
from utils import log_message
import json
import re
import requests
import time


def get_siteinfo(url, args, session):
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
			message = f'Request failed with status code: {response.status_code}'
			log_message(session, message)
			if args.verbose:
				print(message)
			return None
	except requests.RequestException as e:
		message = f'Request exception: {e}'
		log_message(session, message)
		if args.verbose:
			print(message)
		return None


def last_versions_match(session, last_sr_id, skin_versions, extension_versions):
	stmt = select(ScrapeRecord).where(ScrapeRecord.w8y_sr_sr_id == last_sr_id)
	sr = session.scalars(stmt).one_or_none()
	if sr is None:
		return False, None
	vr_id = sr.w8y_sr_vr_id
	if vr_id is None:
		return False, None

	stmt = select(SkinData).join_from(
		SkinLink,
		SkinData,
		SkinLink.w8y_sl_sd_id == SkinData.w8y_sd_sd_id
	).where(SkinLink.w8y_sl_vr_id == vr_id)
	last_skins = session.scalars(stmt)
	count = 0
	for skin in last_skins:
		name = skin.w8y_sd_name
		if name not in skin_versions or skin.w8y_sd_version != skin_versions[name]:
			return False, None
		count += 1
	if len(skin_versions) != count:
		return False, None

	stmt = select(ExtensionData).join_from(
		ExtensionLink,
		ExtensionData,
		ExtensionLink.w8y_el_ed_id == ExtensionData.w8y_ed_ed_id
	).where(ExtensionLink.w8y_el_vr_id == vr_id)
	last_extensions = session.scalars(stmt)
	count = 0
	for extension in last_extensions:
		name = extension.w8y_ed_name
		if name not in extension_versions or extension.w8y_ed_version != extension_versions[name]:
			return False, None
		count += 1
	if len(extension_versions) != count:
		return False, None

	return True, vr_id


def create_version_records(session, last_sr_id, components):
	skins = []
	skin_versions = {}
	extensions = []
	extension_versions = {}

	for comp in components:
		if 'name' in comp and 'type' in comp:
			name = bytes(comp['name'], 'utf-8')
			if 'version' in comp and comp['version'] is not None:
				if isinstance(comp['version'], str):
					version = bytes(comp['version'], 'utf-8')
				else:
					version = bytes(str(comp['version']), 'utf-8')
			else:
				version = None
			if 'url' in comp:
				url = bytes(comp['url'], 'utf-8')
			else:
				url = None
			if comp['type'] == 'skin':
				if name not in skin_versions:
					skins.append({
						'name': name,
						'version': version,
						'url': url
					})
					skin_versions[name] = version
			else:
				if name not in extension_versions:
					extensions.append({
						'name': name,
						'version': version,
						'url': url
					})
					extension_versions[name] = version

	match, last_vr_id = last_versions_match(session, last_sr_id, skin_versions, extension_versions)
	if match:
		return last_vr_id

	version_record = VersionRecord()
	session.add(version_record)
	session.commit()
	vr_id = version_record.w8y_vr_vr_id

	for skin in skins:
		stmt = select(SkinData).where(
			(SkinData.w8y_sd_name == skin['name']) &
			(SkinData.w8y_sd_version == skin['version']) &
			(SkinData.w8y_sd_doc_url == skin['url'])
		)
		skin_data = session.scalars(stmt).one_or_none()
		if skin_data is None:
			skin_data = SkinData(
				w8y_sd_name=skin['name'],
				w8y_sd_version=skin['version'],
				w8y_sd_doc_url=skin['url']
			)
			session.add(skin_data)
			session.commit()
		skin_link = SkinLink(
			w8y_sl_vr_id = vr_id,
			w8y_sl_sd_id = skin_data.w8y_sd_sd_id
		)
		session.add(skin_link)

	for extension in extensions:
		stmt = select(ExtensionData).where(
			(ExtensionData.w8y_ed_name == extension['name']) &
			(ExtensionData.w8y_ed_version == extension['version']) &
			(ExtensionData.w8y_ed_doc_url == extension['url'])
		)
		extension_data = session.scalars(stmt).one_or_none()
		if extension_data is None:
			extension_data = ExtensionData(
				w8y_ed_name=extension['name'],
				w8y_ed_version=extension['version'],
				w8y_ed_doc_url=extension['url']
			)
			session.add(extension_data)
			session.commit()
		extension_link = ExtensionLink(
			w8y_el_vr_id = vr_id,
			w8y_el_ed_id = extension_data.w8y_ed_ed_id
		)
		session.add(extension_link)

	return vr_id


def scrape_site(url, page_id, last_sr_id, args, session):
	data = get_siteinfo(url, args, session)
	timestamp = time.time()

	if not data or 'query' not in data or 'general' not in data['query'] or 'statistics' not in data['query']:
		scrape = ScrapeRecord(
			w8y_sr_page_id=page_id,
			w8y_sr_api_url=bytes(url, 'utf-8'),
			w8y_sr_timestamp=timestamp,
			w8y_sr_is_alive=False
		)
		session.add(scrape)
		session.commit()
		return scrape.w8y_sr_sr_id, True

	query = data['query']

	general = query['general']
	mw_version = re.sub('^MediaWiki ', '', general['generator'])
	if 'dbtype' in general and 'dbversion' in general:
		db_version = general['dbtype'] + ': ' + general['dbversion']
	else:
		db_version = ''
	if 'phpversion' in general and 'phpsapi' in general:
		php_version = general['phpversion'] + '(' + general['phpsapi'] + ')'
	else:
		php_version = ''
	language = general['lang']
	if 'logo' in general and len(general['logo']) < 256:
		logo = general['logo']
	else:
		logo = ''
	if 'favicon' in general:
		favicon = general['favicon']
	else:
		favicon = ''

	statistics = query['statistics']

	if 'extensions' in query:
		vr_id = create_version_records(session, last_sr_id, query['extensions'])
	else:
		vr_id = None

	scrape = ScrapeRecord(
		w8y_sr_page_id=page_id,
		w8y_sr_api_url=bytes(url, 'utf-8'),
		w8y_sr_timestamp=timestamp,
		w8y_sr_is_alive=True,
		w8y_sr_vr_id=vr_id,
		w8y_sr_mw_version=bytes(mw_version, 'utf-8'),
		w8y_sr_db_version=bytes(db_version, 'utf-8'),
		w8y_sr_php_version=bytes(php_version, 'utf-8'),
		w8y_sr_language=bytes(language, 'utf-8'),
		w8y_sr_logo=bytes(logo, 'utf-8'),
		w8y_sr_favicon=bytes(favicon, 'utf-8'),
		w8y_sr_general=bytes(json.dumps(general), 'utf-8'),
		w8y_sr_statistics=bytes(json.dumps(statistics), 'utf-8'),
	)
	session.add(scrape)
	session.commit()
	sr_id = scrape.w8y_sr_sr_id
	return sr_id, False
