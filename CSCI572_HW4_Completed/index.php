<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$sort = isset($_REQUEST['rank']) && $_REQUEST['rank'] == 'pagerank' ? array('sort' => 'pageRankFile desc') : false;
$results = false;
if ($query) {
	 // The Apache Solr Client library should be on the include path
	 // which is usually most easily accomplished by placing in the
	 // same directory as this script ( . or current directory is a default
	 // php include path entry in the php.ini)
	 require_once('solr-php-client-master/Apache/Solr/Service.php');

	 // create a new solr service instance - host, port, and corename
	 // path (all defaults in this example)
	 
	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

	 // if magic quotes is enabled then stripslashes will be needed
	 if (get_magic_quotes_gpc() == 1) {
	 	$query = stripslashes($query);
	 }

	//additional parameters to Solr Server
	$additionalParameters = array(
		'fq' => 'a filtering query',
		'facet' => 'true',
		'facet.field' => array(
			'field_1',
			'field_2'
		)
	);

	 // in production code you'll always want to use a try /catch for any
	 // possible exceptions emitted by searching (i.e. connection
	 // problems or a query parsing error)
	 try {
	 	if ($sort) {
	     	$results = $solr->search($query, 0, $limit, $sort);
		} else {
	 		$results = $solr->search($query, 0, $limit);
	 	}
	 } catch (Exception $e) {
	 // in production you'd probably log or email this error to an admin
	 // and then show a special message to the user but for this example
	 // we're going to show the full exception
	 	die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	 }
}
?>

<html>
	 <head>
	 	<title>PHP Solr Client Example</title>
	 </head>
	 <meta charset="utf-8">
	 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  	 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  	 <style type="text/css">
  	 	body{
  	 		margin: 25px;
  	 	}
  	 </style>
	 <body>
		 <form accept-charset="utf-8" method="get">
		 	<div class="form-group">
			 <label for="q">Search:</label>
			 <input id="q" name="q" type="text" class="form-control" placeholder="enter query" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
				 <div class="form-check">
					 <input class="form-check-input" type="radio" name="rank" value="lucene" checked="checked" <?php if(isset($_REQUEST['rank']) && $_REQUEST['rank']=="lucene") echo "checked";?>>Lucene
					 
		      		 <input class="form-check-input" type="radio" name="rank" value="pagerank" <?php if (isset($_REQUEST['rank']) && $_REQUEST['rank']=="pagerank") echo "checked";?>>Page Rank
		      	 
				 	<input type="submit" class="btn btn-info"/>
				 </div>
			</div>
		 </form>

<?php
// display results
if ($results) {
	$total = (int) $results->response->numFound;
	$start = min(1, $total);
	$end = min($limit, $total);

	$idUrlMatrix = array();
	$fileName = "URLtoHTML_reuters_news.csv";
	if (file_exists($fileName)) {
		$f = fopen($fileName, "r") or die("Unable to open");
		while(!feof($f)) {
			$temp = fgetcsv($f);
			$idUrlMatrix[$temp[0]] = $temp[1];
		}
		fclose($f);
	} else {
		echo "File Doesn't Exist.";
	}

?>
	 <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
	 <ol>
<?php
 // iterate result documents
 foreach ($results->response->docs as $doc) {
?>
	 <li>
	 <table class="table table-bordered">
<?php
 // iterate document fields / values
$displayInfo = array();
foreach ($doc as $field => $value) {
	if ($field == "title" || $field == "og_url" || $field == "id" || $field == "og_description" || $field = "Description" ) {
		$displayInfo[$field] = $value;
	}
}
?>

	<tr>
		<th><?php echo htmlspecialchars(Title, ENT_NOQUOTES, 'utf-8'); ?></th>
		<td><?php 
			if (array_key_exists("og_url", $displayInfo)) {
				echo "<a href='".htmlspecialchars($displayInfo["og_url"], ENT_NOQUOTES, 'utf-8')."'>".htmlspecialchars($displayInfo["title"], ENT_NOQUOTES, 'utf-8')."</a>"; 
			} else {
				echo "<a href='".$idUrlMatrix[explode("/", $displayInfo["id"])[6]]."'>".htmlspecialchars($displayInfo["title"], ENT_NOQUOTES, 'utf-8')."</a>";
			}
		?></td>
	</tr>
	<tr>
		<th><?php echo htmlspecialchars(URL, ENT_NOQUOTES, 'utf-8'); ?></th>
		<td><?php 
			if (array_key_exists("og_url", $displayInfo)) {
				echo "<a href='".htmlspecialchars($displayInfo["og_url"], ENT_NOQUOTES, 'utf-8')."'>".htmlspecialchars($displayInfo["og_url"], ENT_NOQUOTES, 'utf-8')."</a>";
			} else {
				echo "<a href='".$idUrlMatrix[explode("/", $displayInfo["id"])[6]]."'>".htmlspecialchars($displayInfo["og_url"], ENT_NOQUOTES, 'utf-8')."</a>";
			}
		?></td>
	</tr>
	<tr>
		<th><?php echo htmlspecialchars(ID, ENT_NOQUOTES, 'utf-8'); ?></th>
		<td><?php echo htmlspecialchars($displayInfo["id"], ENT_NOQUOTES, 'utf-8'); ?></td>
	</tr>
	<tr>
		<th><?php echo htmlspecialchars(Description, ENT_NOQUOTES, 'utf-8'); ?></th>
		<td><?php 
			if (array_key_exists("og_description", $displayInfo)) {
				echo htmlspecialchars($displayInfo["og_description"], ENT_NOQUOTES, 'utf-8'); 
			} else {
				echo htmlspecialchars(Null, ENT_NOQUOTES, 'utf-8'); 
			}
		?></td>
	</tr>

	 </table>
	 </li>
<?php
 }
?>
 	</ol>
<?php
}
?>
 	</body>
</html>
