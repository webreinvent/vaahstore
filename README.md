# vaahcms-module-store
Store Module for VaahCMS


#### To Run Modules Dusk Test:
- Change path of dusk in `phpunit.dusk.xml` to following:
```xml
...
<directory suffix="Test.php">./VaahCms/Modules/Store/Tests/Browser</directory>
...
```

- Then run `php artisan dusk`