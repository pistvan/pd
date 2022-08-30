<?php

namespace App\Command;

use App\Epub\Validator;
use App\Helpers\DownloadHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command downloads ebooks from Internet, then parses and collects metadata,
 * then save them to an .xml file.
 * @package App\Command
 */
class ParseCommand extends Command {
	protected static $defaultName = 'parse';

	public const Files = [
		'https://account.publishdrive.com/Books/Book1.epub', 
		'https://account.publishdrive.com/Books/Book2.epub', 
		'https://account.publishdrive.com/Books/Book3.epub', 
		'https://account.publishdrive.com/Books/Book4.epub', 
	];

	protected function execute(InputInterface $input, OutputInterface $output): int {
		foreach (self::Files as $uri) {
			$output->writeln("Parsing $uri ...");

			$output->write("Downloading ...");
			$file = DownloadHelper::file($uri);
			$output->writeln("OK");

			$output->writeln("Downloaded to $file");

			Validator::validateFile($file);
		}

		return 0;
	}
}
