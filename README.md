# siflawler-php
A simple, flexible crawler, written in PHP. Do you want data from a website?
This project may be just the tool you need. Do you want to crawl multiple pages,
retrieving data from all those pages? We all love pagination, right? This tool
can handle it. Do you hate writing code that traverses DOM trees in PHP, or do
you not know how to do that? No problem. You only need to write a JSON
configuration file (or pass that information as a `\stdClass` object or
associative array) to let siflawler know what to look for and where and you are
good to go.


  1.  [Usage](#usage)
  1.  [But what do I configure?](#but-what-do-i-configure)
      1.  [Mandatory options](#mandatory-options)
      1.  [Optional options](#optional-options)
      1.  [Querying](#querying)
  1.  [Running tests](#running-tests)
  1.  [Contributing](#contributing)
  1.  [License](#license)


## Usage
There are a couple of steps to go through.

  1.  Download or clone siflawler.

  1.  Create your own PHP file, based on the test files in the repository. Really,
      you only need to include the `siflawler.php` class loader, that will be all.

  1.  Write a JSON configuration file and pass its filename to the `Crawler`
      constructor as follows:

      ```php
      $crawler = new \siflawler\Crawler('/path/to/config.json');
      ```

      Alternatively, you can pass the JSON as a string, you can pass a `\stdClass`
      object or you can even pass an associative array. Whatever you prefer,
      siflawler will (try to) understand it.

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
Configuration? JSON? What? How? Like this.


### Mandatory options
The following options are mandatory and siflawler will throw an exception if you
forget to pass one of these to it. It simply needs to know what to do. To see
what you can pass for the selectors/queries, refer to the [Querying](#querying)
section.

```json
{
    "start": "some url you want to start crawling at",
    "find": "some CSS selector or XPath query to select interesting elements",
    "get": {
        "key-1": "a CSS selector or an XPath query to select some value within a found element",
        "key-2": "another XPath query, et cetera"
    }
}
```

The `start` option is simply the URL siflawler will look (first) for an HTML page to
crawl and get data from. This may also be an absolute path to some file on your
local disk. It can even be an array of URLs, paths, or a mix of those two.

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

The `next` option can be used to find one or more URLs to crawl next. If this is
`null`, then no next page will be crawled, but you can specify an XPath query to
find one or more locations to go next.

The `timeout` option can be used to specify a timeout in seconds for each request.
A value of 0 means that there will be no timeout.

The `verbose` and `warnings` options can be used to toggle siflawler output.


### Querying
Everywhere you can specify a query to find elements or attributes of elements,
you can do two things: either specify an XPath query, or specify a CSS selector.
CSS can normally only select nodes, but siflawler can understand some additional
syntax that will allow you to select attribute values. Examples are:

```css
a.nav$attr("href")
p#some-id$text()
```

Internally, siflawler will translate CSS selectors to XPath queries. If you want
to be sure that this cannot go wrong, you should use XPath, but siflawler's CSS
support is pretty good and can always be improved if you create an issue :)

To distinguish between CSS and XPath, siflawler uses a heuristic. If you want to
be sure that this does not go wrong, you can specify a query as
`css:[your CSS selector]` or `xpath:[your XPath query]`, to let siflawler know
precisely what the query language is you use.


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


## Contributing
If you miss something in siflawler, found a problem or if you have something
really cool to add to it, feel free to open an
[issue](https://github.com/Caster/siflawler-php/issues) or
[pull request](https://github.com/Caster/siflawler-php/pulls) on GitHub. I will
try to respond as quickly as possible.


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
