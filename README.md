# siflawler-php
A simple, flexible crawler, written in PHP. Do you want data from a website?
This project may be just the tool you need. Do you want to crawl multiple pages,
retrieving data from all those pages? We all love pagination, right? This tool
can handle it. Do you hate writing code that traverses DOM trees in PHP, or do
you not know how to do that? No problem. You only need to write a JSON
configuration file to let siflawler know what to look for and where and you are
good to go.


  1.  [Usage](#usage)
  2.  [But what do I configure?](#but-what-do-i-configure)
      1.  [Mandatory options](#mandatory-options)
      2.  [Optional options](#optional-options)
  3.  [Running tests](#running-tests)
  4.  [License](#license)


## Usage
There are a couple of steps to go through.

  1.  Download or clone siflawler.

  1.  Create your own PHP file, based on the test files in the repository. Really,
      you only need to include the `siflawler.php` class loader, that will be all.

  1.  Write a JSON configuration file *(TODO: I want to make it possible to pass a
      string or object with configuration)* and pass its filename to the `Crawler`
      constructor as follows:

      ```php
      $crawler = new \siflawler\Crawler('/path/to/config.json');
      ```
  1.  Start crawling.

      ```php
      $data = $crawler->crawl();
      ```

You may like to do crawling from the commandline. In that case, just run your file
as follows. siflawler will give some output letting you know what it is doing in
case you have set the `verbose` option to `true` (which it is by default).

```
$ php -f your-file.php
```


## But what do I configure?
Configuration? JSON? What? How? Like this. *TODO: I want to support CSS selectors as
queries, possibly extended somehow to retrieve text or attribute values.*


### Mandatory options
The following options are mandatory. siflawler will throw an exception if you forget
to pass one of these to it. It simply needs to know what to do.

```json
{
    "start": "some url you want to start crawling at",
    "find": "some XPath query to select interesting elements",
    "get": {
        "key-1": "an XPath query to select some value within a found element",
        "key-2": "another XPath query, et cetera"
    }
}
```

The `start` option is simply the URL siflawler will look (first) for an HTML page to
crawl and get data from.

The `find` option can be used to specify how siflawler should locate interesting
elements on a page when it has been retrieved. For each interesting element, an
object will be created (`stdClass`) that has the properties specified in the `get`
option. Each key in the `get` option object should be a query indicating what to
put in that key in the resulting `stdClass` object.

If you want to crawl multiple pages, use the `next` option (see next section).


### Optional options
You can use the following optional options, which are all self-explanatory really.
The below values are the default values, you only need to include options if you
want to use a different value or want to be explicit.

```json
{
    "max_requests": 0,
    "next": null,
    "timeout": 0,
    "verbose": true,
    "warnings": true
}
```

The `max_requests` option can be used to limit the number of pages siflawler will
request in total. A value of 0 or less means that there is no limit.

The `next` option can be used to find one (*TODO: or more*) URLs to crawl next. If
this is `null`, then no next page will be crawled, but you can specify an XPath
query to find one or more locations to go next.

The `timeout` option can be used to specify a timeout in seconds for each request.
A value of 0 means that there will be no timeout.

The `verbose` and `warnings` options can be used to toggle siflawler output.


## Running tests
This project includes [phpunit](https://phpunit.de/) as a
[submodule](http://git-scm.com/book/en/v2/Git-Tools-Submodules). This means that
if you want to run tests, you will need to initialise and update that submodule
as follows:

```
$ cd /path/to/siflawler-php/
$ git submodule update --init --remote
Submodule 'phpunit' (git@github.com:sebastianbergmann/phpunit.git) registered for path 'lib/phpunit'
Submodule path 'lib/phpunit': checked out 'e90575c2bb86290d57a262862dab1da125431576'
```

This should check out the latest commit in the stable branch, currently `4.4`.

To set up PHPUnit, you will need to execute a few more commands only once. This
will also be output by PHPUnit if you run it without doing these steps.

```
$ cd /path/to/siflawler-php/lib/phpunit/
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
```

If you then want to run tests, you can do so as follows:

```
$ cd /path/to/siflawler-php/tests/
$ ../lib/phpunit/phpunit
```


## License
siflawler - a simple, flexible crawler, written in PHP.

Copyright (C) 2015  Thom Castermans

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
