<?php $this->fillslot('page_title', 'Getting Started: Views'); ?>

<h5><a href="<?= Route::toURL('/welcome') ?>">Welcome</a></h5>

<h5><a href="<?= Route::toURL('/gs') ?>">Getting Started</a></h5>

<h3>Getting Started With Views</h3>

<div style="width:50%">
View refers to the PHP files that make up a particular view for a specific
route.  A view can consist of any number of files, but in general there are
three: a header, the main body, and a footer.

<br /> <br />

By default, a common header and footer are rendered on all views.  The header
usually includes things like the 'doctype' line and the 'head' section and
usually ends with the body tag.  The footer is usualy very short and just
finishes off the overall page.  The common header and footer can be replaced
with ones which are specific to a given controller, and you can disable header
and footer altogether, for example when using a view that is meant to be served
in an AJAX query.

<br /> <br />

The main body of the view is where all real content will likely be placed, and
will usually consist of HTML with some embedded PHP.  simpleMVC has several
helper functions for writing views to simplify things like getting an URL for
an 'href'.
</div>

<br />

