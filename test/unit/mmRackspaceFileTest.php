<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

//Database connection:
$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
new sfDatabaseManager($configuration);
sfContext::createInstance($configuration);


$t = new lime_test(12, new lime_output_color());

$fileName = uniqid('rs_');
$fileFullPath = '/tmp/' . $fileName;
file_put_contents($fileFullPath, 'rackspace cloud files test!');


//test using details in app.yml
$o = new mmRackspaceFile();

$name = uniqid('container_');

$o->addContainer($name);

$container = $o->getContainer($name);
$t->ok($container instanceof CF_Container, ' container ' . $name . ' exists');

//add file to container
$o->addObjectToContainer($fileFullPath, $name);
$t->ok(fileExists($o, $name, $fileName) == true, ' file ' . $fileName . ' exists in the cloud');

//publish container
$o->publishContainer($name);
$uri = $o->getFilePublicUri($fileName, $name);
$t->ok(file_get_contents($uri), ' file ' . $fileName . ' exists in the cloud and is public in URI:' . $uri);

//UNpublish container
$o->unpublishContainer($name);
$uri = $o->getFilePublicUri($fileName, $name);
$t->ok(is_null($uri), ' container ' . $name . ' is private');


//delete file
$o->deleteObject($fileName, $name);
$t->ok(fileExists($o, $name, $fileName) == false, ' file ' . $fileName . ' DOES NOT exist in the cloud');

//delete container
$o->removeContainer($name);

try
{
  $container = $o->getContainer($name);
  $t->fail('no code should be executed after throwing an exception');
}
catch (Exception $e)
{
  $t->pass('container DOES NOT exist in the cloud');
}

//test using details passed by parameter from app.yml
$name = uniqid('container_');
$o = new mmRackspaceFile(sfConfig::get('app_rackspace_username'), sfConfig::get('app_rackspace_api_key'), sfConfig::get('app_rackspace_account'), $name);

$o->setActiveContainerName($name);

$o->addContainer();

$container = $o->getContainer();
$t->ok($container instanceof CF_Container, ' container ' . $name . ' exists');

//add file to container
$o->addObjectToContainer($fileFullPath);
$t->ok(fileExists($o, $name, $fileName) == true, ' file ' . $fileName . ' exists in the cloud');

//publish container
$o->publishContainer();
$uri = $o->getFilePublicUri($fileName);
$t->ok(file_get_contents($uri), ' file ' . $fileName . ' exists in the cloud and is public in URI:' . $uri);

//UNpublish container
$o->unpublishContainer();
$uri = $o->getFilePublicUri($fileName);
$t->ok(is_null($uri), ' container ' . $name . ' is private');


//delete file
$o->deleteObject($fileName);
$t->ok(fileExists($o, $name, $fileName) == false, ' file ' . $fileName . ' exists in the cloud');

//delete container
$o->removeContainer();
try
{
  $container = $o->getContainer($name);
  $t->fail('no code should be executed after throwing an exception');
}
catch (Exception $e)
{
  $t->pass('container DOES NOT exist in the cloud');
}


/**
*
* Helpers
*
*
**/
function fileExists($o, $name, $fileName)
{
  $list = $o->lst($name);
  foreach($list as $l)
  {
    if($l == $fileName)
    {
      return true;
    }
  }
  return false;
}
