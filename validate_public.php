<?php
include_once 'app/header.php';

$result = false;

if(isset($_POST['send']) && $_POST['send'] == 1 && !empty($_POST['key']))
{
	try
	{
		$ecdsa = new Ecdsa();

		$key = $_POST['key'];
		if(strstr($key, '-----BEGIN') || $ecdsa->is_base64_encoded($key))
		{
			$key = $ecdsa->parsePrivatePem($key);
		}
		$address = $ecdsa->getAdress($key);

		$result = array(
			'type' => 'success',
			'data' => ''
		);

		$result['data'] = '<p><b>Address:</b> '.$address.'</p>';
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
<h4>Public key Validator</h4>
<p>To check whether your public key is correct, paste it into the text field below in the <b>hex</b> format and click the <b>SEND</b> button</p>

<?if($result):?>
	<div class="alert alert-<?=$result['type']?>"><?=$result['data']?></div>
<?endif;?>

<form action="" method="post">
	<input type="hidden" name="send" value="1">
	<div class="form-group">
		<label for="field1">Public key</label>
		<textarea name="key" class="form-control" style="height: 100px;" placeholder="Public key in the hex format"><?=isset($_POST['key'])?htmlspecialchars($_POST['key']):''?></textarea>
	</div>
	<div><button type="submit" class="btn btn-default">SEND</button></div>
</form>

<?php
include_once 'app/footer.php';
?>