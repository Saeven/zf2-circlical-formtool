# Laminas Powertools

[![Latest Stable Version](https://poser.pugx.org/saeven/zf2-form-builder/v)](//packagist.org/packages/saeven/zf2-form-builder) [![Total Downloads](https://poser.pugx.org/saeven/zf2-form-builder/downloads)](//packagist.org/packages/saeven/zf2-form-builder) [![Latest Unstable Version](https://poser.pugx.org/saeven/zf2-form-builder/v/unstable)](//packagist.org/packages/saeven/zf2-form-builder) [![License](https://poser.pugx.org/saeven/zf2-form-builder/license)](//packagist.org/packages/saeven/zf2-form-builder)

Ok.  Let's face it.  Wiring forms with Laminas takes time.  I do love Zend Framework and Laminas, but Forms taste like the cardboard at the bottom of your tub of ice cream.  I built this form helper to spark a little joy.  Forms are just less terrible with this in my composer.json, and in yours too.

PLUS, it also writes controllers.

THEN, it'll open them in your PHPStorm (or whatever else).

It's a CLI tool, is extremely lightweight, and is very simple to use.


https://user-images.githubusercontent.com/887224/117239821-79662300-adfd-11eb-88f7-3cd53bcdeaec.mov


## Installation

    composer require --dev saeven/zf2-form-builder

Then, add it to your application.config.php (laminas-mvc) with `Circlical\LaminasTools`.

## Usage

### Forms
Suppose you wanted to create a FooForm, FooFormFactory, FooInputFilter and FooInputFilterFactory in the Bar module, and wire it all up in your form_elements and input_filters config keys (in Bar).  

*What a chore to even write that!*

Well, this tool turns all that into a one-liner.

```
vendor/bin/laminas ct:form
```

Answer the questions, and you're off to the races.

### Controllers
It'll write controllers as well.

```
vendor/bin/laminas ct:controller
```

## Successfully Achieve 98% Lazy

All you have to do, is pull those config files into your module.config.php like so:

    'form_elements' => require forms.config.php,
    'input_filters' => require inputfilters.config.php,

How's that for service!  You can kick up your feet, tinker with your Pomodoro timer and rake in that same hourly wage with a maximized degree of relaxation.

This tool has been a huge timesaver for me, but please feel free to recommend improvements!  I also accept cookie recipes.

### Some Notes

* Assumes you are structuring your modules like module/Foo/src/Controller
* I could have dug deep and created some nice abstractions, but I didn't.  
* Didn't care enough to write tests for now.  Hammered this last version out real fast, wanted it for another project that has a ton of forms and controllers on the horizon.
