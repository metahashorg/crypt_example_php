<?php
include_once 'app/header.php';

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

		$sign = $ecdsa->sign($_POST['text'], $privkey);

		$result = array(
			'type' => 'success',
			'data' => ''
		);

		$result['data'] = '<p><b>Sign:</b> '.$sign.'</p>';
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
<h4>Signature</h4>

<?if($result):?>
	<div class="alert alert-<?=$result['type']?>"><?=$result['data']?></div>
<?endif;?>

<form action="" method="post">
	<input type="hidden" name="send" value="1">
	<div class="form-group">
		<label for="field1">Private key</label>
		<textarea name="key" id="field1" class="form-control" style="height: 100px;" placeholder="Private key in the hex format"><?=isset($_POST['key'])?htmlspecialchars($_POST['key']):''?></textarea>
	</div>
	<div class="form-group">
		<label for="field2">Signed text</label>
		<textarea name="text" id="field2" class="form-control" style="height: 100px;"><?=isset($_POST['text'])?htmlspecialchars($_POST['text']):''?></textarea>
	</div>
	<div><button type="submit" class="btn btn-default">SEND</button></div>
</form>

<?php
include_once 'app/footer.php';
?>