# siflawler-php
A simple, flexible crawler, written in PHP.

This little project is [easily installable](#usage) and enables you to

  - easily retrieve data from an HTML or XML file, both local and remote;
  - crawl multiple pages efficiently (can handle pagination);
  - not worry about DOM traversal at all.

Interesting features on a page can be found through XPath queries. On top of
that, siflawler supports basic CSS selectors with an [extension](#querying)
enabling the retrieval of attributes. This way, querying a page is easy even if
you do not know XPath.


  1.  [Dependencies](#dependencies)
  1.  [Usage](#usage)
  1.  [Configuration](#configuration)
      1.  [Mandatory options](#mandatory-options)
      1.  [Optional options](#optional-options)
      1.  [Querying](#querying)
  1.  [Running tests](#running-tests)
  1.  [Contributing](#contributing)
  1.  [License](#license)

## Dependencies
To be able to run siflawler, you will need to have the [PHP cURL
extension](http://php.net/manual/en/book.curl.php) installed. This is what
siflawler uses to download pages from the website(s) you want to crawl. This
does enable siflawler to download pages in parallel, amongst others. Please open
an issue if you have a problem with this and would like to see if bare PHP
`file_get_contents` support can be added.

## Usage
You can install siflawler using [Composer](https://getcomposer.org/). Either run
`composer require 'caster/siflawler:~1.2.1'` or put the following in your
`composer.json` and run `composer install`.

```json
{
    "require": {
        "caster/siflawler": "~1.2.1"
    }
}
```

You are now set up to start crawling.

```php
$config = // JSON string, path to JSON file, object, or associative array
$crawler = new \siflawler\Crawler($config);
$data = $crawler->crawl();
```

You may like to do crawling from the commandline. In that case, just run your file
as follows. siflawler will give some output letting you know what it is doing in
case you have set the `verbose` option to `true` (which it is by default).

```
$ php -f your-file.php
```


## Configuration
These are the options you can pass the `\siflawler\Crawler` when constructing
it.


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
`null`, then no next page will be crawled, but you can specify a
[query](#querying) to find one or more locations to go to next. This is useful
when you want to crawl data that is split over multiple pages using pagination.

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
support is pretty good and can always be improved if you create an issue 🙂

To distinguish between CSS and XPath, siflawler uses a heuristic. If you want to
be sure that this does not go wrong, you can specify a query as
`css:[your CSS selector]` or `xpath:[your XPath query]`, to let siflawler know
precisely what the query language is you use.


## Running tests
This project uses [phpunit](https://phpunit.de/) for automated unit testing. You
can easily run the tests by executing `composer test`. For that to work, you do
need to install the dev version of siflawler.


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
