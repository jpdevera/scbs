<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'third_party'.DS.'react'.DS.'LoopInterface.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'LibEventLoop.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'LibEvLoop.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'StreamSelectLoop.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Factory.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Tick'.DS.'NextTickQueue.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Tick'.DS.'FutureTickQueue.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Timer'.DS.'TimerInterface.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Timer'.DS.'Timer.php';
require_once APPPATH.'third_party'.DS.'react'.DS.'Timer'.DS.'Timers.php';

class Ci_react
{
	public function __construct()
	{

	}
}