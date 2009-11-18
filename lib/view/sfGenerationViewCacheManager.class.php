<?php
class sfGenerationViewCacheManager extends sfViewCacheManager {
  
  public function generateCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '') {
    $key = parent::generateCacheKey($internalUri, $hostName, $vary, $contextualPrefix);
    if ($generationKey = $this->getGenerationKey($internalUri)) {
      $key .= $generationKey;
    }
    echo $key;
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
    $this->cacheConfig[$moduleName][$actionName]['generationGroup'] = isset($options['generationGroup']) ? $options['generationGroup'] : false;
  }
  
  public function incrementGenerationGroup($generationGroup) {
    $key = $this->getKeyForGroup($generationGroup);
    return $this->cache->increment($key);
  }
  
  protected function getKeyForGroup($generationGroup) {
    return 'sfGenerationGroup:' . $generationGroup;
  }
  
}