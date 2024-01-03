import time
from models import Log


def log_message(session, message):
	logged_message = Log(
		w8y_lo_timestamp=time.time(),
		w8y_lo_message=bytes(message, 'utf8')
	)
	session.add(logged_message)
	session.commit()
