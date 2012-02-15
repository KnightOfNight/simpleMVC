<?php $this->fillslot('page_title', 'Getting Started: Models'); ?>

<h3>Getting Started With Models</h3>

<div style="width:50%">
Model refers to a database table and the means by which it is accessed.  A
simpleMVC model class provides basic CRUD functionality for a given table, but
can be extended with additional methods as needed.  simpleMVC supports the
database server MySQL.
<h4>Table Requirements</h4>
<b>Name:</b> prefix + plural name of contents, e.g. app_titles, app_authors <b>*</b>
<br /> <br />
<b>*</b> the default prefix is 'app_' and can be changed in the framework
configuration file.  You can also override the name of a single table
in the model class.  See the sample model for an example.
<br /> <br />
<b>Columns:</b> there is only one required column.  The following table description
should be used...
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
