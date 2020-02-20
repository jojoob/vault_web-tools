<!DOCTYPE html>
<html>
	<head>
		<title>Vault Tools - Manage CRL</title>
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
					if (isset($_ENV["PKI_PATH"])) {
						$pki_path = $_ENV["PKI_PATH"];
					} else {
						echo '<div class="alert alert-danger" role="alert">PKI_PATH not set!</div>';
					}
					if (isset($_ENV["CRL_EXPORT_PATH"])) {
						$crl_export_path = $_ENV["CRL_EXPORT_PATH"];
					} else {
						echo '<div class="alert alert-danger" role="alert">CRL_EXPORT_PATH not set!</div>';
					}
					if (preg_match('/s\\.\\w{24}/', $_POST["token"]) == 1) {
						$token = $_POST["token"];
					}
				?>
				<h1>Manage CRL</h1>
				<form method="post" class="form-inline">
					<button type="submit" name="view" class="btn btn-primary mb-2 mr-sm-2">View</button>
				</form>
				<form method="post" class="form-inline">
					<input id="token" type="text" name="token" required autocomplete="off" value="<?php echo $token; ?>" class="form-control mb-2 mr-sm-2" placeholder="Token" >
					<button type="submit" name="rotate" class="btn btn-warning mb-2 mr-sm-2">Rotate</button>
				</form>
			</div></div>
			<div class="row"><div class="col">
				<?php
					if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

						if (isset($_POST['view'])) {
							curl_setopt($ch, CURLOPT_URL, $vault_api . $pki_path . '/crl/pem');

							$response = curl_exec($ch);
							if (curl_errno($ch) == 0) {
								$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
								echo '<div class="card"><pre>';
								echo htmlentities($response);
								echo '</pre></div>';
							} else {
								$error = curl_error($ch);
								echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
							}
						} elseif (isset($_POST['rotate'])) {
							if ($token) {
								curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Vault-Token: ' . $token]);
								curl_setopt($ch, CURLOPT_URL, $vault_api . $pki_path . '/crl/rotate');

								$response = curl_exec($ch);
								if (curl_errno($ch) == 0) {
									$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
									if ($httpCode == 200) {
										// Export...
										$fp = fopen ($crl_export_path, 'w+');
										$ch2 = curl_init();
										curl_setopt($ch2, CURLOPT_URL, $vault_api . $pki_path . '/crl');
										curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch2, CURLOPT_FILE, $fp);
										$data = curl_exec($ch2);
										curl_close($ch2);
										fclose($fp);
										if (curl_errno($ch) == 0) {
											echo '<div class="alert alert-success" role="alert">CRL rotated and exported.</div>';
										} else {
											$error = curl_error($ch);
											echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
										}
									}
									echo '<div class="card"><pre>';
									echo htmlentities(json_encode(json_decode($response), JSON_PRETTY_PRINT));
									echo '</pre></div>';
								} else {
									$error = curl_error($ch);
									echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
								}
							} else {
								echo '<div class="alert alert-warning" role="alert">Malformed token entered.</div>';
							}
						} else {
							echo '<div class="alert alert-danger" role="alert">Something mysterious happened.</div>';
						}
						curl_close($ch);
					}
				?>
			</div></div>
		</div>
	</body>
</html>
