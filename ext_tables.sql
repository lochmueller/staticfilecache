#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_staticfilecache_cache tinyint(1) DEFAULT '1',
	tx_staticfilecache_cache_force tinyint(1) DEFAULT '0',
	tx_staticfilecache_cache_offline tinyint(1) DEFAULT '0',
	tx_staticfilecache_cache_priority int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_staticfilecache_queue'
#
CREATE TABLE tx_staticfilecache_queue (
	uid int(11) NOT NULL auto_increment,
	cache_url varchar(500) NOT NULL,
	cache_priority int(11) DEFAULT '0' NOT NULL,
	page_uid int(11) DEFAULT '0' NOT NULL,
	invalid_date int(11) DEFAULT '0' NOT NULL,
	call_date int(11) DEFAULT '0' NOT NULL,
	call_result varchar(255) NOT NULL,
	PRIMARY KEY (uid),
	INDEX call_date (call_date, cache_url(100))
);
