<?
	$schemas = array();

	#
	# add a block like this for each thing you want to be able to compare.
	# you need to set label, label_a and label_b for each entry.
	# the first block is the default. a swicther is shown if you define multiple blocks.
	#


	#
	# if you have a mysql database, define db_a/db_b with host, database-name, user and password
	#

	$schemas['main'] = array(
		'db_a'		=> array('dev-db', 'my_db', 'user', null),
		'db_b'		=> array('prod-db', 'my_db', 'root', 'PASSWORD'),
		'label_a'	=> 'Dev DB',
		'label_b'	=> 'Prod DB',
		'label'		=> 'MySQL Dump',
	);


	#
	# if you have a command that will output the thing to diff, use cmd_a/cmd_b
	#

	$schemas['cmd'] = array(
		'cmd_a'		=> "perl /woo/yay/hoopla.pl",
		'cmd_b'		=> "php -q /foo/bar/baz.php",
		'label_a'	=> 'Perl thingy',
		'label_b'	=> 'PHP thingy',
		'label'		=> 'Commands',
	);


	#
	# if you have a file you need to compare, that's even easier
	#

	$schemas['files'] = array(
		'file_a'	=> 'demo_dev.sql',
		'file_b'	=> 'demo_prod.sql',
		'label_a'	=> 'Dev DB Dump',
		'label_b'	=> 'Prod DB Dump',
		'label'		=> 'Files',
	);



	#
	# if you only want to show *some* context around changes, set this to a value greater than zero.
	# it will then collapse/hide undiffering blocks longer than this
	#

	$max_context = 2;



	#
	# if there are certain strings that should be warned or alerted in your output, add them here
	#

	$warn_strings = array(
		'character set',
		'MyISAM',
		'checksum',
	);

	$alert_strings = array(
		'latin1',
		'service',
	);
