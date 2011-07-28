# mmRackspaceFilePlugin


symfony 1.4/1.3/1.2 plugin to easily maintain files hosted in Rackspace Cloud File CDN.

## Purpose

Using this plugin you will be able to maintain your Rackspace Cloud files (images, javascripts, 
stylesheets, etc) easily without having to interact directly with the Rackspace API.

## How to install

Download this plugin into your /plugins dir
Using `git`, the standard way:

    $ cd /path/to/symfony/project
    $ git clone git://github.com/pedrobc/mmRackspaceFilePlugin.git

If your project already uses `git`, you can add the plugin as one of its submodule:

    $ cd /path/to/symfony/project
    $ git submodule add git://github.com/pedrobc/mmRackspaceFilePlugin.git plugins/mmRackspaceFilePlugin
    $ git submodule update --init --recursive
    $ git commit -a -m "added mmRackspaceFilePlugin submodule"

and activate it in your ProjectConfiguration.class.php (for example).

    $this->enablePlugins(…, 'mmRackspaceFilePlugin');


## Simple example

Configure your Rackspace File account details in app.yml

    all:
      rackspace:
        account: /v1/CF_xer7_34
        username: my_user_name
        api_key: my_api_key
        is_uk: false
    
      
Adding a file to Rackspace file :

    //Create the plugin instance
    $rackspace = new mmRackspaceFile();

    //Defining a container name to use: images 
    $containerName = 'images';

    //Add (create) the new container
    $container = $rackspace->addContainer($name);

    // OR use an existing container, if it already exists
    $container = $rackspace->getContainer($name);

    //if you want to make your container public (CDN), do:
    $rackspace->publishContainer($name);

    //Define the local file path to be added to Rackspace File
    $fileFullPath = '/path/to/your/file/file_name.png';

    //Add local file to Rackspace file container
    $rackspace->addObjectToContainer($fileFullPath, $name);

    //To display your file
    $rackspace->getFilePublicUri('file_name.png', $name)

## More

See the tests files for further details.


