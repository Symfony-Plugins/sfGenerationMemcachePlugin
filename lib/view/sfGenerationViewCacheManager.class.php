<?php

/*
 * This file is part of the sfGenerationMemcachePlugin package.
 * (c) 2009 Ben Lumley
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extension to sfViewCacheManager that takes care of config and application of generation keys
 *
 * @package    sfGenerationMemcachePlugin
 * @subpackage view
 * @author     Ben Lumley
 * @version    SVN: $Id$
 */
class sfGenerationViewCacheManager extends sfViewCacheManager {
  
  public function generateCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '') {
    $key = parent::generateCacheKey($internalUri, $hostName, $vary, $contextualPrefix);
    if ($generationKey = $this->getGenerationKey($internalUri)) {
      $key .= $generationKey;
    }
    return $key;
  }

  public function getGenerationKey($internalUri) {
    $generationGroups = $this->getGenerationGroups($internalUri);

    if (!$generationGroups) {
       return false;
    }
    
    $generationKey = '';    
    
    foreach ($generationGroups as $generationGroup) {
      $generationKey .= sprintf('/gk_%s/%s', $generationGroup, $this->getGeneration($generationGroup));
    }
    
    return $generationKey;
  }
  
  public function getGenerationGroups($internalUri) {
    return $this->getCacheConfig($internalUri, 'generationGroup');
  }
  
  public function getGeneration($generationGroup) {
    $key = $this->getKeyForGroup($generationGroup);
    $generation = $this->cache->get($key);

    if (!$generation) {
        $generation = $this->cache->increment($key);
    }
    
    return $generation;
  }
  
  public function addCache($moduleName, $actionName, $options = array())
  {
    parent::addCache($moduleName, $actionName, $options);
    $this->cacheConfig[$moduleName][$actionName]['generationGroup'] = isset($options['generationGroup']) ? (array)$options['generationGroup'] : false;
  }
  
  public function incrementGenerationGroup($generationGroup) {
    $key = $this->getKeyForGroup($generationGroup);
    return $this->cache->increment($key);
  }
  
  public function incrementGenerationGroups($generationGroups) {
    foreach ($generationGroups as $generationGroup) {
      $this->incrementGenerationGroup($generationGroup);
    }
  }
  
  protected function getKeyForGroup($generationGroup) {
    return 'sfGenerationGroup:' . $generationGroup;
  }
  
}