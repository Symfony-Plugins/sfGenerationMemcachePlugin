<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in memcache.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerationMemcacheCache extends sfCache
{
  protected
    $memcache = null;

  /**
   * Initializes this sfCache instance.
   *
   * Available options:
   *
   * * memcache: A memcache object (optional)
   *
   * * host:       The default host (default to localhost)
   * * port:       The port for the default server (default to 11211)
   * * persistent: true if the connection must be persistent, false otherwise (true by default)
   *
   * * servers:    An array of additional servers (keys: host, port, persistent)
   *
   * * see sfCache for options available for all drivers
   *
   * @see sfCache
   */
  public function initialize($options = array())
  {
    parent::initialize($options);

    if (!class_exists('Memcache'))
    {
      throw new sfInitializationException('You must have memcache installed and enabled to use sfMemcacheCache class.');
    }

    if ($this->getOption('memcache'))
    {
      $this->memcache = $this->getOption('memcache');
    }
    else
    {
      $this->memcache = new Memcache();

      if ($this->getOption('servers'))
      {
        foreach ($this->getOption('servers') as $server)
        {
          $port = isset($server['port']) ? $server['port'] : 11211;
          if (!$this->memcache->addServer($server['host'], $port, isset($server['persistent']) ? $server['persistent'] : true))
          {
            throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $server['host'], $port));
          }
        }
      }
      else
      {
        $method = $this->getOption('persistent', true) ? 'pconnect' : 'connect';
        if (!$this->memcache->$method($this->getOption('host', 'localhost'), $this->getOption('port', 11211), $this->getOption('timeout', 1)))
        {
          throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $this->getOption('host', 'localhost'), $this->getOption('port', 11211)));
        }
      }
    }
  }

  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->memcache;
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = $this->memcache->get($this->getOption('prefix').$key);

    return false === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return !(false === $this->memcache->get($this->getOption('prefix').$key));
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = is_null($lifetime) ? $this->getOption('lifetime') : $lifetime;

    // save metadata
    $this->setMetadata($key, $lifetime);

    if (false !== $this->memcache->replace($this->getOption('prefix').$key, $data, false, time() + $lifetime))
    {
      return true;
    }

    return $this->memcache->set($this->getOption('prefix').$key, $data, false, time() + $lifetime);
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    // delete metadata
    $this->memcache->delete($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key);
    return $this->memcache->delete($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      return $this->memcache->flush();
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['lastModified'];
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['timeout'];
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    throw new sfCacheException('sfGenerationMemcacheCache does not support removePattern. It provides an alternative method of clearing cache.');
  }
  
  /**
   * @see sfCache
   */
  public function getMany($keys)
  {
    $values = array();
    foreach ($this->memcache->get(array_map(create_function('$k', 'return "'.$this->getOption('prefix').'".$k;'), $keys)) as $key => $value)
    {
      $values[str_replace($this->getOption('prefix'), '', $key)] = $value;
    }

    return $values;
  }

  /**
   * Gets metadata about a key in the cache.
   *
   * @param string $key A cache key
   *
   * @return array An array of metadata information
   */
  protected function getMetadata($key)
  {
    if (sfConfig::get('sf_web_debug')) {
      return $this->memcache->get($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key);
    }
    return false;
  }

  /**
   * Stores metadata about a key in the cache.
   *
   * @param string $key      A cache key
   * @param string $lifetime The lifetime
   */
  protected function setMetadata($key, $lifetime)
  {
    if (sfConfig::get('sf_web_debug')) {
      $this->memcache->set($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key, array('lastModified' => time(), 'timeout' => time() + $lifetime), false, $lifetime);
    }
  }

  /**
   * Increments a key in the cache, initialises to 1 if not exits. Used for generation numbers.
   *
   * @param string $key A cache key
   *
   * @return int new value
   */
  public function increment($key) {
    if ($val = $this->memcache->increment($this->getOption('prefix').$key, 1)) {
      return $val;
    }
    $this->memcache->add($this->getOption('prefix').$key, 0);
    return $this->memcache->increment($this->getOption('prefix').$key, 1);
  }
  
}
