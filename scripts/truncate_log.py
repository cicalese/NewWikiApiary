from sqlalchemy import func, select, delete
import sys
sys.path.append('./lib')
from models import engine, Log


rows_to_keep = 1000
log_table = Log.__table__
with engine.connect() as conn:
	count = conn.scalar(select(func.count(log_table.c.w8y_lo_id)))
	print('Count of log rows before: %d' % count)
	if count > rows_to_keep:
		max_id = conn.scalar(select(func.max(log_table.c.w8y_lo_id)))
		sql = delete(log_table).where(log_table.c.w8y_lo_id <= max_id - rows_to_keep)
		conn.execute(sql)
		conn.commit()
	count = conn.scalar(select(func.count(log_table.c.w8y_lo_id)))
	print('Count of log rows after: %d' % count)
