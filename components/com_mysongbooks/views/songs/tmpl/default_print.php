<?php
defined('_JEXEC') or die('Restricted Access');
/**
 * This file will be outputted in MySongBooksViewSongs::printit just before $app->close
 * This means that there is no J! support for header/any other document rendering stuff
 * All needs to be done manually
 *
 */
/** @var MySongBooksViewSongs $this */
?>
<!doctype html>
<html>
	<head>
		<link rel="stylesheet" href="/templates/yoo_nano2/css/bootstrap.css" type="text/css" />
		<link rel="stylesheet" href="/templates/yoo_nano2/css/base.css" />
		<link rel="stylesheet" href="/media/com_mysongbooks/css/front.css" type="text/css" />
		<link rel="stylesheet" href="/media/com_mysongbooks/css/print.css" type="text/css" />
		<script src="/media/jui/js/jquery.min.js" type="text/javascript"></script>
		<script src="/media/com_mysongbooks/js/raphael.js" type="text/javascript"></script>
		<script src="/media/com_mysongbooks/js/jTab.js" type="text/javascript"></script>
	</head>
	<body>
		<div class="container">
			<?php echo $this->songPageContent; ?>
		</div>
	</body>
</html>