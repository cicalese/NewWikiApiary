import requests
import pywikibot
from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
sys.path.append('./lib')
from models import engine, Wiki
from utils import log_message


def get_sitename(apiurl, session):
	query = [
		'action=query',
		'meta=siteinfo',
		'siprop=general',
		'format=json'
	]
	try:
		response = requests.get(apiurl + '?' + '&'.join(query))
		if response.status_code == 200:
			response.encoding = 'utf-8-sig'
			data = response.json()
			if 'query' in data:
				query = data['query']
				if 'general' in query:
					general = query['general']
					if 'sitename' in general and 'lang' in general:
						return general['sitename'] + ' (' + general['lang'] + ')'
			return None
		else:
			log_message(
				session,
				f'Request to {apiurl} failed with status code: {response.status_code}'
			)
			errors.append(apiurl)
			return None
	except requests.RequestException as e:
		log_message(session, f'Request to {apiurl} raised exception: {e}')
		errors.append(apiurl)
		return None


errors = []
def run():
	site = pywikibot.Site()
	with Session(engine) as session:
		try:
			file_path = 'urls.txt'
			with open(file_path, 'r') as file:
				for idx, line in enumerate(file):
					url = line.strip()
					sitename = get_sitename(url, session)
					if sitename:
						log_message(
							session,
							f'Creating/updating page for site {sitename} with URL {url}'
						)
						page = pywikibot.Page(site, sitename)
						if page.exists():
							comment = 'Updated page'
						else:
							comment = 'Created page'
						page.text = f'{{{{Wiki|url={url}}}}}'
						page.save(comment)
						stmt = select(Wiki).where(Wiki.w8y_wi_page_id == page.pageid)
						wiki = session.scalars(stmt).one_or_none()
						if wiki:
							wiki.w8y_wi_api_url = bytes(url, 'utf8')
							wiki.w8y_wi_last_sr_id = None
						else:
							wiki = Wiki(
								w8y_wi_page_id=page.pageid,
								w8y_wi_api_url=bytes(url, 'utf8'),
								w8y_wi_last_sr_id=None,
								w8y_wi_is_defunct=False
							)
							session.add(wiki)
						session.commit()
		except KeyboardInterrupt:
			pass
		finally:
			for error in errors:
				log_message(session, f'Bad URL: {error}')


if __name__ == '__main__':
	run()
