<?php 

/*
 * (c) JOE SWARD <joe@iamacodemonkey.co.uk>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace iamacodemonkey;

class MapMaker 
{

	static $mapData;

	private static $finishedCode;
	private static $lostCode;
	private static $crashedCode;

	public function createMap($url, $name, $validResponseCodes, $debug=false ) 
	{
		
		$lastResponse = 0;
		$sanityCheck  = 0;

		// valid codes - assign a copy for later use
		self::$finishedCode = $validResponseCodes['finished'];
		self::$crashedCode  = $validResponseCodes['crashed'];
		self::$lostCode     = $validResponseCodes['lost'];

		// keep going until we've made it through - not sure how many steps,
		// while loop is faster than recursion (in PHP)
		while ($lastResponse != self::$finishedCode ) {

			// find path based upon where we are
			$path = $this->computeNextMove();

			// send out a droid and get data
			$droid = $this->sendDroid($url, $name, $path, $debug);

			// validate response code from droid
			$validResponse = $this->validateResponse($droid->responseCode, $validResponseCodes);

			// we've got a valid response - let's add it to our map data
			if ($validResponse == true) {

				$droidData['message'] = $droid->message;
				$droidData['map'] = explode(PHP_EOL, $droid->map);
				$droidData['path'] = $path;
				$droidData['response'] = $droid->responseCode;
				$droidData['lastMove'] = substr($path, -1);

				// make sure we exit our loop when we hit the end
				$lastResponse = $droid->responseCode;

				self::$mapData[] = $droidData;


			} else {

				// we are ignoring all errors at the moment - try again and silently fail after 5 attempts
				$sanityCheck ++;
				if ($sanityCheck > 4) {
					break;
				}

			}

		}

		// enusre that we've made it through
		if ($lastResponse == self::$finishedCode) {

			// grab the path through the minefield
			$lastMove = end(self::$mapData);

			// return the complete url with parameters
			return $url.'?'.$name.'&path='.$lastMove['path'];

		} else {

			// silently fail
			return '';

		}



	}

	private function validateResponse($response, $validCodes) 
	{

		// ensure that we've got a valid response from the droid
		if (!in_array($response, $validCodes)) {

			$valid = false;
		
		} else {

			$valid = true;
		
		}

		return $valid;

	}	

	private function sendDroid($url, $name, $path, $debug) 
	{

		// create a new droid, retrieve and return data collected
		$droid = new Droid();

		$droid->url = $url;
		$droid->name = $name;
		$droid->path = $path;
		$droid->debug = $debug;

		$droid->move();

		return $droid;

	}

	private function computeNextMove() 
	{
		
		// if we have no data, we are at the beginning - let's try moving forwards
		if (count(self::$mapData) == 0) {

			$nextMove = 'f';
		
		} else {

			// get information from last move
			$lastMove = end(self::$mapData);
				
			// find our next move based upon response code
			switch ($lastMove['response']) {
				case self::$finishedCode:
					// we've made it through - no need to do anything!
					break;

				case self::$lostCode:
					// lost contact with droid - try moving forwards
					$nextMove = $lastMove['path'] . 'f';
					break;
				
				case self::$crashedCode:
					// we've crashed - figure out next move from last move
					$nextMove = $this->findNextStep($lastMove['path'], $lastMove['lastMove']);
					break;
			}		
			
					
		}

		return $nextMove;
	}

	private function findNextStep($path, $lastMove) 
	{


		if ($lastMove == 'f') { 

				// we crashed trying to go forwards - let's try moving left first

				// remove the failed forward movement

				$this->removeFailedAttempts();

				// try moving left
				$lastKnownGood = end(self::$mapData);
				$nextStep =  $lastKnownGood['path'] . 'l';
		
		} else if ($lastMove == 'l') {

			// we crashed trying to go left - let's backtrack & trying moving right
			$this->removeFailedAttempts();

			$lastKnownGood = end(self::$mapData);
			$nextStep =  $lastKnownGood['path'] . 'r';
		
		}

		return $nextStep;

	}

	private function removeFailedAttempts()
	{

		// backtrack to last known good forward step, erasing wrong moves

		// reverse array, then loop through
		foreach(array_reverse(self::$mapData, true) as $key=>$data) {


			// remove step if it was a step left or a step forward, with a "lost" code
			if ($data['lastMove'] == 'l' || ($data['lastMove'] == 'f' && $data['response'] == self::$crashedCode)) {

				unset(self::$mapData[$key]);

			} else if ($data['lastMove'] == 'f' && $data['response'] == self::$lostCode)  {

				break;

			} else {

				break;

			}

		}

	}

}