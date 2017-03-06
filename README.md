CM Framework  [![Build Status](https://travis-ci.org/cargomedia/cm.png)](https://travis-ci.org/cargomedia/cm)
============

Major concepts
--------------

### Namespace
A namespace groups related code. The namespace is used as a prefix for classnames. `CM` is a namespace, a classname could be `CM_Foo_BarZoo`.

### Site
One application can serve multiple *sites* (extending `CM_Site_Abstract`).

Each HTTP-request (`CM_Http_Request_Abstract`) is matched against the available sites (`::match()`), before it is processed.

A site contains multiple *Namespaces* (for models and controllers) and *themes* (for views).

### View
A view (extending `CM_View_Abstract`) can be rendered, usually as HTML. The following view types are pre-defined:
* `CM_Layout_Abstract`: HTML-document.
* `CM_Page_Abstract`: A page of the web application. Is actually a component itself.
* `CM_Component_Abstract`: Sub-part of an HTML-document.
* `CM_Form_Abstract`: Form with input elements and actions.
* `CM_FormField_Abstract`: Form input field.
* `CM_Mail_Mailable`: E-mail.

### Model
A model (extending `CM_Model_Abstract`) represents a "real-world" object, reads and writes data from a key-value store and provides functionality on top of that.

Every model is identified by an *id*, with which in can be instantiated:
```php
$foo = new Foo(123);
```
By default, the constructor expects an integer value for the ID.
Internally, it is stored as a key-value store (array), which can be exposed if there's need for a more complex model identification.

#### Schema
To validate and enforce type casting of your models' fields, define an appropriate schema definition:
```php
	protected function _getSchema() {
		return new CM_Model_Schema_Definition(array(
			'fieldA' => array('type' => 'int'),
			'fieldB' => array('type' => 'string', 'optional' => true),
			'fieldC' => array('type' => 'CM_Model_Example'),
		));
	}
```
Fields with a model's class name as a type will be converted forth and back between an *object* and a *json representation of its ID* when reading and writing from the data store.

#### Persisting data
If present, the *persistence* storage adapter will be used to load and save a model's data.
```php
	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
```
In this example, the database adapter's `load()` and `save()` methods will be called whenever you instantiate an existing model or update schema fields.

The `CM_Model_StorageAdapter_Database` will make use of following naming conventions to persist models in a database:
- Table name: Lower-case class name of the model
- Column names: Name of the schema fields

You can access a model's fields with `_get()` and `_set()`, which will consider the schema for type coercion.
It is recommended to implement a pair of getter and setter for each field in order to keep field names internal:
```php
 	/**
 	 * @return string|null
 	 */
 	public function getFieldB() {
 		return $this->_get('fieldB');
 	}

 	/**
 	 * @param string|null $fieldB
 	 */
 	public function setFieldB($fieldB) {
 		$this->_set('fieldB', $fieldB);
 	}

 	/**
 	 * @return CM_Model_Example
 	 */
 	public function getFieldC() {
 		return $this->_get('fieldC');
 	}

 	/**
 	 * @param CM_Model_Example $fieldC
 	 */
 	public function setFieldC($fieldC) {
 		$this->_set('fieldC', $fieldC);
 	}
```

By default, the data is cached between multiple reads in Memcache. Use `getCacheClass()` to change this behaviour:
```php
	public static function getCacheClass() {
		return 'CM_Model_StorageAdapter_CacheLocal';
	}
```

Alternatively if you're not using a *persistence* storage adapter, you can implement a custom method `_loadData()` which should return an array of the model's key-value data.
In this case, your setters are responsible for persistence and should still call `_set()` for caching.

#### Creating and deleting
Models with a *persistence* storage adapter can be created by using their setters followed by a `commit()`:
```php
	$foo = new Foo();
	$foo->setFieldA(23);
	$foo->setFieldB('bar');
	$foo->commit();
```

For deleting models, just call:
```php
	$foo = new Foo(123);
	$foo->delete();
```

Alternatively, if you're not using a *persistence* storage adapter, you can implement your own creation logic in `_createStatic(array $data)` and then create models with:
```php
	$foo = Foo::createStatic(array('fieldA' => 1, 'fieldB' => 'hello world'));
```
In this case, make sure to delete the corresponding records within `_onDelete()` (see below).

#### Event handling
The following methods will be called for different events in the lifetime of a model:
* `_onCreate()`: After persistence, when a model was created.
* `_onChange()`: After persistence, when a field's value was changed. Also after the model was created.
* `_onDelete()`: Before persistence, when a model is deleted.

### Paging
A paging is an ordered collection with pagination-capabilities.

The data source for a paging is a PagingSource (`CM_PagingSource_Sql`, `CM_PagingSource_Elasticsearch` etc.).
Caching can be enabled optionally with `enableCache()`.

Items within a paging can be post-processed before being returned. For example one can instantiate an object for the id returned from the database.

Naming convention:
```php
CM_Paging_<Type of item>_<Lookup description>
CM_Paging_Photo_User                           # All photos of a given user
CM_Paging_User_Country                         # All users from a given country
```

Structuring files
-----------------
Class definitions should be grouped by topic and stored in a common directory.
Within such a *topic module* directories should be used to group files with common parent classes.
A *topic module* can again contain a directory for another (sub) topic module.

Example of a topic module "Payments", inside another topic module "Accounting":
```
library/
└── CM
    └── Payments
        ├── Accounting
        │   ├── Account.php
        │   ├── Transaction.php
        │   └── TransactionList
        │       ├── Abstract.php
        │       └── User.php
        ├── Bank.php
        ├── BankList
        │   ├── Abstract.php
        │   ├── All.php
        │   └── PaymentProvider.php
        ├── ExchangeRateUpdater.php
        └── SetupScript.php
```

Views like *components* and *pages* need to reside in their respective directory.
It's recommended to group them by topic within a sub-directory.

Example of *components* for the topic "Payments":
```
library/
└── CM
    └── Component
        └── Payments
            ├── AccountList.js
            ├── AccountList.php
            ├── TransactionList.js
            └── TransactionList.php
```

Creating a new project
----------------------

### Cloning the CM skeleton application

In your workspace, run:
```bash
composer create-project cargomedia/cm-project --stability=dev <project-name>
```
This will create a new directory `<project-name>` containing a project based on CM.

### Namespace creation, site setup

CM framework provides a base which should be extended. Our own libraries should be part of different namespace. To create one simply run:
```bash
bin/cm generator create-namespace <namespace>
```

### Adding new modules
To simplify creation of common framework modules, but also to help understanding of its structure there is a generator tool. It helps with scaffolding framework views and simple classes. It also allows easy addition of new namespace or site.

```bash
generator create-view <class-name>
```
Creates new view based on the <class-name> provided. It will create php class, javascript class, empty html template and less file. It will also look for most appropriate abstract class to extend.

```bash
generator create-class <class-name>
```
Creates new <class-name> class.

## Command line tools

CM framework comes with its own set of command line tools to easily run common php routines.
To see full list of available commands simply execute `bin/cm`.

```
Usage:
 [options] <command> [arguments]

Options:
 --quiet
 --quiet-warnings
 --non-interactive
 --forks=<value>

Commands:
 app fill-caches
 app generate-config-internal
 app set-config <filename> <config-json> [--merge]
 app setup [--reload]
 cache clear
 console interactive
 db db-to-file <namespace>
 db file-to-db
 frontend generate-favicon
 frontend icon-refresh
 generator bootstrap-project [--project-name=<value>] [--domain=<value>] [--module-name=<value>]
 generator create-class <class-name>
 generator create-module <module-name> [--single-module-structure] [--module-path=<value>]
 generator create-site <class-name> <name> <domain>
 generator create-view <class-name>
 job-distribution start-worker
 location outdated [--verbose]
 location upgrade [--without-ip-blocks] [--verbose]
 maintenance start
 media-streams import-archive <stream-channel-media-id> <archive-source>
 media-streams import-video-thumbnail <stream-channel-media-id> <thumbnail-source> <create-stamp>
 message-stream start-synchronization
 migration add [--namespace=<value>] [--name=<value>]
 migration run [--name=<value>]
 search-index create [--index-name=<value>] [--skip-if-exist]
 search-index delete [--index-name=<value>]
 search-index optimize
 search-index update [--index-name=<value>]
 ```

Deployment
----------

Apart from setting whole infrastructure (http server, various services) application itself needs some preparation.

### Class types
Each CM application heavily depends on types which are integer identifiers for classes. In order to keep fixed identifier (class name can change) we require to store those in VCS-stored config file (internal.php).
In order to generate types run:
```bash
$ bin/cm app generate-config-internal
```
This will generate `resources/config/internal.php` and `resources/config/js/internal.js` files required for application to work. Keep this file in VCS at it needs to be preserved between releases.


### Provision scripts
Most CM applications require services to be setup and/or some initial data inserted. To do so CM Framework uses so-called provision scripts.
There is built-in command for running all setup-scripts defined in `$config->CM_App->setupScriptClasses` config property.

```bash
$ bin/cm app setup
```

Provision scripts are responsible for setting up everything app-related - from creating database schema to loading fixtures.

#### Provision script
All those scripts need to be classes extending `CM_Provision_Script_Abstract` and therefore implement `load` and `shouldBeLoaded` method.
Anytime scripts are about to be loaded they will be first checked if they actually should, by running `shouldBeLoaded`.
Once this is positive it will run `load`.

Additionally provision scripts can implement `CM_Provision_Script_UnloadableInterface` with corresponding `unload` and `shouldBeUnloaded` methods.

### Migration scripts
Migration scripts are located in `[modulePath]/resources/migration` directories, they are executed in module 
registration order (see `extra.cm-modules` in `composer.json`), then by script filename natural order.

- script classes are [required][php-require-once] and instantiated on the fly
- script classes name must be unique
  - by convention, a timestamp is added in the class name to avoid conflicts
- script classes must implement the `CM_Migration_UpgradableInterface` interface
- script may optionally implement `CM_Service_ManagerAwareInterface` to gain access to the service manager
- the `UpgradableInterface::up` PHP documentation block will be displayed during the script execution if available


#### Execute migration scripts
- `cm migration run`   
  run all scripts not successfully executed yet
- `cm migration run --name=<filename>`     
  (re)run a specific script, by its filename without extension

ie:
```
bin/cm migration run
- 1485180420_Foo…
- 1485180453_Bar: some description coming from PHP doc…
```

#### Generate a migration script
- `cm migration add`   
  generate a migration script, by default in `[root]/resources/migration` and with the current git branch name
- `cm migration add --namespace=<module-name> --name=<script-name>`    
  generate a migration script in a specific module / with a specific name

ie:
```
bin/cm migration add --namespace=Foo --name=Bar
`/home/vagrant/cm/library/Foo/resources/migration/1485180453_Bar.php` generated
```
  
  
  [php-require-once]: http://php.net/manual/en/function.require-once.php
