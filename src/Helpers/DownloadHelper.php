<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DownloadHelper {
	/** @var Client */
	protected static $client;

	protected static function init() {
		self::$client = new Client();
	}

	/**
	 * Download the given URL and returns the filename (including path).
	 * @param string $uri The URI to download.
	 * @return string Path to the downloaded file.
	 * @throws GuzzleException When error occurs during download.
	 */
	static function file(string $uri): string {
		if (is_null(self::$client)) self::init();

		$file = tempnam(sys_get_temp_dir(), 'epub');

		self::$client->get($uri, [
			'sink' => $file,
		]);

		return $file;
	}
}