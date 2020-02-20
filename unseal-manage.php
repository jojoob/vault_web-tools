<!DOCTYPE html>
<html>
	<head>
		<title>Vault Tools - Manage Unseal Operation</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
		<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script> -->
		<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script> -->
		<style>
			.container-fluid {
				padding-left: 100px;
				padding-right: 100px;
				padding-top: 20px;
			}
			.row {
				padding-bottom: 10px;
			}
		</style>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row"><div class="col">
				<?php
					if (isset($_ENV["VAULT_ADDR"])) {
						$vault_api = $_ENV["VAULT_ADDR"] . '/v1/';
					} else {
						echo '<div class="alert alert-danger" role="alert">VAULT_ADDR not set!</div>';
					}
				?>
				<h1>Manage Unseal Operation</h1>
				<form method="post" class="form-inline">
					<button type="submit" name="status" class="btn btn-primary mb-2 mr-sm-2">Status</button>
					<button type="submit" name="reset" class="btn btn-warning mb-2 mr-sm-2">Reset</button>
				</form>
			</div></div>
			<div class="row"><div class="col">
				<?php
					if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						if (isset($_POST['status'])) {
							curl_setopt($ch, CURLOPT_URL, $vault_api . 'sys/seal-status');
						} elseif (isset($_POST['reset'])) {
							$data = '{"reset": true}';
							curl_setopt($ch, CURLOPT_URL, $vault_api . 'sys/unseal');
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
							curl_setopt($ch, CURLOPT_HTTPHEADER, array(
								'Content-Type: application/json',
								'Content-Length: ' . strlen($data)));
							curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						} else {
							echo '<div class="alert alert-danger" role="alert">Something mysterious happened.</div>';
						}
						$response = curl_exec($ch);
						if (curl_errno($ch) == 0) {
							$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							if (isset($_POST['reset']) && $httpCode == 200) {
								echo '<div class="alert alert-success" role="alert">Unseal operation resetted.</div>';
							}
							echo '<div class="card"><pre>';
							echo htmlentities(json_encode(json_decode($response), JSON_PRETTY_PRINT));
							echo '</pre></div>';
						} else {
							$error = curl_error($ch);
							echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
						}
						curl_close($ch);
					}
				?>
			</div></div>
		</div>
	</body>
</html>
