<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
	
	<title><?php echo $title; ?></title>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" href="admin/sticky-footer.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/blitzer/jquery-ui.min.css">
	
	<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body>

	<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="index.php"><?php echo $title; ?></a>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
			<ul class="nav navbar-nav">
				<li <?php if($pagename=="index.php"){echo "class='active'";}?>><a href="index.php">Start</a></li>
				<li <?php if($pagename=="wishes.php"){echo "class='active'";}?>><a href="wishes.php">meine Wünsche</a></li>
				<li <?php if($pagename=="presents.php"){echo "class='active'";}?>><a href="presents.php">meine Geschenke</a></li>
				<li <?php if($pagename=="admin.php"){echo "class='active'";}?>><a href="admin.php">Admin</a></li>
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<li><form action="" method="post" role="search" style="padding-top:9px;">
					 <div class="form-group">
						<input type="text" name="user" class="form-control" placeholder="Name">
						<input type="password" name="pw" class="form-control" placeholder="Password">
					</div>
					<button type="submit" name="login" value="true" class="btn btn-default">Login</button>
				</form></li>
			</ul>
		</div>
	</div>
	</nav>
		
	<div class="container">