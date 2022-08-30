<?php

namespace App\Epub;

use DOMDocument;
use ZipArchive;

class Parser {
	/**
	 * Parses an epub file and returns its metadata (author, title, publisher)
	 * as an object.
	 * @param string $file The file to parse.
	 * @return object Contains the metadata of the file.
	 * @throws ParserException If any error occurs.
	 */
	public static function parseFile(string $file): object {
		$zip = new ZipArchive();

		if ($zip->open($file) !== true) {
			throw new ParserException("Invalid .zip file.");
		}

		$rootfile = self::getRootFileFromContainerXml(
			$zip->getFromName('META-INF/container.xml')
		);

		return self::parseRootFile($zip->getFromName($rootfile));
	}

	protected static function getRootFileFromContainerXml(string|false $xml_as_string) {
		if ($xml_as_string === false) {
			throw new ParserException("There is no container.xml in .zip file");
		}

		$dom = new DOMDocument();
		$dom->loadXML($xml_as_string);

		$element = 
			($dom
				->getElementsByTagName('rootfiles')[0]
				?->getElementsByTagName('rootfile')[0]
			) ?? null;

		if (is_null($element)) {
			throw new ParserException("Possibly invalid container.xml file");
		}

		$attribute = $element->getAttribute('full-path');

		if (empty($element)) {
			throw new ParserException("Possibly invalid container.xml file");
		}

		return $attribute;
	}

	protected static function parseRootFile(string|false $xml_as_string): object {
		if ($xml_as_string === false) {
			throw new ParserException("Invalid rootfile");
		}

		$dom = new DOMDocument();
		$dom->loadXML($xml_as_string);

		$element = $dom->getElementsByTagName('metadata')[0] ?? null;

		if (is_null($element)) {
			throw new ParserException("Possibly invalid root file");
		}

		return (object)[
			'author' => $element->getElementsByTagName('creator')[0]->nodeValue ?? null,
			'title' => $element->getElementsByTagName('title')[0]->nodeValue ?? null,
			'publisher' => $element->getElementsByTagName('publisher')[0]->nodeValue ?? null,
		];
	}
}
