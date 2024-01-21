-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: tables.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/w8y_wikis (
  w8y_wi_page_id INT UNSIGNED NOT NULL,
  w8y_wi_api_url VARCHAR(255) NOT NULL,
  w8y_wi_last_sr_id INT UNSIGNED DEFAULT NULL,
  w8y_wi_is_defunct TINYINT(1) NOT NULL,
  INDEX w8y_wi_last_sr_id (w8y_wi_last_sr_id),
  PRIMARY KEY(w8y_wi_page_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_scrape_records (
  w8y_sr_sr_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  w8y_sr_page_id INT UNSIGNED NOT NULL,
  w8y_sr_api_url VARCHAR(255) NOT NULL,
  w8y_sr_timestamp INT UNSIGNED NOT NULL,
  w8y_sr_is_alive TINYINT(1) NOT NULL,
  w8y_sr_vr_id INT UNSIGNED DEFAULT NULL,
  w8y_sr_mw_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_db_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_php_version VARCHAR(255) DEFAULT NULL,
  w8y_sr_logo VARCHAR(255) DEFAULT NULL,
  w8y_sr_favicon VARCHAR(255) DEFAULT NULL,
  w8y_sr_language VARCHAR(255) DEFAULT NULL,
  w8y_sr_general BLOB DEFAULT NULL,
  w8y_sr_statistics BLOB DEFAULT NULL,
  INDEX w8y_sr_page_id_by_timestamp (
    w8y_sr_page_id, w8y_sr_timestamp
  ),
  INDEX w8y_sr_mw_version (w8y_sr_sr_id, w8y_sr_mw_version),
  INDEX w8y_sr_db_version (w8y_sr_sr_id, w8y_sr_db_version),
  INDEX w8y_sr_php_version (
    w8y_sr_sr_id, w8y_sr_php_version
  ),
  PRIMARY KEY(w8y_sr_sr_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_last_version_record_id (
  w8y_vr_vr_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(w8y_vr_vr_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_skin_links (
  w8y_sl_vr_id INT UNSIGNED NOT NULL,
  w8y_sl_sd_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(w8y_sl_vr_id, w8y_sl_sd_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_skin_data (
  w8y_sd_sd_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  w8y_sd_name VARCHAR(255) NOT NULL,
  w8y_sd_version VARCHAR(255) DEFAULT NULL,
  w8y_sd_doc_url VARCHAR(255) DEFAULT NULL,
  UNIQUE INDEX w8y_sd (
    w8y_sd_name, w8y_sd_version, w8y_sd_doc_url
  ),
  INDEX w8y_sd_name (w8y_sd_name),
  PRIMARY KEY(w8y_sd_sd_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_extension_links (
  w8y_el_vr_id INT UNSIGNED NOT NULL,
  w8y_el_ed_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(w8y_el_vr_id, w8y_el_ed_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_extension_data (
  w8y_ed_ed_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  w8y_ed_name VARCHAR(255) NOT NULL,
  w8y_ed_version VARCHAR(255) DEFAULT NULL,
  w8y_ed_doc_url VARCHAR(255) DEFAULT NULL,
  UNIQUE INDEX w8y_ed (
    w8y_ed_name, w8y_ed_version, w8y_ed_doc_url
  ),
  INDEX w8y_ed_name (w8y_ed_name),
  PRIMARY KEY(w8y_ed_ed_id)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/w8y_log (
  w8y_lo_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  w8y_lo_timestamp INT UNSIGNED NOT NULL,
  w8y_lo_message VARCHAR(255) NOT NULL,
  PRIMARY KEY(w8y_lo_id)
) /*$wgDBTableOptions*/;
