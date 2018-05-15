<?php
include_once 'app/header.php';

$result = false;

if(isset($_POST['send']) && $_POST['send'] == 1 && !empty($_POST['key']) && !empty($_POST['text']) && !empty($_POST['sign']))
{
	try
	{
		$ecdsa = new Ecdsa();

		$pubkey = $_POST['key'];
		if(strstr($pubkey, '-----BEGIN') || $ecdsa->is_base64_encoded($pubkey))
		{
			$pubkey = $ecdsa->parsePublicPem($pubkey);
		}

		$status = $ecdsa->verify($_POST['sign'], $_POST['text'], $pubkey);

		if(!$status)
		{
			throw new Exception("Invalid sign");
		}

		$result = array(
			'type' => 'success',
			'data' => 'OK'
		);
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
<h4>Signature Validator</h4>

<?if($result):?>
	<div class="alert alert-<?=$result['type']?>"><?=$result['data']?></div>
<?endif;?>

<form action="" method="post">
	<input type="hidden" name="send" value="1">
	<div class="form-group">
		<label for="field1">Public key</label>
		<textarea name="key" id="field1" class="form-control" style="height: 100px;" placeholder="Public key in the hex format"><?=isset($_POST['key'])?htmlspecialchars($_POST['key']):''?></textarea>
	</div>
	<div class="form-group">
		<label for="field2">Signed text</label>
		<textarea name="text" id="field2" class="form-control" style="height: 100px;"><?=isset($_POST['text'])?htmlspecialchars($_POST['text']):''?></textarea>
	</div>
	<div class="form-group">
		<label for="field3">Signature</label>
		<textarea name="sign" id="field3" class="form-control" style="height: 100px;" placeholder="Signature in the hex format"><?=isset($_POST['sign'])?htmlspecialchars($_POST['sign']):''?></textarea>
	</div>
	<div><button type="submit" class="btn btn-default">SEND</button></div>
</form>

<?php
include_once 'app/footer.php';
?>