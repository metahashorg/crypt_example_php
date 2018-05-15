<?php
include_once 'app/header.php';

$result = false;

if(isset($_POST['send']) && $_POST['send'] == 1 && !empty($_POST['key']))
{
	try
	{
		$ecdsa = new Ecdsa();

		$key = $_POST['key'];
		$password = trim($_POST['password']);

		if(empty($password))
		{
			throw new Exception("Password is empty", 1);
		}

		if($private_key_res = openssl_pkey_get_private($key, $password))
		{
			openssl_pkey_export($private_key_res, $private_key);
			
			if(strstr($private_key, '-----BEGIN') || $ecdsa->is_base64_encoded($private_key))
			{
				$private_key = $ecdsa->parsePrivatePem($private_key);
			}

			$public_key = $ecdsa->privateToPublic($private_key);
			$address = $ecdsa->getAdress($public_key);

			$result = array(
				'type' => 'success',
				'data' => ''
			);

			$result['data'] = '<p><b>Address:</b> '.$address.'</p>';
		}
		else
		{
			throw new Exception("Invalid key or password", 1);
		}
	}
	catch(Exception $e)
	{
		$result = array(
			'type' => 'danger',
			'data' => $e->getMessage()
		);
	}
}
?>
<h4>Encrypted key Validator</h4>
<p>To check whether your encrypted key is correct, paste it into the text field below in the <b>pem</b> format and click the <b>SEND</b> button</p>

<?if($result):?>
	<div class="alert alert-<?=$result['type']?>"><?=$result['data']?></div>
<?endif;?>

<form action="" method="post">
	<input type="hidden" name="send" value="1">
	<div class="form-group">
		<label for="field1">Encrypted key</label>
		<textarea name="key" class="form-control" style="height: 100px;" placeholder="Encrypted key in the pem format"><?=isset($_POST['key'])?htmlspecialchars($_POST['key']):''?></textarea>
	</div>
	<div class="form-group">
		<label for="field1">Password</label>
		<input name="password" class="form-control" placeholder="Password" value="<?=isset($_POST['password'])?htmlspecialchars($_POST['password']):''?>">
	</div>
	<div><button type="submit" class="btn btn-default">SEND</button></div>
</form>

<?php
include_once 'app/footer.php';
?>