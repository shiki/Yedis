<?php

namespace Yedis;

/**
 * Wraps {@link https://github.com/nrk/predis | Predis} in a Yii extension so it can easily be accessed
 * from a Yii component (e.g. `Yii::app()->yedis->client()`).
 *
 * This also allows Predis configurations to be set up in the Yii config files and then easily create
 * Predis clients using those configurations anywhere.
 *
 * To use, add this as a Yii component, and set the `clients` property to an array of
 * different Predis client configurations that you want to use. Different clients are set with different array keys.
 *
 * The Predis library files are not included in this library. You need to download it yourself and
 * then set the `predisLibPath` to its location.
 *
 * Here's an example Yii configuration using this library:
 *
 * <code>
 * ...
 * 'components' => array(
 *   'yedis' => array(
 *     'class' => '\\Yedis\\Yedis',
 *     'predisLibPath' => '/path/to/predis/base/folder',
 *     'clients' => array(
 *       'default' => array(
 *         'params' => 'tcp://127.0.0.1',
 *         'options' => array(
 *           'connections' => array('tcp' => 'Predis\Connection\PhpiredisConnection'),
 *         ),
 *       ),
 *     ),
 *   ),
 * )
 * ...
 * </code>
 *
 * Then, access your client by:
 *
 * <code>
 * $client = \Yii::app()->yedis->getClient('default');
 * </code>
 *
 * Or, just:
 *
 * <code>
 * $client = \Yii::app()->yedis->getClient(); // Assumed "default"
 * </code>
 *
 * The above will return an instance of {@link Predis\Client} with the values of `params` and `options`
 * passed to the constructor. If a client with the same configuration key was previously created,
 * {@link getClient} will return the previously created client instance. If you do not want this behavior,
 * you can use {@link createClient}.
 *
 * @see https://github.com/nrk/predis
 * @author Shiki
 */
class Yedis extends \CApplicationComponent
{
  /**
   * Should point to <path>/predis/lib. If this is not given, the default path
   * inside the extension will be used.
   *
   * @var string
   */
  public $predisLibPath;

  /**
   * Client configurations. This is normally set up in Yii config files. This is an array
   * containing configurations for {@link Predis\Client} instances that will be created using this
   * application component. Each configuration should contain a property named `params` and optionally
   * a property named `options`. The value of those properties will be passed to the constructor of `Predis\Client`.
   *
   * Sample value:
   *
   * <code>
   * array(
   *   'default' => array(
   *     'params' => ''tcp://10.0.0.1:6379',
   *   ),
   *   'multi' => array(
   *     'params' => array(
   *       array('host' => '10.0.0.1', 'port' => 6379),
   *       array('host' => '10.0.0.2', 'port' => 6379)
   *     ),
   *   ),
   *   'phpiredis' => array(
   *     'params' => 'tcp://127.0.0.1',
   *     'options' => array(
   *       'connections' => array('tcp' => 'Predis\Connection\PhpiredisConnection'),
   *     ),
   *   ),
   * )
   * </code>
   *
   * @var array
   */
  public $clients;

  /**
   *
   * @var array
   */
  protected $_clientInstances = array();

  /**
   * {@inheritdoc}
   */
  public function init()
  {
    // make sure Yii can autoload Predis\\Client
    if (!class_exists('Predis\\Client', false)) {
      $path = rtrim($this->predisLibPath, '/') . '/lib/Predis';
      \Yii::setPathOfAlias('Predis', $path);
    }

    if (!is_array($this->clients))
      $this->clients = array();

    parent::init();
  }

  /**
   * Get an instance of `Predis\Client` using the configuration pointed to by `$key`.
   * This will store the created instance locally and subsequent calls to this method using the same `$key`
   * will return the already created client.
   *
   * @param string $key The client configuration key that can be found in {@link $clients}.
   * @return Predis\Client
   */
  public function getClient($key = 'default')
  {
    if (isset($this->_clientInstances[$key]))
      return $this->_clientInstances[$key];

    $this->_clientInstances[$key] = $this->createClient($key);
    return $this->_clientInstances[$key];
  }

  /**
   * Create an instance of `Predis\Client` using the configuration pointed to by `$key`.
   *
   * @param string $key The client configuration key that can be found in {@link $clients}.
   * @return Predis\Client
   */
  public function createClient($key = 'default')
  {
    if ($key == 'default' && !isset($this->clients[$key]))
      return new \Predis\Client();

    $config = $this->clients[$key];

    $params  = isset($config['params']) ? $config['params'] : null;
    $options = isset($config['config']) ? $config['config'] : null;

    return new \Predis\Client($params, $options);
  }
}
