<?php $this->fillslot('page_title', 'Getting Started: Routes'); ?>

<h3>Getting Started With Routes</h3>

<div style="width:50%">
Route refers to a specific path through the application framework.  A route is
parsed from the URL used to access the application, and it consists of three
parts: the controller, an action, and a query.

<br /> <br />

There must be a class written for each controller, and in each such class there
must be methods to handle each of that controller's actions.  The framework
will automatically parse any query string and pass it to the action method as a
hash.

<br /> <br />

All routes must be defined in the application configuration file.  This also
controls which actions can be accessed externally via browser, and what action,
if any, is the default for a given controller.  You can also setup rewrites using
PCRE.

</div>

<br />

<div><a href="<?= Route::toURL('/gs') ?>">Getting Started</a></div>

<br />
