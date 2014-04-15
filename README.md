wp-posttypes
============

Wordpress Custom Post Types Plugin

## Usage

Install to plugin directory.

Create a new directory under `wp-content` called `posttypes` (This will be configurable soon).

Inside of the `posttypes` directory create a directory called `types`. This will store all the yaml files.

## Creating a post type

Copy one of the example yaml files from the `example-conf` directory and place it into the `types` directory you created.

After you make changes you need to flush the rewrite cache before your changes will take affect on the front-end. To do this, click the Tools→Flush Rewrite Cache link.