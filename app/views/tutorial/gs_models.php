<?php $this->fillslot('page_title', 'Getting Started: Models'); ?>

<h3>Getting Started With Models</h3>

<div style="width:50%">
Model refers to a database table and the means by which it is accessed.  A
simpleMVC model class provides basic CRUD for a given table, but can be
extended with additional methods as needed.  simpleMVC supports the database
server MySQL.
<h4>Table Requirements</h4>
<b>Name:</b> prefix + plural name of contents, e.g. app_titles, app_authors*
<br /> <br />
* The default prefix is 'app_' and can be changed in the framework
configuration file.  You can also specify the table name directly in the model
class; see the sample model for an example.
<br /> <br />
<b>Columns:</b> there is only one required column.  It must be named 'id' and
should be defined as follows...
<br /> <br />
`id` int(10) unsigned NOT NULL auto_increment,
<br />
PRIMARY KEY  (`id`),
<br /> <br />
<b>Engine type:</b> any
</div>

<br />

<div><a href="<?= Route::toURL('/tutorial/gs') ?>">Getting Started</a></div>

<br />
