<?php

namespace App\Epub;

use App\Epub\ValidationException as EpubValidationException;
use RuntimeException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException as ExceptionRuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

class Validator {
	/**
	 * Validates the given local file via EPUBCheck Java library.
	 * @param string $file The file to validate.
	 * @return bool True if validation success.
	 * @throws RuntimeException If some error occurs before EPUBCheck returns anything.
	 * @throws ValidationException If EPUBCheck reports errors in the file.
	 */
	static function validateFile(string $file): bool {
		$args = [
			$_ENV['PATH_TO_JAVA'] ?? 'java',
			'-jar', $_ENV['PATH_TO_EPUBCHECK_JAR'] ?? '',
			$file,
			'--json', '-',
			'--profile', 'default',
			'--quiet',
		];

		printf("Execute: " . join(' ', $args). "\n");
		$process = new Process($args);

		try {
			$process->run();
		} catch (ProcessTimedOutException) {
			throw new RuntimeException("EPUBCheck libary timed out.");
		}

		$output = json_decode($process->getOutput());

		if (is_null($output)) {
			throw new RuntimeException("EPUBCheck library sent an invalid response.");
		}

		if ($process->getExitCode() == 1 && $output->messages) {
			throw new EpubValidationException($output->messages);
		}

		return true;
	}
}
