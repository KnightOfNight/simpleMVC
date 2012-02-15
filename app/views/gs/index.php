<?php $this->fillslot('page_title', 'Getting Started'); ?>

<h4><a href="<?= Route::toURL('/welcome') ?>">Welcome</a></h4>

<h2>Getting Started</h2>

<ol>
<li><a href="<?= Route::toURL('/gs/models') ?>">Models</a>
<li><a href="<?= Route::toURL('/gs/views') ?>">Views</a>
<li><a href="<?= Route::toURL('/gs/controllers') ?>">Controllers</a>
<li><a href="<?= Route::toURL('/gs/routes') ?>">Routes</a>
</ol>
