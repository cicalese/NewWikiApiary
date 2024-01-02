import requests
import pywikibot
from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki

errors = []

def get_sitename(url):
	query = [
		'action=query',
		'meta=siteinfo',
		'siprop=general',
		'format=json'
	]
	try:
		response = requests.get(url + '?' + '&'.join(query))
		if response.status_code == 200:
			response.encoding = 'utf-8-sig'
			data = response.json()
			if 'query' in data:
				query = data['query']
				if 'general' in query:
					general = query['general']
					if 'sitename' in general and 'lang' in general:
						sitename = general['sitename']
						language = general['lang']
						return sitename + ' (' + language + ')'
			return None
		else:
			print(f'Request failed with status code: {response.status_code}')
			errors.append(url)
			return None
	except requests.RequestException as e:
		print(f'Request exception: {e}')
		errors.append(url)
		return None

try:
	site = pywikibot.Site()
	with Session(engine) as session:
		file_path = 'urls.txt'
		with open(file_path, 'r') as file:
			for idx, line in enumerate(file):
				url = line.strip()
				sitename = get_sitename(url)
				if sitename:
					print(url)
					print(sitename)
					page = pywikibot.Page(site, sitename)
					if page.exists():
						comment = 'Updated page'
					else:
						comment = 'Created page'
					page.text = f'{{{{Wiki|url={url}}}}}'
					page.save(comment)
					print(page.pageid)
					stmt = select(Wiki).where(Wiki.w8y_wi_page_id == page.pageid)
					wiki = session.scalars(stmt).one_or_none()
					if wiki:
						wiki.w8y_wi_api_url = bytes(url, 'utf8')
						wiki.w8y_wi_last_sr_id = None
					else:
						wiki = Wiki(
							w8y_wi_page_id = page.pageid,
							w8y_wi_api_url = bytes(url, 'utf8'),
							w8y_wi_last_sr_id = None,
							w8y_wi_is_defunct = False
						)
						session.add(wiki)
					session.commit()
except KeyboardInterrupt:
	print('Interrupted')
finally:
	if errors:
		print('Bad URLs:')
		print(*errors, sep='\n')

