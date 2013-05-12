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
A model (extending `CM_Model_Abstract`) represents a "real-world" object, stores state persistently and provides functionality.

Every model is identified by an *id*. The default constructor implements an integer-id.
Internally the id is stored as a key-value structure (array), which can be exposed if there's need for more complex model-identification.

All loaded data (implement `_loadData()`) is accessible as a key-value store with `_get()` and `_set()`.
The key-value store is cached with Memcache by default.

Model lifecycle uses the methods `create()` and `delete()`.

To create a fully-functional model implement the following:
* `_create()`: Create the model, return an instance
* `_loadData()`: Return key-value store as array, or FALSE on error
* `_onDelete()`: Delete the model
* Getters and Setters: Can use the internal `_get()` and `_set()` to access the key-value store. Setters should call `_change()` to invalidate caches.

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
composer create-project cargomedia/CM-project --repository-url="http://satis.cargomedia.ch/source" <project-name>
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
./scripts/cm.php config generate
```

### Namespace creation, site setup

CM framework provides a base which should be extended. Our own libraries should be part of different namespace. To create one simply run:
```bash
./scripts/cm.php generator create-namespace <namespace>
```
Once completed you need to manually adjust entry points (`public/index.php`, `scripts/cm.php`). Replace current `CM_Bootloader` usage with `<namespace>_Bootloader` and add following line before it:
```php
require_once dirname(__DIR__) . '/library/<namespace>/library/<namespace>/Bootloader.php';
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
To see full list of available commands simply execute `./scripts/cm.php`.

```
Usage:
 [options] <command> [arguments]

Options:
 --quiet
 --non-interactive

Commands:
 config generate
 generator create-view <class-name>
 generator create-class <class-name>
 generator create-namespace <namespace>
 generator create-javascript-files
 job-distribution start-manager
 db dump <namespace>
 db run-updates
 db run-update <version> [--namespace=<value>]
 search-index create [--index-name=<value>]
 search-index update [--index-name=<value>] [--host=<Elasticsearch host>] [--port=<Elasticsearch port>]
 search-index optimize
```
