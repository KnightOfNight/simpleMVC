<?php $this->fillslot('page_title', 'Getting Started'); ?>

<h5><a href="<?= Route::toURL('/welcome') ?>">Welcome</a></h5>

<h3>Getting Started</h3>

<ol>
<li><a href="<?= Route::toURL('/gs/models') ?>">Models</a>
<li><a href="<?= Route::toURL('/gs/views') ?>">Views</a>
<li><a href="<?= Route::toURL('/gs/controllers') ?>">Controllers</a>
<li><a href="<?= Route::toURL('/gs/routes') ?>">Routes</a>
</ol>

