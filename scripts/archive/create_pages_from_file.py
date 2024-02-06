from argparse import ArgumentParser
import requests
import pywikibot
from pywikibot.exceptions import InvalidTitleError, PageRelatedError
from sqlalchemy.orm import Session
from sqlalchemy import select
import time
import sys
sys.path.append('./lib')
from models import engine, Wiki
from utils import log_message


def get_args():
	parser = ArgumentParser(prog='Create', description='creates pages in wiki corresponding to URLs in file')
	parser.add_argument('file', help='file to read URLs from, one per line')
	parser.add_argument("-v", "--verbose", action="count", default=0, help="increase output verbosity")
	return parser.parse_args()


def get_sitename(apiurl, args, errors, session):
	query = [
		'action=query',
		'meta=siteinfo',
		'siprop=general',
		'format=json'
	]
	try:
		response = requests.get(apiurl + '?' + '&'.join(query))
		if response.status_code >= 200 and response.status_code < 300:
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
			message = f'Request to {apiurl} failed with status code: {response.status_code}'
			log_message(session, message)
			if args.verbose:
				print(message)
			errors.append(apiurl)
			return None
	except requests.RequestException as e:
		message = f'Request to {apiurl} raised exception: {e}'
		log_message(session, message)
		if args.verbose:
			print(message)
		errors.append(apiurl)
		return None


def run():
	args = get_args()
	start_time = time.time()
	errors = []
	site = pywikibot.Site()
	with Session(engine) as session:
		message = 'Starting importing URLs.'
		log_message(session, message)
		if args.verbose:
			print(message)
		count = 0
		try:
			file_path = args.file
			with open(file_path, 'r') as file:
				for idx, line in enumerate(file):
					count = idx
					if idx % 100 == 0:
						duration = time.time() - start_time
						message = 'Processed %d URLs in %d seconds' % (idx, duration)
						log_message(session, message)
						if args.verbose:
							print(message)
					url = line.strip()
					sitename = get_sitename(url, args, errors, session)
					if sitename:
						try:
							log_message(
								session,
								f'Creating/updating page for site {sitename} with URL {url}'
							)
							page = pywikibot.Page(site, 'Wiki:' + sitename)
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
						except (InvalidTitleError, PageRelatedError):
							message = f'Error from {url} with {sitename}'
							log_message(session, message)
							if args.verbose:
								print(message)
							errors.append(url)
		except KeyboardInterrupt:
			pass
		finally:
			duration = time.time() - start_time
			message = 'Completed importing URLs, %d complete in %d seconds' % (count, duration)
			log_message(session, message)
			if args.verbose:
				print(message)
			for error in errors:
				message = f'Bad URL: {error}'
				log_message(session, message)
				if args.verbose:
					print(message)


if __name__ == '__main__':
	run()
