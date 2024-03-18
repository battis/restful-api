# PHPUnit Sessions

[![codecov](https://codecov.io/gh/battis/phpunit-sessions/branch/main/graph/badge.svg?token=ABK4AJLYO0)](https://codecov.io/gh/battis/phpunit-sessions)

Extension to PHPUnit to handle PHP sessions more gracefully

## Install

```bash
composer require --dev battis/phpunit-sessions
```

## Configure

In `phpunit.xml`:

```xml
<phpunit

    ...

    bootstrap="tests/bootstrap.php"
>

  ...

  <extensions>
      <extension class="Battis\PHPUnit\Sessions\Extension" />
  </extensions>
</phpunit>
```

In `tests/bootstrap.php`:

```php
require_once __DIR__ . "/../vendor/autoload.php";

Battis\PHPUnit\Sessions\Bootstrap::execute();
```

## How it works

Fundamentally, there are two problems with working with sessions and cookies when testing in PHP:

1. PHPUnit runs from the CLI, so `$_COOKIES` and `$_SESSIONS` don't exist.
2. PHPUnit starts output before any of the code under test that works with sessions is executed, generating errors about output being sent before headers, etc.

To address this, this extension enables an output buffer as PHPUnit starts, buffering all of the output until after the last test is run. (You lose nothing but the immediacy of the output -- you'll still see it all, but only after a heart-stopping pause.)

In addition, when the script run from the CLI, `$_SESSIONS` AND `$_COOKIES` are initialized as empty arrays which can then be manipulated as usual.
