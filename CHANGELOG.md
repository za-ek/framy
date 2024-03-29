### 3.1.0 -> 3.2.0
* Create superclass RouteGroup
* Create RouteProxy
* RoutePrefix is a subclass of RouteGroup
* Routes REST is obsolete
* Minimal PHP version is 8.1

### 3.0.2 -> 3.1.0
* add RoutePrefix class for group URIs
* fix multiple methods in dynamic routes
* set conf value (e.g. in route)
* get arguments list for CLI request

### 2.0.1 -> 3.0.2
* Remove Controller class -> moving into App
* Static file
* addRoutes method
* Router prepares Action for further call. See Router::convertToAction
* Json action returns JSON array
* confDefined method for checking if key exists
* Add void action

### 2.0.0-alpha.1 &rarr; 2.0.1
* WEB requests
* Wildcard route
* Simulate HTTP-methods (like GET, PUT, etc.) in cli
* Remove php-like arrays _get and _post, add real container _body
* Add interfaces for request 

### 2.0.0-alpha &rarr; 2.0.0-alpha.1
* Get console arguments in Request\Cli class 

### v1.0.6 &rarr; 2.0.0-alpha

#### <a href="https://github.com/za-ek/framy/commit/9157551c8049fc623721164e941392ce1e45df50">[9157551c]</a>
* Start using <a href="https://semver.org" target="_blank">semantic versioning</a>
* Add changelog
* Now request creating only available from Request object not from plain method and uri
* Add support for GET variables in request string
* Add support for parse command line arguments