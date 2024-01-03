-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: tables.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/w8y_wikis (
  w8y_wi_page_id INTEGER UNSIGNED NOT NULL,
  w8y_wi_api_url VARCHAR(255) NOT NULL,
  w8y_wi_last_sr_id INTEGER UNSIGNED DEFAULT NULL,
  w8y_wi_is_defunct BOOLEAN NOT NULL,
  PRIMARY KEY(w8y_wi_page_id)
);


CREATE TABLE /*_*/w8y_scrape_records (
  w8y_sr_sr_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  w8y_sr_page_id INTEGER UNSIGNED NOT NULL,
  w8y_sr_api_url VARCHAR(255) NOT NULL,
  w8y_sr_timestamp INTEGER UNSIGNED NOT NULL,
  w8y_sr_is_alive BOOLEAN NOT NULL,
  w8y_sr_mw_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_db_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_php_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_logo VARCHAR(255) DEFAULT NULL,
  w8y_sr_favicon VARCHAR(255) DEFAULT NULL,
  w8y_sr_language VARCHAR(255) DEFAULT NULL,
  w8y_sr_general BLOB DEFAULT NULL,
  w8y_sr_statistics BLOB DEFAULT NULL
);


CREATE TABLE /*_*/w8y_skins (
  w8y_sk_sr_id INTEGER UNSIGNED NOT NULL,
  w8y_sk_name VARCHAR(255) NOT NULL,
  w8y_sk_version VARCHAR(255) DEFAULT NULL,
  w8y_sk_doc_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(w8y_sk_sr_id, w8y_sk_name)
);


CREATE TABLE /*_*/w8y_extensions (
  w8y_ex_sr_id INTEGER UNSIGNED NOT NULL,
  w8y_ex_name VARCHAR(255) NOT NULL,
  w8y_ex_version VARCHAR(255) DEFAULT NULL,
  w8y_ex_doc_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(w8y_ex_sr_id, w8y_ex_name)
);


CREATE TABLE /*_*/w8y_log (
  w8y_lo_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  w8y_lo_timestamp INTEGER UNSIGNED NOT NULL,
  w8y_lo_message VARCHAR(255) NOT NULL
);
