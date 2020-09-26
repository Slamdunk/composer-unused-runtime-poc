# composer-unused-runtime Proof-of-Concept

## Are all your production dependencies truly necessary?

[`composer-unused`](https://github.com/composer-unused/composer-unused) is a needful tool nowadays:
it looks at your `composer.json/"require"` section and checks that every declared production
dependency is used somewhere, in your code; if it doesn't it suggests to remove it.

That's great. But a declared dependency can bring along a ton of other dependencies.

Are those **child** dependencies necessary? Do we use them?

1. If our prod doesn't use them, it would be better to avoid installing them altogether:
the only code without bugs is in the neverland
1. If our prod uses them, we should test them!  

## The idea

If we have 100% Code-Coverage, a full run of our testing framework will populate
[`get_included_files()`](https://www.php.net/manual/en/function.get-included-files.php) with each and
every dependency we use.

Diffing `get_included_files()` output with the list of `composer install --no-dev` installed packages
can show the child dependencies that we can prevent altogether to be installed in production, thanks to the
[`replace`](https://getcomposer.org/doc/04-schema.md#replace) section of `composer.json`.

## The example

This package calculates the `crc32` checksum of any variable, expect closures, because it's silly.

Since we also are lazy, we leverage [`brick/varexporter`](https://github.com/brick/varexporter) for our
internal operation.

The [test](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/tests/VarHasherTest.php)
is simple, and so is the [code](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/src/VarHasher.php),
reaching 100% CC is trivial:

```
$ vendor/bin/phpunit --coverage-text
PHPUnit 9.3.11 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:00.026, Memory: 10.00 MB

OK (6 tests, 6 assertions)


Code Coverage Report:   
  2020-09-26 12:22:09   
                        
 Summary:               
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (1/1)
```

Let's now list all the installed dependencies in production:

```
$ composer install --no-dev

$ composer show --format=json | jq --raw-output '.installed[].name' > .installed.txt

$ cat .installed.txt
brick/varexporter
nikic/php-parser
```

`brick/varexporter` is ok, [we required it in the composer.json](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/composer.json#L7).

But do we need `nikic/php-parser`?

We can register a shutdown function in the test suite to dump to a
[file](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/.composer-used-runtime.php)
the `get_included_files()` output, see
[`tests/bootstrap.php`](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/tests/bootstrap.php),
and a [simple diff](https://github.com/Slamdunk/composer-unused-runtime-poc/blob/master/composer-unused-runtime.php)
checks everything out:

```
$ php composer-unused-runtime.php 
The following packages will be installed in production, but are never used during the test suite:

Array
(
    [1] => nikic/php-parser
)
```

Ok, so now we can strip the dependency out of composer.json:

```diff
         "php": "^7.4",
         "brick/varexporter": "^0.3"
     },
+    "replace": {
+        "nikic/php-parser": "*"
+    },
     "require-dev": {
         "phpunit/phpunit": "^9.3"
     },
```

Update the composer.lock and re-run the tests:

```
$ composer update
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 0 installs, 0 updates, 1 removal
  - Removing nikic/php-parser (v4.10.2)
Writing lock file
Generating autoload files

$ vendor/bin/phpunit
PHPUnit 9.3.11 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:00.007, Memory: 6.00 MB

OK (6 tests, 6 assertions)
```

No more unused child dependency will lend in production from now on :rocket: