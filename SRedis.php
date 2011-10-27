<?php

/**
 * Wraps Predis in a Yii extension that can be accessed by Yii::app()->sredis.
 *
 * @see https://github.com/nrk/predis
 * @author Shiki
 */
class SRedis extends CApplicationComponent
{
  /**
   *
   * @var array
   */
  public $connections;
  
  /**
   * Should point to <path>/predis/lib. If this is not given, the default path
   * inside the extension will be used.
   * @var string
   */
  public $predisLibPath;
  
  /**
   *
   * @var array
   */
  private $_clients = array();
  
  public function init()
  {
    // make sure Yii can autoload Predis\\Client
    if (!class_exists('Predis\\Client', false)) {
      $path = !empty($this->predisLibPath) ? $this->predisLibPath : dirname(__FILE__) . '/predis/lib';
      $path = rtrim($path, '/') . '/Predis';
      Yii::setPathOfAlias('Predis', $path);
    }
    
    if (!is_array($this->connections))
      $this->connections = array();
    
    parent::init();
  }
  
  /**
   *
   * @param string $name
   * @return Predis\Client
   */
  public function client($name = 'default')
  {
    if (!isset($this->_clients[$name])) {
      // @todo error when client was not created or $this->connections[$name] is not set
      $client = null;
      
      if ($name == 'default' && !isset($this->connections[$name]))
        $client = new Predis\Client();
      else
        $client = new Predis\Client($this->connections[$name]);
            
      $this->_clients[$name] = $client;
    }
    
    return $this->_clients[$name];
  }
}