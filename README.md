## Installation


1. *We work on stable, supported and up-to-date versions of packages. We recommend you to do the same.*

```bash
$ composer require acseo/sylius-admin-tracker-plugin
```

2. Add plugin dependencies to your `config/bundles.php` file:

```php
return [
    ...
    ACSEO\SyliusAdminTrackerPlugin\SyliusAdminTrackerPlugin::class => ['all' => true],
];
```

3. Import required config in your `config/packages/_sylius.yaml` file:
```yaml
# config/packages/_sylius.yaml

imports:
      ...
          
          - { resource: "@SyliusAdminTrackerPlugin/Resources/config/services.yaml" }
```

4. Import routing in your `config/routes.yaml` file:

```yaml

# config/routes.yaml
...

sylius_admin_user_action:
    resource: "@SyliusAdminTrackerPlugin/Resources/config/routes/sylius_admin.yaml"
```

5. Finish the installation by updating the database schema and installing assets:

```bash
$ bin/console cache:clear
$ bin/console do:migrations:di
$ bin/console doctrine:migrations:migrate
```

6.  Please add plugin templates into your project:
$ cp -R vendor/acseo/sylius-admin-tracker-plugin/src/Resources/views/Grid/Field templates/bundles/SyliusAdminBundle/Grid
