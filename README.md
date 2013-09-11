# CM Framework


## Major concepts

### Namespace
A namespace groups related code. The namespace is used as a prefix for classnames. `CM` is a namespace, a classname could be `CM_Foo_BarZoo`.

### Site
One application can serve multiple *sites* (extending `CM_Site_Abstract`).

Each HTTP-request (`CM_Request_Abstract`) is matched against the available sites (`::match()`), before it is processed.

A site contains multiple *Namespaces* (for models and controllers) and *themes* (for views).

### View
A view (extending `CM_View_Abstract`) can be rendered, usually as HTML. The following view types are pre-defined:
* `CM_Layout_Abstract`: HTML-document.
* `CM_Page_Abstract`: A page of the web application. Is actually a component itself.
* `CM_Component_Abstract`: Sub-part of an HTML-document.
* `CM_Form_Abstract`: Form with input elements and actions.
* `CM_FormField_Abstract`: Form input field.
* `CM_Mail`: E-mail.

### Model
A model (extending `CM_Model_Abstract`) represents a "real-world" object, reads and writes data from a key-value store and provides functionality on top of that.

Every model is identified by an *id*, with which in can be instantiated:
```php
$foo = new Foo(123);
```
By default the constructor expects an integer value for the ID.
Internally it is stored as a key-value store (array), which can be exposed if there's need for more complex model-identification.

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
Fields with a model's class name as type will be converted forth and back between *object* and *json representation* of its ID when reading and writing from the data store.

#### Persisting data
If present the *persistence* storage adapter will be used to load and save a model's data.
```php
	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
```
In this example the database adapter's `load()` and `save()` methods will be called whenever you instantiate an existing model or update schema fields.

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

By default the data is cached between multiple reads in Memcache. Use `getCacheClass()` to change this behaviour:
```php
	public static function getCacheClass() {
		return 'CM_Model_StorageAdapter_CacheLocal';
	}
```

Alternatively if you're not using a *persistence* storage adapter, you can implement a custom method `_loadData()` which should return an array of the model's key-value data.
In this case your setters are responsible for persistence.

#### Creating and deleting
Models with a *persistence* storage adapter can be created by using their setters plus `commit()`:
```php
	$foo = new Foo();
	$foo->setFieldA(23);
	$foo->setFieldB('bar');
	$foo->commit();
```

For deleting models just call:
```php
	$foo = new Foo(123);
	$foo->delete();
```

Alternatively if you're not using a *persistence* storage adapter, you can implement your own creation logic in `_createStatic(array $data)` and then create models with:
```php
	$foo = Foo::createStatic(array('fieldA' => 1, 'fieldB' => 'hello world'));
```
In this case make sure to delete the corresponding records within `_onDelete()` (see below).

#### Event handling
The following methods will be called for different events in the lifetime of a model:
* `_onCreate()`: After persistence, when a model was created.
* `_onChange()`: After persistence, when a field's value was changed. Also after the model was created.
* `_onDelete()`: Before persistence, when a model is deleted.

### Paging
A paging is an ordered collection with pagination-capabilities.

The data source for a paging is a PagingSource (`CM_PagingSource_Sql`, `CM_PagingSource_Search` etc.).
Caching can be enabled optionally with `enableCache()`.

Items within a paging can be post-processed before being returned. For example one can instantiate an object for the id returned from the database.

Naming convention:
```php
CM_Paging_<Type of item>_<Lookup description>
CM_Paging_Photo_User                           # All photos of a given user
CM_Paging_User_Country                         # All users from a given country
```


## Creating a new project

### Cloning the CM skeleton application

In your workspace, run:
```bash
composer create-project cargomedia/CM-project --repository-url="http://satis.cargomedia.ch/source" --stability=dev <project-name>
```
This will create a new directory `<project-name>` containing a project based on CM.

### Setting up a virtual host

The only entry point of your application should be `public/index.php`.
A typical Apache virtual host configuration for this purpose were:

```conf
<VirtualHost *>
  ServerName ‹hostname›
  RedirectPermanent / http://www.‹hostname›/
</VirtualHost>

<VirtualHost *>
  ServerName www.‹hostname›
  DocumentRoot ‹project-dir›

  <Directory ‹project-dir›/>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ public/$1
  </Directory>

  <Directory ‹project-dir›/public/>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php
    RewriteRule .* - [E=HTTP_X_REQUESTED_WITH:%{HTTP:X-Requested-With}]
  </Directory>
</VirtualHost>
```

### Project configuration

In your project directory, run:
```bash
./scripts/cm.php app generate-config
```

### Namespace creation, site setup

CM framework provides a base which should be extended. Our own libraries should be part of different namespace. To create one simply run:
```bash
./scripts/cm.php generator create-namespace <namespace>
```
Once completed you need to manually adjust entry points (`public/index.php`, `scripts/cm.php`). Replace current `CM_Bootloader` usage with `<namespace>_Bootloader`.

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
To see full list of available commands simply execute `./scripts/cm.php`.

```
Usage:
 [options] <command> [arguments]

Options:
 --quiet
 --quiet-warnings
 --non-interactive

Commands:
 app generate-config
 app fill-cache
 console interactive
 css icon-refresh
 css emoticon-refresh
 db dump <namespace>
 db run-updates
 db run-update <version> [--namespace=<value>]
 generator create-view <class-name>
 generator create-class <class-name>
 generator create-namespace <namespace>
 generator create-javascript-files
 job-distribution start-manager
 maintenance common
 maintenance heavy
 search-index create [--index-name=<value>]
 search-index update [--index-name=<value>] [--host=<Elasticsearch host>] [--port=<Elasticsearch port>]
 search-index optimize
 stream start-message-synchronization
 entertainment start-processing [--interval=<value>]
```
