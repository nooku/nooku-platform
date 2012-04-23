<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>
<!DOCTYPE HTML>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>

<meta http-equiv="X-UA-Compatible" content="chrome=1">

<jdoc:include type="head" />

<link href="templates/<?php echo  $this->template ?>/css/default.css" rel="stylesheet" type="text/css" />

<?php if(strpos(KRequest::get('server.HTTP_USER_AGENT', 'word'), 'Titanium')) : ?>
     <link href="templates/desktop/css/template.css" rel="stylesheet" type="text/css" />
 <?php endif ?>

</head>
<body id="tmpl-component" class="<?php echo JRequest::getVar('option', 'cmd'); ?> contentpane">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>