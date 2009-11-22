<?php

/*
 * This file is part of the sfGenerationMemcachePlugin package.
 * (c) 2009 Ben Lumley
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Task to increment the generation id associated with a particular group of cached data.
 *
 * @package    sfGenerationMemcachePlugin
 * @subpackage task
 * @author     Ben Lumley
 * @version    SVN: $Id$
 */
class IncrementGenerationTask extends sfDoctrineBaseTask {
  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('group', sfCommandArgument::REQUIRED, 'The generation group'),
    ));
      
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend'),
    ));
    
    $this->namespace        = 'cache';
    $this->name             = 'increment-generation';
    $this->briefDescription = 'Increment the generation key for a group of cached items';
    $this->detailedDescription = <<<EOF
The [increment-generation|INFO] task re-calculates the top articles for each category required for home/top level categories.
Call it with:

  [php symfony populate-top-articles|INFO]
EOF;
  }
  
  protected function execute($arguments = array(), $options = array()) {

    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
    sfContext::createInstance($configuration);
    $cache = sfContext::getInstance()->getViewCacheManager();
    $this->logSection('cache', 'Incrementing generation number for group "' . $arguments['group'] . '"');
    $version = $cache->incrementGenerationGroup($arguments['group']);
    $this->logSection('cache', 'Done - now at generation "' . $version . '"');
  }
}
