<?php

class sfGenerationCacheConfigHandler extends sfCacheConfigHandler
{

  protected function addCache($actionName = '')
  {
    $data = parent::addCache($actionName);
    $generationGroup = $this->getConfigValue('generation_group', $actionName, false);
    if ($generationGroup) {
      $str = sprintf("'generationGroup'=>%s, 'withLayout'", var_export((array)$generationGroup, true));
      $data = str_replace("'withLayout'", $str, $data);      
    }
    return $data;
  }
  


}
