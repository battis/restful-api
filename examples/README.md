# Example App

Right now, these are really just notes so that _I_ can reuse my own code. Better docs comming some day.

  - `.env:ROOT_PATH` needs to be set to the path from the server to the app (e.g. `/foo/bar` to serve the app API from `https://example.com/foo/bar/api/v1`)
  - `.env:DB_*` needs to be set to valid credentials, etc.
  - Load schema into database (cool to set up something like Phinx for this -- right now it's just raw SQL queries)
  - Define base classes for objects and users (`ExampleObject`, `ExampleUser`). The base object class should set the `$USER_BINDING` to the user class so that authentication works properly. Note that unless `ExampleUser::name()` or `ExampleUser::namePlural()` are overridden, the user will be stored in a table named `exampleusers`.
  - Develop your object model (e.g. `\Example\Model\Widget`). Define fields and setters and getters (need to align with schema field names). Note that setters and getters need to be public or protected (private will keep the superclass from accessing them). Some examples for different strategies (e.g. a field that might be null, a field that shouldn't be modified outside the model, etc.) are given in `Widget`.
  - Define routes (note handy helpers in `Auth.php` and `Users.php`)
  - Update `api.config.php` (enjoy the handy middleware already defined for authentication and CORS):
    1. Call `JWTOperations::bindObjectType()` to bind to your base object type, which will then cause the appropriate base user object type to be used when authenticating requests.
    2. Include your route definitions in `API::_ROUTES`.
 - TODO script creation of first user
 - TODO script creation of app template