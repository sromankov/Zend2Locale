Zend2Locale (SlmLocale fork)
===

Created by Jurian Sluiman
Forked by Sergii Romankov

Introduction
------------
SlmLocale (Zend2Locale) is a Zend Framework 2 module to automatically detect a locale for your
application. It uses a variety of pluggable strategies to search for a valid
locale. SlmLocale features a default locale, a set of supported locales and
locale aliases.
...
An additional information is here https://github.com/juriansluiman/SlmLocale

This is a forked version of the Jurian Sluiman's module SlmLocale.

Changes list:
 1. An additional module setup parameter assembleDefault (default value is true) that allows
 to include/exclude  locale part from the URI for the UriPathStrategy strategy

Installation
---
Modify your composer.json file and update your dependencies. Enable
SlmLocale in your `application.config.php`.

If you do not have a composer.json file in the root of your project, copy the
contents below and put that into a file called `composer.json` and save it in
the root of your project:

```
{
    "require": {
        "rs/zend2locale": "@dev"
    },
    "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/sromankov/zend2locale"
            }
        ]
}
```

Then execute the following commands in a CLI:

```
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

Usage
---
Set your default locale in the configuration:

```
'slm_locale' => array(
    'default' => 'nl-NL',
),
```

Set all your supported locales in the configuration:

```
'slm_locale' => array(
    'supported' => array('en-US', 'en-GB'),
),
```

If you want to exclude locale URI part for the default language, set assembleDefault to "false":

```
'slm_locale' => array(
    'assembleDefault' => false,
),
```

And enable some strategies. The naming is made via the following list:

 * **cookie**: `SlmLocale\Strategy\CookieStrategy`
 * **host**: `SlmLocale\Strategy\HostStrategy`
 * **acceptlanguage**: `SlmLocale\Strategy\HttpAcceptLanguageStrategy`
 * **query**: `SlmLocale\Strategy\QueryStrategy`
 * **uripath**: `SlmLocale\Strategy\UriPathStrategy`

You can enable one or more of them in the `strategies` list. Mind the priority
is important! You usually want the `acceptlanguage` as last for a fallback:

```
'slm_locale' => array(
    'strategies' => array('uripath', 'acceptlanguage'),
),
```

At this moment, the locale should be detected. The locale is stored inside php's
`Locale` object. Retrieve the locale with `Locale::getDefault()`. This is also
automated inside Zend Framework 2 translator objects and i18n view helpers (so
you do not need to set the locale yourself there).

### Set the locale's language in html
It is common to provide the html with the used locale. This can be set for example
in the `html` tag:

```
<html lang="en">
```

Inject the detected language here with the following code:

```
<html lang="<?= Locale::getPrimaryLanguage(Locale::getDefault())?>">
```

### Create a list of available locales

T.B.D

Read more about usage and the configuration of all the strategies in the
[documentation](docs/1.Introduction.md).
