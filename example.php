<?php
include_once 'app/header.php';

$data = array(
	'private' => '',
	'public' => '',
	'address' => ''
);

try
{
	$ecdsa = new Ecdsa();
	$keys = $ecdsa->getKey();

	$data['private'] = $keys['private'];
	$data['public'] = $keys['public'];
	$data['address'] = $ecdsa->getAdress($data['public']);
}
catch(Example $e)
{
	// empty
}
?>
<h4>Example</h4>

<h5>Private key</h5>
<pre><code><?=$data['private']?></code></pre>
<h5>Public key</h5>
<pre><code><?=$data['public']?></code></pre>
<h5>Address</h5>
<pre><code><?=$data['address']?></code></pre>
<?php
include_once 'app/footer.php';