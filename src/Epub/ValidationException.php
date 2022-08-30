<?php

namespace App\Epub;

use Exception;

class ValidationException extends Exception {
	protected $errors;

	public function __construct(array $errors) {
		$message = sprintf("There are %d error(s)", count($errors));
		parent::__construct($message, 1);
	}

	public function getErrors(): array {
		return $this->errors;
	}
}