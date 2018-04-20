<?php namespace Priskz\Fyler\Laravel\Processor;

use Priskz\Payload\Payload;
use Priskz\SORAD\Service\Processor\Laravel\Processor as LaravelProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Standard extends LaravelProcessor
{
	/**
	 * @OVERRIDE
	 * 
	 * Process the default values for the given defaults configuration.
	 * 
	 * @param  string $defaults Type of function to be processed.
	 * @param  array  $data    Data to be processed.
	 * @return array
	 */
	public function processDefaults($data, $defaults = [])
	{
		if(is_array($defaults))
		{
			// Replace default values with given values.
			$defaults = $data + $defaults;
			
			if(array_key_exists('file', $data))
			{
				if($data['file'] instanceof UploadedFile)
				{
					// Iterate each default key set.
					foreach($defaults as $key => $value)
					{
						// If value is not false, use that directly otherwise compute it.
						if($value !== false && empty($data[$key]))
						{
							$data[$key] = $value;
						}
						// Otherwise, do some custom processing.
						elseif(empty($data[$key]))
						{
							switch($key)
							{
								case 'original_name':
									$data[$key] = $data['file']->getClientOriginalName();
								break;

								case 'mime_type':
									$data[$key] = $data['file']->getClientMimeType();
								break;
								
								case 'size':
									$data[$key] = $data['file']->getClientSize();
								break;
														
								case 'path':
									$data[$key] = $data['file']->getRealPath();
								break;

								default:
								break;
							}
						}
					}
				}

				// Break the file name up.
				$filePart = pathinfo($data['original_name']);

				$filename = $filePart['filename'];

				// Generate a unique hashed file name.
				do
				{
					$filename = sha1($filename);

					$fullFilename = $filename . '.' . strtolower($filePart['extension']);
				}
				while(file_exists($data['destination'] . $fullFilename));

				$data['name'] = $fullFilename;
			}
		}

		return $data;
	}
}