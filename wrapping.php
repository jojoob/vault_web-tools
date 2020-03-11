<!DOCTYPE html>
<html>
	<head>
		<title>Vault Tools - Wrapping</title>
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
					$wrapping_token = '';
					if (preg_match('/s\\.\\w{24}/', $_POST["wrapping_token"]) == 1) {
						$wrapping_token = $_POST["wrapping_token"];
					} elseif (preg_match('/s\\.\\w{24}/', $_GET["token"]) == 1) {
						$wrapping_token = $_GET["token"];
					}
				?>
				<h1>Use Wrapping Token</h1>
				<form method="post" class="form-inline">
					<input id="wrapping_token" type="text" name="wrapping_token" required autocomplete="off" value="<?php echo $wrapping_token; ?>" class="form-control mb-2 mr-sm-2" placeholder="Wrapping token" >
					<button type="submit" name="lookup" class="btn btn-primary mb-2 mr-sm-2">Lookup</button>
					<button type="submit" name="unwrap" class="btn btn-warning mb-2 mr-sm-2">Unwrap</button>
				</form>
			</div></div>
			<div class="row"><div class="col">
				<?php
					if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						if ($wrapping_token) {
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Vault-Token: ' . $wrapping_token]);
							if (isset($_POST['lookup'])) {
								curl_setopt($ch, CURLOPT_URL, $vault_api . 'sys/wrapping/lookup');
							} elseif (isset($_POST['unwrap'])) {
								curl_setopt($ch, CURLOPT_URL, $vault_api . 'sys/wrapping/unwrap');
								curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
							} else {
								echo '<div class="alert alert-danger" role="alert">Something mysterious happened.</div>';
							}
							$response = curl_exec($ch);
							if (curl_errno($ch) == 0) {
								$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
								if (isset($_POST['unwrap']) && $httpCode == 200) {
									echo '<div class="alert alert-warning" role="alert">Copy the unwrapped data elsewhere. It is not possible to retrieve the unwrapped data again!</div>';
								}
								echo '<div class="card"><pre>';
								echo htmlentities(json_encode(json_decode($response), JSON_PRETTY_PRINT));
								echo '</pre></div>';
							} else {
								$error = curl_error($ch);
								echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
							}
							curl_close($ch);
						} else {
							echo '<div class="alert alert-warning" role="alert">Malformed wrapping token entered.</div>';
						}
					}
				?>
			</div></div>
		</div>
	</body>
</html>
