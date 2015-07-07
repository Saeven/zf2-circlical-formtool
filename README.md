# Circlical Form Tool

Let's face it, wiring forms the right way with ZF2 takes time!  Late nights after that last sip of RedBull, it feels pretty darned close to poking your forehead with a fork.

I do love Zend Framework 2 though, and so, I've built this little tool for myself that I share.

It is a CLI tool, is extremely lightweight, and is very simple to use:

```
php public/index.php formtool create --doctrine Module FormName Object
```

e.g:

```
php public/index.php formtool create Application Profile User
```

(the --doctrine or -d for short, are optional)

Were you to execute this command, it would create:

* Form
* FormFactory
* InputFilter
* InputFilterFactory

and echo the service manager lines that you need to plug into your config.

How's that for service!

This tool has been a huge timesaver for me, but please feel free to recommend improvements!

![Imgur](http://i.imgur.com/42e0qY8.png)