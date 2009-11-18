<?php

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
    echo $cache->getGeneration('home');
  }
}
