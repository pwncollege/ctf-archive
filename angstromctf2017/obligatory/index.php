<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Difficult Choices</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    </head>
	<?php
		if(isset($_POST["username"]) && isset($_POST["password"]))
		{
			$success = false;

			$use = $_POST["username"];
			$pass = $_POST["password"];

			$username = "root";
			$password = "0xDEADBEEF";
			$conn = new mysqli("localhost", $username, $password);

			$sql = "SELECT username FROM users WHERE password = \"$pass\"";
			$result = $conn->query($sql);

			if($result->fetch_assoc() > 0)
			{
				$unlocked = $result->fetch_all();
				for($i = 0; $i < $result->fetch_assoc(); $i++)
				{
					if($unlocked[$i] == $use)
					{
						$success = true;
						break;
					}
				}
			}

			$conn->close();
		}
	?>
	
    <body>
        <div class="container">
			<?php
			if($success)
			{
				$flag = "actf{3v3ry_d4mn_ctf_h4s_th1s_pr0bl3m}";

				echo "<h1>Access Granted</h1>
				You logged in successfully!
				<br/>
				<br/>

					<div class=\"alert alert-success\" role=\"alert\">
						<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span>
						$flag
					</div>\"

				";
			}
			else{
				echo "<h1>Access Denied</h1>
				This flag is locked, log in to view it!
				<br/>
				<br/>

					<div class=\"alert alert-danger\" role=\"alert\">
						<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span>
						Log-in failed
					</div>

				<form id=\"password-form\" method=\"post\" action=\"/\">
					<div class=\"form-group\">
						<label for=\"password\">Password</label>
						<input type=\"password\" class=\"form-control\" id=\"password\" placeholder=\"Password\">
					</div>

					<input type=\"hidden\" id=\"data\" name=\"data\">

					<button type=\"submit\" class=\"btn btn-success\">Submit</button>
				</form>";
			}
			?>

        </div>
    </body>
</html>