<?php

namespace App\Command;

use App\Epub\Parser;
use App\Epub\ParserException;
use App\Epub\ValidationException;
use App\Epub\Validator;
use App\Helpers\DownloadHelper;
use DOMDocument;
use GuzzleHttp\Exception\GuzzleException;
use DOMException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command downloads ebooks from Internet, then parses and collects metadata,
 * then write the data to stdout in XML format.
 * @package App\Command
 */
class ParseCommand extends Command {
	protected $input;

	/** @var ConsoleOutput */
	protected $output;

	protected static $defaultName = 'parse';

	protected $xmlNodes = [];

	public const Files = [
		'https://account.publishdrive.com/Books/Book1.epub', 
		'https://account.publishdrive.com/Books/Book2.epub', 
		'https://account.publishdrive.com/Books/Book3.epub', 
		'https://account.publishdrive.com/Books/Book4.epub', 
	];

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->input = $input;
		$this->output = $output;

		foreach (self::Files as $uri) {
			$this->handleUri($uri);
		}

		$generatedXml = $this->generateXml();

		$output->writeln($generatedXml);

		return 0;
	}

	/**
	 * Manage download, validation (via Java tool) and parsing (via ZipArchive),
	 * then generate a DOMNode from all the above information.
	 * @param string $uri The URI to download.
	 * @return void 
	 * @throws GuzzleException When any network error occurs.
	 */
	protected function handleUri(string $uri) {
		$this->output->getErrorOutput()->writeln("Parsing $uri ...");

		// Download URI.
		$file = DownloadHelper::file($uri);
		$this->output->getErrorOutput()->writeln("Downloaded to $file");

		// Validate and parse.
		$validationErrors = [];
		$data = null;
		try {
			Validator::validateFile($file);
		} catch (RuntimeException $e) {
			$validationErrors[] = $e->getMessage();
		} catch (ValidationException $e) {
			$validationErrors = $e->getErrors();
		}

		try {
			$data = Parser::parseFile($file);
		} catch (ParserException $e) {
			$validationErrors[] = $e->getMessage();
		}

		// Create XML node.
		$doc = new DOMDocument();
		$dom = $doc->createElement('file');

		if ($data) {
			foreach ($data as $key => $value) {
				$dom->appendChild($doc->createElement($key, $value));
			}
		}

		$errorsNode = $doc->createElement('errors');
		foreach ($validationErrors as $error) {
			$errorNode = Validator::validationErrorToXmlNode($error);

			// Import and append to $dom.
			$importedErrorNode = $doc->importNode($errorNode, true);
			$errorsNode->appendChild($importedErrorNode);
		}
		$dom->appendChild($errorsNode);

		$this->output->getErrorOutput()->writeln("Parse OK.");

		$this->xmlNodes[] = $dom;
	}

	/**
	 * Compiles the final XML document from the previously generated DOMNodes.
	 * @return string The final XML document as a string.
	 */
	protected function generateXml(): string {
		$doc = new DOMDocument();
		$doc->formatOutput = true;

		$dom = $doc->createElement('files');

		foreach ($this->xmlNodes as $node) {
			$importedNode = $doc->importNode($node, true);
			$dom->appendChild($importedNode);
		}

		$doc->appendChild($dom);

		return $doc->saveXML();
	}
}
