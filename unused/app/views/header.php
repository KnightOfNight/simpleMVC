<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?PHP $this->includeSlot("page_title") ?></title>
<?PHP $this->includeCSS() ?>
<?PHP $this->includeJS() ?>
</head>

<body>

<div><a href="<?PHP echo Link::to("welcome") ?>">Welcome</a></div>