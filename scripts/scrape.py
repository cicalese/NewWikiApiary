from argparse import ArgumentParser
from sqlalchemy.orm import Session
from sqlalchemy import select
import sys
import time
sys.path.append('./lib')
from models import engine, Wiki
from scraper import scrape_site
from utils import log_message


def get_args():
	parser = ArgumentParser(prog='Create', description='creates pages in wiki corresponding to URLs in file')
	parser.add_argument("-v", "--verbose", action="count", default=0, help="increase output verbosity")
	return parser.parse_args()


def get_wikis(session):
	stmt = select(Wiki).where(Wiki.w8y_wi_is_defunct == False)
	return session.scalars(stmt)


def run():
	args = get_args()
	start_time = time.time()
	good_count = 0
	error_count = 0
	with Session(engine) as session:
		try:
			wikis = get_wikis(session)
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
				(sr_id, error) = scrape_site(url, page_id, args, session)
				wiki.w8y_wi_last_sr_id = sr_id
				session.add(wiki)
				session.commit()
				if error:
					error_count += 1
				else:
					good_count += 1
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
