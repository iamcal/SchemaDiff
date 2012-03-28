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
		'db_a'		=> array('dev-db1', 'speck_dev_main', 'user', null),
		'db_b'		=> array('dbmain1', 'ts_main', 'root', 'PASSWORD'),
		'label_a'	=> 'Dev DB',
		'label_b'	=> 'Prod DB',
		'label'		=> 'MySQL Dump',
	);


	#
	# for you have a command that will output the thing to diff, use cmd_a/cmd_b
	#

	$schemas['cmd'] = array(
		'cmd_a'		=> "cat dev.dump",
		'cmd_b'		=> "cat prod.dump",
		'label_a'	=> 'Dev DB',
		'label_b'	=> 'Prod DB',
		'label'		=> 'Commands',
	);


	#
	# if you have a file you need to compare, that's even easier
	#

	$schemas['files'] = array(
		'file_a'	=> 'dev.dump',
		'file_b'	=> 'prod.dump',
		'label_a'	=> 'Dev DB',
		'label_b'	=> 'Prod DB',
		'label'		=> 'Files',
	);



	#
	# if you only want to show *some* context around changes, set this to a value greater than zero.
	# it will then collapse/hide undiffering blocks longer than this
	#

	$max_context = 0;
