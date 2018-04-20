<?php namespace Priskz\Fyler\Laravel;

use Priskz\Payload\Payload;
use Priskz\Fyler\Laravel\Processor\Standard as Processor;
use Priskz\SORAD\Service\Laravel\GenericService;

class Service extends GenericService
{
	/**
	 *  Persitence Configuration
	 */
	protected $configuration = [
		'UPLOAD' => [
			'keys' => [
				'file', 'original_name', 'mime_type', 'size', 'destination', 'name', 'path',
			],
			'rules' => [
				'file'          => 'required|file',
				'original_name' => 'required',
				'mime_type'     => 'required',
				'size'          => 'required|integer|max:10000000',
				'destination'   => 'required',
				'name'          => 'required',
				'path'          => 'required',
				'context'       => [],
			],
			'defaults' => [
				'original_name' => false,
				'mime_type'     => false,
				'size'          => false,
				'destination'   => '/tmp/',
				'name'          => false,
				'path'          => false,
			],
		],
		'SCRAPE' => [
			'keys'     => [
				'url',
			],
			'rules'    => [
				'url' => 'required',
			],
			'defaults' => [
				'destination' => '/tmp/',
			],
		],
		'MOVE' => [
			'keys'     => [
				'destination', 'path', 'name',
			],
			'rules'    => [
				'destination' => 'required',
				'path'        => 'required',
				'name'        => 'required',
				'context'     => [],
			],
			'defaults' => [],
		],
	];

	/**
	 *	Constructor
	 */
	public function __construct()
	{
		// @todo: This is a Laravel shortcut way to do this.
		parent::__construct(\App::make('Priskz\Fyler\Laravel\Processor\Standard'));
	}

	/**
	 * Upload a File.
	 *
	 * @param  array  $data
	 * 
	 * @return Payload
	 */
	public function upload($data)
	{
		// Process the given data.
		$processPayload = $this->process(__FUNCTION__, $data);

		// Verify that the data is valid.
		if($processPayload->getStatus() != 'valid')
		{
			return $processPayload;
		}

		$movePayload = $this->move($processPayload->getData(), true);

		if($movePayload->getStatus() != 'moved')
		{
			return $movePayload;
		}

		return new Payload($movePayload->getData(), 'uploaded');
	}

	/**
	 * Move a File.
	 *
	 * @param  array $data
	 * @param  bool  $uploaded
	 * 
	 * @return Payload
	 */
	public function move($data, $uploaded = true)
	{
		// Process the given data.
		$processPayload = $this->process(__FUNCTION__, $data);

		// Verify that the data is valid.
		if($processPayload->getStatus() != 'valid')
		{
			return $processPayload;
		}

		// Utilize PHP's built in move_upload_file() method by default for it's security benefits.
		if($uploaded)
		{
			$moved = move_uploaded_file($data['path'], $data['destination'] . $data['name']);
		}
		else
		{
			$moved = rename($data['path'], $data['destination'] . $data['name']);
		}

		// Set the Payload move status.
		if($moved)
		{
			$status = 'moved';
		}
		else
		{
			$status = 'not_moved';
		}

		return new Payload($processPayload->getData() + $data, $status);
	}

	/**
	 * @todo : Not Yet Implemented Fully
	 * 
	 * Scrape a file from an external source.
	 *
	 * @param  array  $data
	 * 
	 * @return Payload
	 */
	public function scrape($data)
	{
		// Process the given data.
		$processPayload = $this->process(__FUNCTION__, $data);

		// Verify that the data is valid.
		if($processPayload->getStatus() != 'valid')
		{
			return $processPayload;
		}

		// Sanitize the scraped file name.
		$path_parts = pathinfo($url);
		$fileName   = substr(preg_replace("/[^-_a-zA-Z0-9]/", '', str_replace('.', '_', $path_parts['filename'])), 0, 10);
		$unqiue     = date('_Y_m_d_H_i_s', time()) . '_' . mt_rand();
		$extension  = $path_parts['extension'];

		// Concat File Name
		$fileName = $fileName . '_' . $unqiue . '.' . $extension;

		// Go get the file contents.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

		$data = curl_exec($ch);
		curl_close($ch);

		// Create our File Path && name.
		$tempFile = $scrapeToPath . $fileName;

	    // Create upload directory if it doesn't exist.
	    if (!file_exists($scrapeToPath))
	    {
	      mkdir($scrapeToPath, 0777, true);
	    }

		$openedFile = fopen($tempFile, 'wb');

		fwrite($openedFile, $data);

		fclose($openedFile);

	    if(file_exists($tempFile))
	    {
	      $asset = $scrapeToPath . $fileName;

	      return $asset;
	    }

	    return false;
	}

	/**
	 * Process Data for the Given Context.
	 *
	 * @param  string $context Type of function to be processed.
	 * @param  array  $data    Data to be processed.
	 * @return Payload
	 */
	protected function process($context, $data)
	{
		// Make sure the given context is all uppercase.
		$context = strtoupper($context);

		// Ensure the given context is configured before processing.
		if( ! array_key_exists($context, $this->configuration))
		{
			return new Payload(null, strtolower($context . '_not_configured'));
		}

		//  Finally, process the data.
		return $this->processor->process($data, $this->configuration[$context]['keys'], $this->configuration[$context]['rules'], $this->configuration[$context]['defaults']);
	}
}