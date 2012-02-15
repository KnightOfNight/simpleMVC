<?php $this->fillslot('page_title', 'Getting Started: Models'); ?>

<h4><a href="<?= Route::toURL('/welcome') ?>">Welcome</a></h4>
<h4><a href="<?= Route::toURL('/gs') ?>">Getting Started</a></h4>

<div style="width:50%">
<h2>Models</h2>
Model refers to a database table and the means by which it is accessed.  A
simpleMVC model class provides basic CRUD for a given table, but can be
extended with additional methods as needed.  simpleMVC supports the database
server MySQL.
</div>

<br />

<div style="width:50%">
<h4>Table Requirements</h4>
<em>Name:</em> prefix + plural name of contents, e.g. app_titles, app_authors*
<br /> <br />
* The default prefix is 'app_' and can be changed in the framework
configuration file.  You can also specify the table name directly in the model
class; see the sample model for an example.
<br /> <br />
<em>Columns:</em> there is only one required column.  It must be named 'id' and
should be defined as follows...
<br /> <br />
`id` int(10) unsigned NOT NULL auto_increment,
<br />
PRIMARY KEY  (`id`),
<br /> <br />
<em>Engine type:</em> any
</div>
