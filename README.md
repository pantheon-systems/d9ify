# d9ify

The idea behind this site is that there's a single command to create a new Pantheon
D9 site from a messy old D8 site that may or may not be using composer to manage
it's dependencies.

![Passing Tests](https://github.com/stovak/d9ify/actions/workflows/php.yml/badge.svg)

USAGE: ```composer install && composer d9ify:process {PANTHEON_SITE_ID}```

1. STEP 
***************************************

  ### Set Source and Destination.
  
  Source Param is not optional and needs to be
  a pantheon site ID or name.



1. STEP 
***************************************

  ### Set Destination directory
  
  Destination name will be {source}-{THIS YEAR} by default
  if you don't provide a value. Destination name will be
  {source}-{THIS YEAR} by default if you don't provide a value.



1. STEP 
***************************************

  ### Clone Source & Destination.
  
  Clone both sites to folders inside this root directory.
  If destination does not exist, create the using Pantheon's
  Terminus API. If destination doesn't exist, Create it.



1. STEP 
***************************************

  ### Move over Contrib
  
  Spelunk the old site for MODULE.info.yaml and after reading
  those files. This step searches for every {modulename}.info.yml. If that
  file has a 'project' proerty (i.e. it's been thru the automated services at
  drupal.org), it records that property and version number and ensures
  those values are in the composer.json 'require' array. Your old composer
  file will re renamed backup-*-composer.json.



1. STEP 
***************************************

  ### JS contrib/drupal libraries
  
  Process /libraries folder if exists & Add ES Libraries to the composer
  install payload



1. STEP 
***************************************

  ### Write the composer file.



1. STEP 
***************************************

  ### composer install
  
  Exception will be thrown if install fails.



1. STEP 
***************************************

  ### Copy Custom Code.
  
  This step looks for {MODULENAME}.info.yml files that also have "custom"
  in the path. If they have THEME in the path it copies them to web/themes/custom.
  If they have "module" in the path, it copies the folder to web/modules/custom.



1. STEP 
***************************************

  ### Ensure pantheon.yaml has preferred values.


