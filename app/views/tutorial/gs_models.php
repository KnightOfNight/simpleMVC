<pre>
Model refers to a database table and the means by which it is accessed.  A
simpleMVC model class provides basic CRUD functionality for a given table, but
can be extended with additional methods as needed.  simpleMVC supports the
database server MySQL.

The following requirements apply to tables used with simple MVC...

Name: prefix + plural name of contents, e.g. app_titles, app_authors*

  * the default prefix is 'app_' and can be changed in the framework
  configuration file.  You can also override the name of a single table
  in the model class.  See the sample model for an example.

Columns: there is only one required column.  The following table description
should be used...

  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),

Engine type: any
</pre>
