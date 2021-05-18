# d9ify

The idea behind this site is that there's a single command to create a new Pantheon
D9 site from a messy old D8 site that may or may not be using composer to manage
it's dependencies.


| WARNING                                                                     |
-------------------------------------------------------------------------------
| THIS PROJECT IS IN ALPHA VERSION STATUS AND AT THIS POINT HAS VERY LITTLE   |
| ERROR CHECKING. PLEASE USE AT YOUR OWN RISK.                                |
| The guide to use this file is in /README.md                                 |


![Passing Tests](https://github.com/stovak/d9ify/actions/workflows/php.yml/badge.svg)

## USAGE 

  ```composer install && composer d9ify:process {PANTHEON_SITE_ID}```

## STEPS

### Set Source directory

 Source Param is not optional and needs to be
 a pantheon site ID or name.



### Set Destination directory

 Destination name will be {source}-{THIS YEAR} by default
 if you don't provide a value.



### Clone Source & Destination.

 Clone both sites to folders inside this root directory.
 If destination does not exist, create the using Pantheon's
 Terminus API. If destination doesn't exist, Create it.



### Move over Contrib

 Spelunk the old site for MODULE.info.yaml and after reading
 those files. This step searches for every {modulename}.info.yml. If that
 file has a 'project' proerty (i.e. it's been thru the automated services at
 drupal.org), it records that property and version number and ensures
 those values are in the composer.json 'require' array. Your old composer
 file will re renamed backup-*-composer.json.

   [REGEX](https://regex101.com/r/60GonN/1)
   Get every .info.y{a}ml file in source.

### JS contrib/drupal libraries

 Process /libraries folder if exists & Add ES Libraries to the composer
 install payload.

   [REGEX](https://regex101.com/r/EHYzcz/1)
   Get every package.json in the libraries folder.

### Write the composer file.

 Write the composer file to disk.



### composer install

 Exception will be thrown if install fails.



### Copy Custom Code

 This step looks for {MODULENAME}.info.yml files that also have "custom"
 in the path. If they have THEME in the path it copies them to web/themes/custom.
 If they have "module" in the path, it copies the folder to web/modules/custom.

   [REGEX](https://regex101.com/r/kUWCou/1)
   get every .info file with "custom" in the path, e.g.
   ✓  web/modules/custom/milken_migrate/milken_migrate.info.yaml
   ✗  web/modules/contrib/entity_embed/entity_embed.info.yaml
   ✓  web/modules/custom/milken_base/milken_base.info.yaml

### Ensure pantheon.yaml has preferred values

 Write known values to the pantheon.yml file.



