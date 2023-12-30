import pywikibot
from urllib.parse import urlparse

site = pywikibot.Site()
file_path = 'urls.txt'
with open(file_path, 'r') as file:
	for idx, line in enumerate(file):
		url = line.strip()
		page_name = urlparse(url).hostname
		page = pywikibot.Page(site, page_name)
		page.text = f'{{{{Wiki|url={url}}}}}'
		page.save('Created page')
