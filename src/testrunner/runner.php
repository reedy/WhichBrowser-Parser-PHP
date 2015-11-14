<?php

	include_once 'src/bootstrap.php';

	use WhichBrowser\Testrunner;




	function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
	    // error was suppressed with the @-operator
	    if (0 === error_reporting()) {
	        return false;
	    }

	    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
	set_error_handler("handleError");


	$all = false;
	$command = 'compare';
	$files = array();

	array_shift($argv);

	if (count($argv)) {
		if (in_array($argv[0], array('compare', 'check', 'rebase', 'list'))) {
			$command = array_shift($argv);
		}

		if (count($argv)) {
			foreach($argv as $file) {
				if (fnmatch("*.yaml", $file)) {
					echo "MATCH!";
					$files[] = $file;
				}
				else {
					$files = array_merge($files, glob(dirname(__FILE__) . "/data/{$file}/*.yaml"));
				}
			}
		}

		else {
			$files = glob(dirname(__FILE__) . "/data/*/*.yaml");
		}
	}
	else {
		$files = glob(dirname(__FILE__) . "/data/*/*.yaml");
	}


	switch($command) {

		case 'list':
				Testrunner::search($files);
				break;

		case 'check':
				$result = Testrunner::compare($files);

				if (!$result) {
					echo "\033[0;31mTestrunner failed, please fix or rebase before building or deploying!\033[0m\n\n";
					exit(1);
				}

				break;

		case 'compare':
				$result = Testrunner::compare($files);

				if (!$result) {
					echo "\033[0;31mTestrunner failed, please look at runner.log for the details!\033[0m\n\n";
					exit(1);
				}

				break;

		case 'rebase':
				Testrunner::rebase($files, !$all);
				break;
	}

