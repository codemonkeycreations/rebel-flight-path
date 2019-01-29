<?php 

/*
 * (c) JOE SWARD <joe@iamacodemonkey.co.uk>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace iamacodemonkey;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;


class Droid {

	public $url;
	public $name;
	public $path;
	public $map;
	public $responseCode;
	public $debug;

	
    public function move() {


    	// we'll use Guzzle to reach the API
		$client = new Client();
		 
			// test droid's flight path - we need to retreive flight data including error codes
		try {

			$response = $client->request('GET', $this->url, ['query' => ['name'=>$this->name, 'path'=>$this->path], 'debug' => $this->debug]);

			//if we make it here, we're through!
			$this->responseCode = $response->getStatusCode();

			$data = json_decode($response->getBody());

			// save data for processing
			$this->message = $data->message;
			$this->map = $data->map;

		} catch (ClientException $e) {

			// grab the error code and any messages from the droid's recon

		    $responseCode =  $e->getResponse()->getStatusCode();
		    $data = json_decode($e->getResponse()->getBody());

			$this->responseCode = $responseCode;
			$this->message = $data->message;
			$this->map = $data->map;

		} catch (\Exception $e) {

		    // catch all other errors - we don't want the Imperial Forces to have any warnings.
		    // currently silent fail on error       
		    $this->error = true;

		}

		return $this;

    }
}