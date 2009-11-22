<?php

/*
 * This file is part of the sfGenerationMemcachePlugin package.
 * (c) 2009 Ben Lumley
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extension to sfCacheConfigHandler that sets the generationGroup property from yaml files.
 *
 * @package    sfGenerationMemcachePlugin
 * @subpackage config
 * @author     Ben Lumley
 * @version    SVN: $Id$
 */
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
