<?PHP

$this->slot ("page_title", "My Web Page");
$this->use_css ("categories_view.css");
$this->use_js ("categories_view.js");

?>
<?PHP if ($pager = $this->pager()): ?>
<h2>Page # <?PHP echo $pager->pageNo() ?> of <?PHP echo $pager->pageCount() ?></h2>
<?PHP else: ?>
<h2>No pager found, Page # 1 of 1</h2>
<?PHP endif ?>

<?PHP foreach ($categories as $category): ?>
<pre>
<?PHP var_dump ($category) ?>
</pre>
Category # <?PHP echo $category["category_id"] ?>, <?PHP echo $category["category_name"] ?>, <?PHP echo $category["category_parent_id"] ?><br />
<?PHP endforeach ?>
