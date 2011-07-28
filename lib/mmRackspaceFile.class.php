<?php

require dirname(__FILE__) . '/../vendor/php-cloudfiles/cloudfiles.php';

class mmRackspaceFile
{
  private
    $_username
  , $_key
  , $_account
  , $_activeContainerName
  , $_connection
  , $_container = array()
  ;

  public function __construct($username = null, $key = null, $account = null, $containerName = null)
  {
    $this->_username = is_null($username) ? sfConfig::get('app_rackspace_username') : $username;
    $this->_key = is_null($key) ? sfConfig::get('app_rackspace_api_key') : $key;
    $this->_account = is_null($account) ? sfConfig::get('app_rackspace_account') : $account;
    $this->_activeContainerName = $containerName;

    $this->_connect();
  }

  public function setActiveContainerName($containerName)
  {
    $this->_activeContainerName = $containerName;
  }

  public function addContainer($name = null)
  {
    $name = is_null($name) ? $this->_activeContainerName : $name;
    if(is_null($name))
    {
      throw new Exception(__FUNCTION__ . ': You have to specify a container either by telling me its name or specifying globally calling setActiveContainerName');
    }
    $this->_container[$name] = $this->_connection->create_container($name);

    return $this->_container[$name];
  }

  public function removeContainer($name = null)
  {
    $container = $this->_getContainer($name);

    $this->_connection->delete_container($container);
    $this->_removeContainer($container->name);
  }

  public function getContainer($name = null)
  {
    return $this->_getContainer($name);
  }

  /**
  * Uploads a file to given container
  *
  *
  * @param $name The Container Name, if null tries to use the current connections active one, if it doesnt exist, creates it
  * @param $file The file full path
  *
  **/
  public function addObjectToContainer($file, $name = null)
  {
    try {
      $container = $this->_getContainer($name);
      $o = $container->create_object(basename($file));
      return $o->load_from_filename($file);
    } catch(Exception $e) {
      //error_log('Exception:' . $e->getMessage().PHP_EOL, 3, '/tmp/a');
    }

  }

  public function deleteObject($object, $containerName = null)
  {
    $container = $this->_getContainer($containerName);

    $container->delete_object($object);
  }

  public function publishContainer($name = null)
  {
    return $this->_getContainer($name)->make_public();
  }

  public function unpublishContainer($name = null)
  {
    return $this->_getContainer($name)->make_private();
  }

  public function getFilePublicUri($objectName, $name = null)
  {
    $container = $this->_getContainer($name);
    $obj = $container->get_object($objectName);

    return $obj->public_uri();
  }

  public function lst($name = null)
  {
    $container = $this->_getContainer($name);

    return $container->list_objects();
  }


  /** PRIVATE METHODS **/

  private function _connect()
  {
    $this->_account = null; //@TODO
    $authHost = sfConfig::get('app_rackspace_is_uk', false) ? UK_AUTHURL : US_AUTHURL;
    $auth = new CF_Authentication($this->_username, $this->_key, $this->_account, $authHost);
    //$auth->ssl_use_cabundle();
    $auth->authenticate();
    $this->_connection = new CF_Connection($auth);
  }

  private function _getContainer($name = null)
  {
    if(is_null($name))
    {
      if(is_null($this->_activeContainerName))
      {
        throw new Exception(__FUNCTION__ . ': You have to specify a container either by telling me its name or specifying globally calling setActiveContainerName');
      }
      else
      {
        $container = $this->_container[$this->_activeContainerName];
      }
    }
    else
    {
      if(!isset($this->_container[$name]))
      {
        $container = $this->_connection->get_container($name); //throws exception if doesnt exist
      }
      else
      {
        $container = $this->_container[$name];
      }
    }
    return $container;
  }

  private function _removeContainer($name = null)
  {
    if(is_null($name))
    {
      if(is_null($this->_activeContainerName))
      {
        throw new Exception(__FUNCTION__ . ': You have to specify a container either by telling me its name or specifying globally calling setActiveContainerName');
      }
      else
      {
        $this->_activeContainerName = null;
      }
    }
    else
    {
      if(isset($this->_container[$name]))
      {
        unset($this->_container[$name]);
      }
    }
  }
}
