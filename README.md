**This code is part of the [SymfonyContrib](http://symfonycontrib.com/) community.**

# Symfony2 ContentSchedulerBundle

###Features:

* Schedule publishing and unpublishing of content.
* more to come...

## Installation

Installation is similar to a standard bundle.
http://symfony.com/doc/current/cookbook/bundles/installation.html

* Add bundle to composer.json: https://packagist.org/packages/symfonycontrib/content-scheduler-bundle
* Add bundle to AppKernel.php:

```php
new SymfonyContrib\Bundle\ContentSchedulerBundle\ContentSchedulerBundle(),
```

## Usage Examples

Currently only schedule publishing and unpublishing is provided but the framework
is there to add more actions easily.

To implement scheduled publishing you need to collect the scheduling data on
your content form. A prepared type is provided that can be used or extended.

```php
$builder->add('scheduler', ScheduledPublishingType::class);
```

That is all that is need to create and manage your publishing schedule.
The only thing left is to have something execute schedules when needed.
This is done by running:

```php
$this->get('content_scheduler.publishing.scheduler')->runDueActions();
```

** [CronBundle](https://github.com/SymfonyContrib/CronBundle) works well in executing this task. **

