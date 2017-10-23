<?php
	require "ht/connect.php";
	require "service/JwtService.php";
	require "service/LogService.php";
	require "service/AdminService.php";
	
	$warning = "";
	$success = "";

	$user = (new JwtService(new LogService()))->getUserFromJwt();
	$isAdmin = $user->isAdmin();
	if ($isAdmin)
	{
		$adminService = new AdminService($link);
		$nameToTable = array(
			"user"	 => $adminService->userTable,
			"filter" => $adminService->filterTable,
			"wish"	 => $adminService->wishTable,
			"present" => $adminService->presentTable
		);

		// readTable
		$tableToRead = null;
		$table = null;
		if(isset($_POST['readTable']) && !empty($_POST['readTable']))
		{
			$t = $_POST['readTable'];
			$table = $nameToTable[$t];
			
			// update
			if(isset($_POST['dbId']) && !empty($_POST['dbId']) && isset($_POST['dbField']) && !empty($_POST['dbField']))
			{
				$dbId = $_POST['dbId'];
				$dbField = $_POST['dbField'];
				$newValue = $_POST['dbValue_'.$dbId.'_'.$dbField];
				$updateResult = $adminService->sqlUpdate($table, array($dbField => $newValue), $_POST['dbId']);
				if(!empty($updateResult))
					$warning .= $updateResult;
			}
			
			// read
			$tableToRead = $adminService->sqlSelect("SELECT * FROM ".$t);
		}

		// dropTable
		if(isset($_POST['dropTable']) && !empty($_POST['dropTable']))
		{
			$dropTable = $_POST['dropTable'];
			$query = "DROP TABLE ".$dropTable;
			$dropResult = $adminService->sql($query);
			if(empty($dropResult))
				$success .= "Tabelle '".$dropTable."' gelöscht<br />\n";
			else
				$warning .= $dropResult."<br />\n";
		}
		
		// createTable
		if(isset($_POST['createTable']) && !empty($_POST['createTable']))
		{
			$createTable = $_POST['createTable'];
			$table = $nameToTable[$createTable];
			$query = "CREATE TABLE ".$table->name." (";
			$count = count($table->fields);
			for ($i = 0; $i < $count; $i++)
			{
				$field = $table->fields[$i];
				$type = $field->type;
				if($adminService->startsWith($type, "VARCHAR"))
					$type .= " CHARACTER SET utf8 COLLATE utf8_general_ci";
				$query .= "\n\t".$field->name."\t".$type;
					if(!$field->mayBeNull)
				$query .= " NOT NULL";
				if($i == 0)
					$query .= " AUTO_INCREMENT";
				$query .= ",";
			}
			$query .= "\n\tPRIMARY KEY (".$table->fields[0]->name."))";
			$createResult = $adminService->sql($query);
			if(empty($createResult))
				$success .= "Tabelle '".$table->name."' erstellt<br />\n";
			else
				$warning .= $createResult."<br />\n";
		}
		
		// fillTable
		if(isset($_POST['fillTable']) && !empty($_POST['fillTable']))
		{
			$fillTable = $_POST['fillTable'];
			if ($fillTable == "user")
			{
				$query = "INSERT INTO user
							VALUES	(NULL, 'Lisa',		'".password_hash("Lisa", PASSWORD_DEFAULT)."',		'', ''),
									(NULL, 'Michael',	'".password_hash("Michael", PASSWORD_DEFAULT)."',	'', ''),
									(NULL, 'Ulla',		'".password_hash("Ulla", PASSWORD_DEFAULT)."',		'', ''),
									(NULL, 'Julia',		'".password_hash("Julia", PASSWORD_DEFAULT)."',		'', ''),
									(NULL, 'Rene',		'".password_hash("Rene", PASSWORD_DEFAULT)."',		'', ''),
									(NULL, 'Claudia',	'".password_hash("Claudia", PASSWORD_DEFAULT)."',	'', ''),
									(NULL, 'Manuel',	'".password_hash("m", PASSWORD_DEFAULT)."', 		'',	'Y'),
									(NULL, 'Jana',		'".password_hash("Jana", PASSWORD_DEFAULT)."',		'', ''),
									(NULL, 'Jonas',		'".password_hash("Jonas", PASSWORD_DEFAULT)."',		'', '')";
				$insertResult = $adminService->sql($query);
				if(empty($insertResult))
					$success .= "Tabelle '".$fillTable."' gefüllt<br />\n";
				else
					$warning .= $insertResult."<br />\n";
			}
			else
				$warning .= "Funktion nicht implementiert<br />\n";
		}
		
		// execute SQL
		if(isset($_POST['lqs']) && !empty($_POST['lqs']) && isset($dbName))
		{
			$sqlResult = $adminService->sql($_POST['lqs']);
			if(empty($sqlResult))
				$success .= "SQL ausgeführt<br />\n";
			else
				$warning .= $sqlResult."<br />\n";
		}
		
		// create DB
		if(isset($_POST['createDB']) && !empty($_POST['createDB']))
		{
			$sqlResult = $adminService->sql("CREATE DATABASE '".$dbName."'");
			if(empty($sqlResult))
				$success .= "Datenbank erstellt<br />\n";
			else
				$warning .= $sqlResult."<br />\n";
		}
		
		// new user
		if (isset($_POST['newUser']))
		{
			$nm = $_POST["u_nm"];
			$countNames = count(getFieldsUnique($userTable->name, array("u_name"), "u_name", $nm));
			if($countNames == 0)
			{
				$pw = $_POST["u_pw"];
				$fieldsArray = array(
						"u_id" => null,
						"u_name" => $nm,
						"u_pw" => password_hash($pw, PASSWORD_DEFAULT),
						"u_log" => null,
						"u_adm" => null
						);
				$insertResult = $adminService->sqlInsert($userTable, $fieldsArray);
				if(empty($insertResult))
					$success .= "Neuen Admin erfolgreich hinzugefügt<br />\n";
				else
					$warning .= $insertResult."<br />\n";
			}
			else
				$warning .= "Nimm einen anderen Benutzernamen<br />\n";
		}
		
		mysqli_close($link);

$title = "Admin";
$pagename = "admin.php";
include("admin/header.inc");
if(!empty($warning))
{
	echo "<div class=\"bg-danger\">\n".$warning."\n</div>";
}
if(!empty($success))
{
	echo "<div class=\"bg-success\">\n".$success."\n</div>";
}
?>
<script src="admin/dropzone.js"></script>
<h3>Admin</h3>

		<form name="adminForm" action="" method="post">
<?php	$actions = array("read" => "auslesen");
		if($isAdmin)
		{
			$actions["drop"] = "löschen";
			$actions["create"] = "erstellen";
			$actions["fill"] = "füllen";
		}
		$actionKeys = array_keys($actions);
		for($i = 0; $i <= count($actionKeys); $i++)
		{
?>			<input type="hidden" name="<?php echo $actionKeys[$i]; ?>Table" value="" />
<?php	}
?>		</form>

		<div class="btn-group btn-group-justified" role="group" aria-label="...">
<?php	for($i = 0; $i < count($actionKeys); $i++)
		{
			$key = $actionKeys[$i];
?>			<div class="btn-group" role="group">
				<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu<?php echo $key; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					Tabelle <?php echo $actions[$key]; ?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu<?php echo $key; ?>">
					<li><a href="javascript:setFieldAndOpen('adminForm', '', '<?php echo $key; ?>Table', 'user');">user</a></li>
					<li><a href="javascript:setFieldAndOpen('adminForm', '', '<?php echo $key; ?>Table', 'filter');">filter</a></li>
					<li><a href="javascript:setFieldAndOpen('adminForm', '', '<?php echo $key; ?>Table', 'wish');">wish</a></li>
					<li><a href="javascript:setFieldAndOpen('adminForm', '', '<?php echo $key; ?>Table', 'present');">present</a></li>
				</ul>
			</div>
<?php	}
?>			<div onclick="javascript:showThis('#newUser');" class="btn btn-default btn-group" role="group">Neuer Benutzer</div>
		</div>

		<form name="sqlForm" action="" method="post" class="input-group">
			<input type="text" class="form-control" name="lqs" id="lqs" placeholder="">
			<span class="input-group-btn">
				<button type="submit" name="execute" value="true" class="btn btn-default">Führe SQL aus</button>
			</span>
		</form>

<?php
if($tableToRead != null)
{
?>
	<h4 class="toggleDiv">Tabelle '<?php echo $table->name; ?>'</h4>

	<form name="dbForm" action="" method="post" class="toggleDiv">
	<input type="hidden" name="readTable" value="<?php echo $table->name; ?>" />
	<input type="hidden" name="dbId" value="" />
	<input type="hidden" name="dbField" value="" />
	<table class="table">
	<thead>
		<tr>
<?php
	$countColumns = count($table->fields);
	for ($j = 0; $j < $countColumns; $j++)
	{
?>			<td id="<?php echo $table->fields[$j]->name; ?>"><span title="<?php echo $table->fields[$j]->type; ?>"><?php echo $table->fields[$j]->name; ?></span></td>
<?php
	}
?>		</tr>
	</thead>
	<tbody>
<?php
	for ($i = 0; $i < count($tableToRead); $i++)
	{
?>		<tr id="<?php echo $tableToRead[$i][0]; ?>">
<?php	for ($j = 0; $j < $countColumns; $j++)
		{
			$value = $tableToRead[$i][$j];
			$isNull = $value == null;
			$tdStyle = "";
			$fieldName = $table->fields[$j]->name;
			if($isNull == true)
			{
				$value = "NULL";
				$tdStyle = "style=\"color:#999;\"";
			}
?>			<td <?php echo $tdStyle; ?>>
				<div class="input-group inputToggle" style="display:none;">
					<input type="text" class="form-control" name="dbValue_<?php echo $tableToRead[$i][0]; ?>_<?php echo $fieldName; ?>" value="<?php echo $tableToRead[$i][$j]; ?>" />
					<span class="input-group-addon" onclick="document.forms['dbForm'].submit();"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></span>
					<span class="input-group-addon" onclick="resetField(<?php echo $tableToRead[$i][0]; ?>, <?php echo $j; ?>);"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></span>
				</div>
				<span style="cursor:pointer;" onclick="javascript:setField(<?php echo $tableToRead[$i][0]; ?>, <?php echo $j; ?>, '<?php echo $fieldName; ?>');">
					<?php echo $value; ?>
				</span>
			</td>
<?php	}
?>		</tr>
<?php
	}
?>	</tbody>
	</table>
	</form>

<span id="onclickActive" style="display:none;">true</span>
<script>
	$(function()
	{
<?php
		for ($j = 0; $j < count($table->fields); $j++)
		{
?>			$('#<?php echo $table->fields[$j]->name; ?> span').tooltip();
<?php	}
?>	});
	
	function setField(row, column, field)
	{
		if($("#onclickActive").html() == "true")
		{
			var td = $("tr#" + row + " td:nth-child(" + (column+1) + ")");
			td.children("span").hide();
			td.children("div.inputToggle").show();
			$("#onclickActive").html("false");
			
			var form = document.forms["dbForm"];
			form["dbId"].value = row;
			form["dbField"].value = field;

			$("tbody td span").css("cursor", "default");
		}
	}

	function resetField(row, column)
	{
		var td = $("tr#" + row + " td:nth-child(" + (column+1) + ")");
		td.children("span").show();
		td.children("div.inputToggle").hide();
		$("#onclickActive").html("true");
		
		var form = document.forms["dbForm"];
		form["dbId"].value = "";
		form["dbField"].value = "";

		$("tbody td span").css("cursor", "pointer");
	}
</script>
<?php
}
?>
<div id="newUser" style="display:none;" class="toggleDiv">
	<h4>Neuer Benutzer</h4>
	<form action="" method="post">
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">Benutzername:</span>
				<input type="text" class="form-control" name="u_nm" placeholder="Benutzername" />
			</div>
			
			<div class="input-group">
				<span class="input-group-addon">Passwort:</span>
				<input type="password" class="form-control" name="u_pw" placeholder="Passwort" />
			</div>
			
			<div class="input-group">
				<span class="input-group-addon">Admin:</span>
				<input type="checkbox" class="form-control" name="u_adm"/>
			</div>
		</div>
		<button type="submit" name="newUser" value="true" class="btn btn-default">Benutzer anlegen</button>
	</form>
</div>

<div id="importPlayers" style="display:none;" class="toggleDiv">
	<h4>Importiere Spieler aus .csv</h4>
	<p><a href="players.csv">Leere Importdatei (players.csv)</a></p>
	<div id="dropzone" style="
		background: white none repeat scroll 0 0;
		border: 2px dashed #0087f7;
		border-radius: 5px;
		min-height: 150px;
    	padding: 54px;
    	cursor: pointer;
    	text-align: center;
	">Upload per Drag & Drop oder Klick für Dateiauswahl</div>

	<div class="table table-striped" class="files" id="previews">
		<div id="template" class="file-row">
			<!-- This is used as the file preview template -->
			<div>
				<span class="preview"><img data-dz-thumbnail /></span>
			</div>
			<div>
				<p class="name" data-dz-name></p>
				<strong class="error text-danger" data-dz-errormessage></strong>
			</div>
			<div>
				<p class="size" data-dz-size></p>
				<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
					<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
				</div>
			</div>
			<div>
				<button class="btn btn-primary start">
					<i class="glyphicon glyphicon-upload"></i>
					<span>Import</span>
				</button>
				<button data-dz-remove class="btn btn-warning cancel">
					<i class="glyphicon glyphicon-ban-circle"></i>
					<span>Abbrechen</span>
				</button>
			</div>
		</div>
	</div>

</div>
<script>
	$(function()
	{
		// Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
		var previewNode = document.querySelector("#template");
		previewNode.id = "";
		var previewTemplate = previewNode.parentNode.innerHTML;
		previewNode.parentNode.removeChild(previewNode);

		var myDropzone = new Dropzone("div#dropzone", { // Make the whole body a dropzone
			url: "upload.php", // Set the url
			thumbnailWidth: 80,
			thumbnailHeight: 80,
			parallelUploads: 20,
			previewTemplate: previewTemplate,
			autoQueue: false, // Make sure the files aren't queued until manually added
			previewsContainer: "#previews", // Define the container to display the previews
			clickable: "div#dropzone", // Define the element that should be used as click trigger to select files.
			success: function(file, response)
			{
				if (response != null && response.indexOf("Spieler importiert") >= 0)
					location.href="players.php";
				else
					alert(response);
			}
		});

		myDropzone.on("addedfile", function(file) {
			// Hookup the start button
			file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
		});

		// Update the total progress bar
		myDropzone.on("totaluploadprogress", function(progress) {
			$("#total-progress .progress-bar").css("width", progress + "%");
		});

		myDropzone.on("sending", function(file) {
			// Show the total progress bar when upload starts
			$("#total-progress").css("opacity", "1");
			// And disable the start button
			file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
		});

		// Hide the total progress bar when nothing's uploading anymore
		myDropzone.on("queuecomplete", function(progress) {
			$("#total-progress").css("opacity", "0");
		});
	});

	function showThis(divId)
	{
		$(".toggleDiv").hide();
		$(divId).toggle();
	}

	function setFieldAndOpen(formName, action, field, value)
	{
		var form = document.forms[formName];
		form[field].value = value;
		form.action = action;
		form.submit();
	}
</script>

<br	/>
<?php
	}
	include("admin/footer.inc");
?>