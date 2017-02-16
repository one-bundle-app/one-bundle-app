# One Bundle App

Welcome to OneBundleApp, a small project to allow your bundle to be an app
without any configuration.

### *TL;DR*

* *This project is perfect for your api-exposing bundles. For example, you have a
php library proposing an API with a ser of services, and you have a Symfony
bundle just built to expose these routes to a Symfony project. In fact, your
goal is just create an small API with these routes, right?*
* *Until now, a new Symfony application was needed to make this happen, but the
question is... why? You have a bundle with some dependencies (defined in your
composer.json file and in your bundle), and you only want to expose routes and
commands. Why a new app?*
* *This project allow to create a new app without having an app, just with your
bundle*
* *Follow this small guide and publish your bundle in an isolated way.*
* *No symfony-standard nor symfony-demo installation. Required.*

## The question

Symfony environment is changing so much, and with it, the last movements are 
telling us, the community, that the **Bundle** idea, the concept, is losing its
leadership. Symfony became what it is because of the components and the Bundles,
and both elements should be treated the way they deserve.

In this case, we will give some love the the bundles. To your bundle. But first
of all, let's talk about what is a bundle. Please, take in account that our
conversation should always be "What a bundle is", and never "What a bundle is
meant to be". The second is something important at the beginning of any project.
The second is important in every real project. Let's talk about what bundles are
and their behaviour in the ecosystem.

A Bundle is a layer. A layer that let us propose some classes to a Symfony
project and giving these classes an specific purpose. The framework will, then,
create these tools to make this business logic accessible from the console and
the HTTP channel via the Request/Response pattern.

Good. But we will stay thinking about bundles, and we will not go away from this
point.

A bundle is meant to be a package. A plugin. A bundle is meant to be an isolated
package. But, and knowing the real ecosystem we have in front of us, is it
really this isolated element it was meant to be?

No. At all.

The package [Symfony Bundle Dependencies](https://github.com/mmoreram/symfony-bundle-dependencies)
was created some time ago in order to cover these dependency needs. A bundle, as
a PHP package should define the dependencies in an existence way (composer) and
in an executable way (Bundle). So the first step was covered by adding this
simple layer to your bundles. Each bundle will return an array of Bundle
instances or namespaces to define ... To be instanced, I need these other
bundles to be instanced first.

Remember the change from Symfony 2.0 and 2.1? Do you remember the old .deps
file? Well. This is exactly the same. Exactly the same!

After this movement, your kernel should only have these bundles you specifically
want to instantiate. If your bundle then needs other bundles, required by
composer previously, then these bundles will be instantiated properly by the
library.

Ok. First layer solved.

Continuing talking about what a Bundle is... should a bundle itself be enough to
be operative? A bundle contains enough information to make itself runnable
without any other layers... so should be a bundle information something
http-ready and console-ready?

## The answer

Well, the answer is yes.

If my bundle has self-defined commands, self-defined controllers (PHP end-points
and a routing.yml file), then nothing else is needed to make it accessible from
both point of entry.

The second question is then a natural one. Should all bundles be useful in an
isolated way? Not at all. Tels list some examples.

* **Yes** My bundle exposes a simple API, requiring the PHP library that
connects to database.
* **Yes** My bundle is a basic console interface to manage queues. No http
access but some console items
* **Yes** My bundle is a simple static website
* **Yes** My bundle is part of a project and should be treatable as a simple
project as well.
* **No** My bundle is part of an application and is coupled to it
* **No** My bundle is a set of services for other bundles or for a final 
application
* **No** I have some bundles I want to use, and creating a new Symfony app is
too much for me. I don't have enough time and I'm lazy.
* **No** I don't care about what you're talking about

If your case is not recommended, please keep using the Symfony application.
Otherwise, if you have a Bundle that should be playable without extra things...
You're lucky then. Let's see how to do it.

## Playing with it

Let's divide the game in three steps.

The installation. We need to require the library that will transform your bundle
into an application. That simple.

To do that, open your composer.json file and merge these lines with your current
composer definition.

```json
"require": {
    "one-bundle-app/one-bundle-app": "dev-master"
},
"scripts": {
    "post-install-cmd": [
        "OneBundleApp\\App\\ComposerHook::installEnvironment"
    ],
    "post-update-cmd": [
        "OneBundleApp\\App\\ComposerHook::installEnvironment"
    ]
}
```

In that case, you will download the package `one-bundle-app/one-bundle-app` and,
after installing or updating, some files will be copied in order to become an
application following the Symfony app style.

Then make a `composer update`.

Then we have the configuration. We need to tell the bundle we want to use for
the application and some extra configurations, if we need them. The basic, the
`app.yml` file. Super simple an fast.

```yml
# app.yml

bundles: MyBundle\MyBundle
config:
    parameters:
        some: blabla
routes:
    - '@MyBundle/Resources/config/routing.yml'
```

You can specify an extra configuration per each environment, by appending it in
the file.

```yml
# app_dev.yml

bundles:
    - MyBundle\MyBundle
    - Symfony\Bundle\WebServerBundle\WebServerBundle
config:
    parameters:
        some: anotherblabla
```

Some advices here.

* The app.yml should only have one bundle. Add other bundles in dev environment
like the WebServerBundle. All other bundles should be added as dependencies of
the bundle. Otherwise, if your case requires more than one bundle, then you may
start thinking about an entire application.
* These files should be small and only contain configuration elements. Each
bundle should load their own dependency injection elements.
* Same for routes. Each bundle should define their own routes, so use the routes
part just to load specific bundle routing.yml file.

After the composer update, the system will create these elements in your bundle
root.

* a */var* folder, where to add cache, sessions, logs...
* a */web* folder, with two files. The HTTP entry points *app.php* and the
*app_dev.php* files. Both files are just soft links.
* a */bin* folder, with one file. The console entry point *console*. This file
is just a soft link as well, and has the right permissions to be executable.

That should be enough. Then your bundle should be accessible both from console
and the HTTP layer. Use an Apache or an Nginx, or even the command
*bin/console server:run* without any extra configuration.