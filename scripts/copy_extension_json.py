import requests
import pywikibot

response = requests.get('https://www.mediawiki.org/wiki/Module:ExtensionJson?action=raw')
if response.status_code == 200:
	site = pywikibot.Site()
	if not site.user():
		site.login()
	if site.user():
		page = pywikibot.Page(site, 'Module:ExtensionJson')
		if page.exists():
			summary = 'Updated page'
		else:
			summary = 'Created page'
		page.text = response.text
		page.save(summary=summary, quiet=True)
	else:
		print("Failed to log in")
else:
	print(f"Failed to fetch data. Status code: {response.status_code}")
