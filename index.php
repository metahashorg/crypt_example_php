<?php
include_once './app/vendor/autoload.php';
include_once './app/Ecdsa.php';
include_once './app/header.php';

$result = false;
if(isset($_POST['send']) && $_POST['send'] == 1 && !empty($_POST['key']))
{
	try
	{
		$ecdsa = new Ecdsa();
		$privkey = $_POST['key'];
		if(strstr($privkey, '-----BEGIN') || $ecdsa->is_base64_encoded($privkey))
		{
			$privkey = $ecdsa->parsePrivatePem($privkey);
		}
		$public_key = $ecdsa->privateToPublic($privkey);
		$address = $ecdsa->getAdress($public_key);
		$result = array(
			'type' => 'success',
			'data' => ''
		);
		$result['data'] = '<p><b>Public key:</b> '.$public_key.'</p>';
		$result['data'] .= '<p><b>Address:</b> '.$address.'</p>';
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
<h4>Private key Validator</h4>
<p>To check whether your private key is correct, paste it into the text field below in the <b>hex</b> format and click the <b>SEND</b> button</p>

<?php
if($result):
?>
	<div class="alert alert-<?=$result['type']?>"><?=$result['data']?></div>
<?php
endif;
?>

<form action="" method="post">
	<input type="hidden" name="send" value="1">
	<div class="form-group">
		<label for="field1">Private key</label>
		<textarea name="key" class="form-control" style="height: 100px;" placeholder="Private key in the hex format"><?=isset($_POST['key'])?htmlspecialchars($_POST['key']):''?></textarea>
	</div>
	<div><button type="submit" class="btn btn-default">SEND</button></div>
</form>

<?php
include_once './app/footer.php';
?>