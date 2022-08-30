<?php

namespace App\Epub;

use App\Epub\ValidationException;
use DOMDocument;
use DOMElement;
use DOMNode;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class Validator {
	/**
	 * Validates the given local file via EPUBCheck Java library,
	 * and returns the output of that library.
	 * @param string $file The file to validate.
	 * @return object Information returned by EPUBCheck library.
	 * @throws RuntimeException If some error occurs before EPUBCheck returns anything.
	 * @throws ValidationException If EPUBCheck reports errors in the file.
	 */
	static function validateFile(string $file): object {
		$args = [
			$_ENV['PATH_TO_JAVA'] ?? 'java',
			'-jar', $_ENV['PATH_TO_EPUBCHECK_JAR'] ?? '',
			$file,
			'--json', '-',
			'--profile', 'default',
			'--quiet',
		];

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
			throw new ValidationException($output->messages);
		}

		return $output;
	}

	/**
	 */
	
	/**
	 * Create a DOMNode from the error object returned by validateFile method.
	 * @param object $error The error object.
	 * @return DOMNode The generated XML node.
	 */
	static function validationErrorToXmlNode(object $error): DOMElement {
		$doc = new DOMDocument();
		$dom = $doc->createElement('error');

		if ($error->{"ID"}) {
			$dom->appendChild($doc->createElement('id', $error->{"ID"}));
		}

		if ($error->severity) {
			$dom->appendChild($doc->createElement('severity', $error->severity));
		}

		if ($error->message) {
			$dom->appendChild($doc->createElement('message', $error->message));
		}

		if ($error->locations) {
			$locationsNode = $doc->createElement('locations');
			$dom->appendChild($locationsNode);
			foreach ($error->locations as $location) {
				$locationNode = $doc->createElement('location');
				$locationsNode->appendChild($locationNode);
				foreach ($location as $key => $value) {
					if (is_null($value)) continue;

					// Append only non-null values.
					$attribute = $doc->createAttribute($key);
					$attribute->value = $value;
					$locationNode->appendChild($attribute);
				}
			}
		}

		return $dom;
	}
}
