from argparse import ArgumentParser
from sqlalchemy.orm import Session
from sqlalchemy import select, and_
import pywikibot
import sys
import time
sys.path.append('./lib')
from models import engine, Wiki
from scraper import scrape_site
from utils import log_message


def get_args():
	parser = ArgumentParser(prog='Create', description='scrapes the data for rows in Wikis and stores the data in the database')
	parser.add_argument("-n", "--new", action="store_true", help="scrape only new pages (those that have not been scraped before)")
	parser.add_argument("-v", "--verbose", action="count", default=0, help="increase output verbosity")
	return parser.parse_args()


def get_wikis(session, new_wikis):
	if new_wikis:
		stmt = select(Wiki).where(
			and_(
				Wiki.w8y_wi_last_sr_id.is_not(None),
				Wiki.w8y_wi_is_defunct == False
			)
		)
	else:
		stmt = select(Wiki).where(
			Wiki.w8y_wi_is_defunct == False
		).order_by(
			Wiki.w8y_wi_last_sr_id
		)
	return session.scalars(stmt)


def purge(site, session, args, pageids):
	params = {
		'action': 'purge',
		'pageids': pageids
	}
	request = pywikibot.data.api.Request(site=site, parameters=params)
	result = request.submit()
	if 'purge' in result:
		for page in result['purge']:
			if 'title' in page:
				message = 'Purged page %s' % page['title']
				log_message(session, message)
				if args.verbose > 1:
					print(message)

def run():
	args = get_args()
	start_time = time.time()
	good_count = 0
	error_count = 0
	site = pywikibot.Site()
	with Session(engine) as session:
		try:
			wikis = get_wikis(session, args.new)
			message = 'Starting scraping wikis.'
			log_message(session, message)
			if args.verbose:
				print(message)
			count = 0
			for wiki in wikis:
				count += 1
				if count % 100 == 0:
					duration = time.time() - start_time
					message = 'Processed %d wikis in %d seconds' % (count, duration)
					log_message(session, message)
					if args.verbose:
						print(message)
				url = wiki.w8y_wi_api_url.decode('utf8')
				page_id = wiki.w8y_wi_page_id
				if args.verbose > 1:
					message = f'Scraping {url}'
					print(message)
				(sr_id, error) = scrape_site(url, page_id, wiki.w8y_wi_last_sr_id, args, session)
				wiki.w8y_wi_last_sr_id = sr_id
				session.add(wiki)
				session.commit()
				if error:
					error_count += 1
				else:
					good_count += 1
					purge(site, session, args, page_id)
		except KeyboardInterrupt:
			session.rollback()
		finally:
			duration = time.time() - start_time
			message = 'Completed scraping, %d complete, %d errors, %d seconds' % (good_count, error_count, duration)
			log_message(session, message)
			if args.verbose:
				print(message)


if __name__ == '__main__':
	run()
