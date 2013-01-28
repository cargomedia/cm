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
```
CM_Paging_<Type of item>_<Lookup description>
CM_Paging_Photo_User                           # All photos of a given user
CM_Paging_User_Country                         # All users from a given country
```


## Creating a new project
```
composer create-project cargomedia/CM-project --repository-url="http://satis.cargomedia.ch" <project-name>
```

Change into your newly created project and install the dependencies:
```
cd <project-name>
composer install
```
