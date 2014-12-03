CREATE TABLE pages (
	tx_bgmhreflang_1 int(11) unsigned DEFAULT '0' NOT NULL,
	tx_bgmhreflang_2 int(11) unsigned DEFAULT '0' NOT NULL,
);

CREATE TABLE tx_bgmhreflang_page_page_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,

	KEY uid_local_foreign (uid_local,uid_foreign),
	KEY uid_foreign_tablenames (uid_foreign,tablenames)
);