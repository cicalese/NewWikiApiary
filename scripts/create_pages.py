from argparse import ArgumentParser
import requests
import pywikibot
from pywikibot.exceptions import InvalidTitleError
from sqlalchemy.orm import Session
from sqlalchemy import select
import time
import sys
sys.path.append('./lib')
from models import engine, SkinData, ExtensionData
from utils import log_message


def get_args():
	parser = ArgumentParser(prog='Create', description='creates pages in wiki corresponding to skins and extensions in database')
	parser.add_argument("-v", "--verbose", action="count", default=0, help="increase output verbosity")
	return parser.parse_args()


def get_skins(session):
	stmt = select(SkinData.w8y_sd_name).group_by(
		SkinData.w8y_sd_name
	)
	return session.scalars(stmt)


def get_extensions(session):
	stmt = select(ExtensionData.w8y_ed_name).group_by(
		ExtensionData.w8y_ed_name
	)
	return session.scalars(stmt)


def create_pages(components, args, site, session, componentType, namespace, template, errors):
	count = 0
	start_time = time.time()
	for component in components:
		count = count + 1
		if count % 100 == 0:
			now = time.time()
			duration = now - start_time
			message = 'Processed %d %s in %d seconds' % (count, componentType, duration)
			log_message(session, message)
			if args.verbose:
				print(message)
		try:
			database_name = component.decode('utf-8')
			badchars = ['[', ']', '>', '<']
			sanitized_name = database_name
			for char in badchars:
				sanitized_name = sanitized_name.replace(char, '')
			pagename = namespace + ':' + sanitized_name
			page = pywikibot.Page(site, pagename)
			if page.exists():
				message = f'{pagename} already exists'
				log_message(session, message)
				if args.verbose > 1:
					print(message)
			else:
				message = f'Creating page {pagename}'
				log_message(session, message)
				if args.verbose:
					print(message)
				summary = 'Created page'
				page.text = f'{{{{{template}|name={database_name}}}}}'
				page.save(summary=summary, quiet=True)
		except InvalidTitleError:
			errors.append(pagename)
			message = f'Invalid title: {pagename}'
			log_message(session, message)
			if args.verbose:
				print(message)

def run():
	args = get_args()
	start_time = time.time()
	site = pywikibot.Site()
	if not site.user():
		site.login()
	if site.user():
		errors = []
		with Session(engine) as session:
			try:
				message = 'Starting creating skin pages.'
				log_message(session, message)
				if args.verbose:
					print(message)
				skins = get_skins(session)
				create_pages(skins, args, site, session, 'skins', 'Skin', 'Skin', errors)
	
				message = 'Starting creating extension pages.'
				log_message(session, message)
				if args.verbose:
					print(message)
				extensions = get_extensions(session)
				create_pages(extensions, args, site, session, 'extensions', 'Extension', 'Extension', errors)
			except KeyboardInterrupt:
				pass
			finally:
				duration = time.time() - start_time
				message = 'Completed creating skin and extension pages in %d seconds' % (duration)
				log_message(session, message)
				if args.verbose:
					print(message)
				for error in errors:
					message = f'Bad page title: {error}'
					log_message(session, message)
					if args.verbose:
						print(message)
	else:
		message = 'User login failure.'
		log_message(session, message)
		if args.verbose:
			print(message)


if __name__ == '__main__':
	run()
