<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>#MetaAPI</title>
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<link rel="stylesheet" href="static/css/bootstrap.min.css">
	<link rel="stylesheet" href="static/css/reset.css">
	<link rel="stylesheet" href="static/css/bootstrap-select.css">
	<link rel="stylesheet" href="static/css/main.css">
	


</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<p class="api__big-tit">#MetaAPI</p>
				<p class="api__just-text api__just-text_pt1">Developers API for #MHC transactions in #MetaHash network</p>
				<div class="api__block">
					<p class="api__block-tit">
						Send transations to #MetaHash
					</p>
					<div class="row">
						<form id="transactionform">
							<div class="col-lg-5 col-md-6">
								<div class="row">
									<div class="col-xs-6">
										<div class="api__over-inp">
											<label for="">#MHC value to send:</label>
											<input type="text" placeholder="0" value="0" id="transactionform_value">
										</div>
									</div>
									<div class="col-xs-6">
										<div class="api__over-inp">
											<label for="">Max fee:</label>
											<input type="text" placeholder="0.0" value="0.0" id="transactionform_fee" disabled>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-7 col-md-6">
								<div class="api__over-inp">
									<label for="">Transaction data:</label>
									<input type="text" plceholder="Any custom data" id="transactionform_data" disabled>
								</div>
							</div>
							<div class="col-md-12">
								<div class="api__over-inp">
									<label>
										From wallet:
										<div class="api__select-text">
											Copy your private keys to "data/" folder to appear in the list or <a href="#walletcreateform">create a new wallet</a>
										</div>
									</label>
									<select class="selectpicker api__select" id="transactionform_address">
									</select>
									<div class="api__select-text mob">
										Copy your private keys to "data/" folder to appear in the list or <a href="#walletcreateform">create a new wallet</a>
									</div>
								</div>
							</div>
							
							<div class="col-lg-8 col-md-8">
								<div class="api__over-inp">
									<label for="">To #MHC address:</label>
									<input type="text" id="transactionform_to">
								</div>
							</div>
							<div class="col-lg-4 col-md-4">
								<div class="api__over-inp">
									<button class="api__bigbtn margtoplabbtn">Send transaction</button>
								</div>
							</div>
						</form>

						<div class="col-md-12">
							<textarea class="api__transactions-status" id="transactionlogs" placeholder="No logs to show" readonly=""></textarea>
						</div>
						<div class="col-md-1 col-xs-6">
							<button class="api__small-btn" id="transactionlogs_copy" disabled>Copy</button>
						</div>
						<div class="col-md-1 col-xs-6">
							<button class="api__small-btn clear" id="transactionlogs_clear" disabled>Clear</button>
						</div>
						<div class="col-md-12">
							<div class="api__mid-tit">
								Last transactions
							</div>
						</div>

						<div class="col-md-12 no-more-tables">
							<table class="api__lasttrans-table" id="addresshistory">
								<thead>
									<tr>
										<th>From</th>
										<th>To</th>
										<th>Value</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr class="api__empty_lastransrow">
										<td colspan="4">No transactions to show</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="api__block">
					<p class="api__block-tit">
						Developer information
					</p>
					<div class="api__mid-tit api__mid-tit_fst">
						Files and functions
					</div>
					<div class="api__overdevinf-block">
						<div class="row">
							<div class="col-md-5 col-sm-12 api__devinf-block row1">data/[wallet address].mh</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Keys files to sign #MetaHash transactions</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">crypt_example.php	</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Connects to #MetaHash protocol with <code>mdanter/ecc</code></div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">crypt_example_bin.php	</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Connects to #MetaHash protocol with our driver for PHP <a href="https://github.com/metahashorg/php-mhcrypto" target="_blank">php-mhcrypto</a></div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">README.md</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Full API instructions</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">dev/transactions.php</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Test-tool to send test transactions</div>
						</div>
					</div>
					<div class="api__mid-tit">
						API Calls
					</div>
					<div class="api__overdevinf-block">
						<div class="row">
							<div class="col-md-5 col-sm-12 api__devinf-block row1">generate</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Generate MH address</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">fetch-balance</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Get balance for MH address</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">fetch-history</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Get history for MH address</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">get-tx</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Get transaction information by hash</div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">send-tx</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2">Create and send transaction</div>
						</div>
					</div>
					<div class="api__mid-tit">
						Additional information
					</div>
					<div class="api__overdevinf-block">
						<div class="row">
							<div class="col-md-5 col-sm-12 api__devinf-block row1">#MetaHash Developer Portal</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2"><a>https://support.metahash.com</a></div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">#MetaHash in GitHub</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2"><a>https://github.com/metahashorg</a></div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">Contact Support</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2"><a>support@metahash.org</a></div>
							<div class="col-md-5 col-sm-12 api__devinf-block row1">#MetaHash Website</div>
							<div class="col-md-7 col-sm-12 api__devinf-block row2"><a>https://metahash.org</a></div>
						</div>
					</div>
				</div>
				<div class="api__block" id="walletcreateform">
					<p class="api__block-tit">
						Create new wallet
					</p>
					<div class="api__creatnw-pretit"></div>
					<div class="row">						
						<div class="col-lg-5 col-md-5">
							<div class="api__over-inp">
								<label class="api__labadrwal" for="">Wallet keyfile will be stored in <b>data/</b></label>
								<button class="api__bigbtn" id="createwallet_button">Create wallet</button>
							</div>
						</div>
						<div class="col-lg-7 col-md-7">
							<div class="api__over-inp">
								<label class="api__labadrwal" for="">Wallet address:</label>
								<input class="api__create-adrinp" type="text" placeholder="Here will be your new wallet address" id="createwallet_input" readonly>
							</div>
						</div>

						<div class="col-lg-12 col-md-12">
							<div class="t-tle">
								<div class="t-row">
									<div class="t-cell">
										<p class="api__create-hint success" id="createwallet_success" style="display: none">
											Wallet successfully created.
										</p>
									</div>
								</div>
							</div>
							
						</div>
					</div>
				</div>
				<footer class="api__footer">

					<p class="api__foot-text">
						Copyright © 2017-2018 #MetaHash.<br> All Rights Reserved
					</p>
					<div class="api__overfoot-logo"><img class="api__foot-logo" src="static/images/logo_mail.svg" alt=""></div>
				</footer>
			</div>
		</div>
	</div>

	<script src="static/js/jquery.min.js"></script>
	<script src="static/js/bootstrap.min.js"></script>
	<script src="static/js/bootstrap-select.js"></script>
	<script src="static/js/main.js"></script>
	<script>
		$(document).ready(function()
		{
			update_address_list();

			$('body')
				.on('submit', '#transactionform', send_transaction)
				.on('click', '#transactionlogs_copy', copy_logs)
				.on('click', '#transactionlogs_clear', clear_logs)
				.on('click', '#createwallet_button', generate_address);
		});

		var update_address_list = function()
		{
			var query = {
					method: 'get-list-address',
					net: 'dev',
				};

			$.ajax({
				dataType: 'json',
				method: 'GET',
				url: '../crypt_example.php',
				data: query,
				success: function(data)
				{
					var transactionform_address = $('#transactionform_address');
					$.each(data, function(key,value){
						transactionform_address.append('<option data-icon="api__option-color" data-subtext="n/a #MHC" id="option' + value + '">' + value + '</option>');
						update_address_balance(value);
					});
					$('.selectpicker').selectpicker('refresh');
				},
				error: function(data)
				{
					console.log('ajax error',data);
				}
			});
			return false;
		}

		var send_transaction = function()
		{
			var query = {
					method: 'send-tx',
					net: 'dev',
					address: $('#transactionform_address').val(),
					to: $('#transactionform_to').val(),
					value: $('#transactionform_value').val()*100000,
					fee: 0,
					data: '',
				};

			$.ajax({
				dataType: 'json',
				method: 'GET',
				url: '../crypt_example.php',
				data: query,
				success: function(data)
				{
					if(data.params)
					{
						if($('.api__empty_lastransrow').length>0)
							$('#addresshistory tbody').html('');

						$('#addresshistory tbody').prepend('<tr data-tr="' + data.params + '">' +
							'<td data-title="From"><a>' + query.address + '</a></td>' +
							'<td data-title="To"><a>' + query.to + '</a></td>' +
							'<td data-title="Value">' + mhc_formatter(query.value) + '</td>' +
							'<td data-title="Status"><span class="api__lasttrans-table-stat pen">Pending</span></td>' +
						'</tr>');

						write_log('Transaction ' + data.params + ' started');

						check_transaction(data.params, 0);
					}
					else if(data.error)
					{
						write_log('error: ' + data.error);
					}
					else
					{
						write_log('unknown error');
					}

				},
				error: function(data)
				{
					console.log('ajax error',data);
				}
			});

			return false;
		}


		var check_transaction = function(tx, iteration)
		{
			iteration++;

			var query = {
					method: 'get-tx',
					net: 'dev',
					hash: tx,
				};

			$.ajax({
				dataType: 'json',
				method: 'GET',
				url: '../crypt_example.php',
				data: query,
				success: function(data)
				{
					if(typeof data.result != 'undefined' && typeof data.result.transaction != 'undefined' && typeof data.result.transaction.transaction != 'undefined')
					{
						$('#addresshistory tbody tr[data-tr="' + tx + '"] .api__lasttrans-table-stat')
							.removeClass('pen')
							.addClass('suc')
							.html('Success')
						write_log('Transaction ' + tx + ' completed!');
					}
					else if(iteration<10)
					{
						if(data.error)
							write_log(data.error.message);
						setTimeout(check_transaction(tx, iteration), 1000);
					}
					else
					{
						$('#addresshistory tbody tr[data-tr="' + tx + '"]')
							.addClass('error')
							.find('.api__lasttrans-table-stat')
								.removeClass('pen')
								.addClass('err')
								.html('Error');
						write_log('Transaction ' + tx + ' failed!');
					}
				},
				error: function(data)
				{
					if(iteration<30)
					{
						setTimeout(check_transaction(tx, iteration), 1000);
					}
					else
					{
						$('#addresshistory tbody tr[data-tr="' + tx + '"]')
							.addClass('error')
							.find('.api__lasttrans-table-stat')
								.removeClass('pen')
								.addClass('err')
								.html('Error');
						write_log('Network proplems. Unknown end of transaction ' + tx);
					}
				}
			});

			return false;
		}

		var generate_address = function(address) 
		{
			var query = {
					method: 'generate',
					net: 'dev',
				};

			$.ajax({
				dataType: 'json',
				method: 'GET',
				url: '../crypt_example.php',
				data: query,
				success: function(data)
				{				
					$('#createwallet_input').val(data.address);
					$('#createwallet_success').show();
					$('#transactionform_address').append('<option data-icon="api__option-color" data-subtext="0.000000 #MHC" id="option' + data.address + '">' + data.address + '</option>');
					$('.selectpicker').selectpicker('refresh');
				},
				error: function(data)
				{
					console.log('ajax error',data);
				}
			});
		}

		var update_address_balance = function(address) 
		{
			if(address.length!=52)
				return 0;

			var query = {
					method: 'fetch-balance',
					net: 'dev',
					address: address
				};

			$.ajax({
				dataType: 'json',
				method: 'GET',
				url: '../crypt_example.php',
				data: query,
				success: function(data)
				{
					var balance = data.result.received - data.result.spent;
					$('#option' + data.result.address).data('subtext', mhc_formatter(balance)  + ' #MHC');
					$('.selectpicker').selectpicker('refresh');
				},
				error: function(data)
				{
					console.log('ajax error',data);
				}
			});
		}
		
		var write_log = function(message) 
		{
			var transactionlogs = $('#transactionlogs'),
				cur_date = new Date(), 
				n = "";

			if(transactionlogs.val().length>0)
				n = "\n";
			else
				$('#transactionlogs_clear, #transactionlogs_copy').removeAttr('disabled');

			transactionlogs.val(transactionlogs.val() + n + cur_date.toLocaleDateString() + ' ' + cur_date.toLocaleTimeString() + ' | ' + message);

			transactionlogs.scrollTop(transactionlogs[0].scrollHeight - transactionlogs.height());
		}

		var copy_logs = function() 
		{
			$('#transactionlogs').select();
			document.execCommand("copy");
			document.getSelection().removeAllRanges();

			$('#transactionlogs_copy').html('ok!');
			
			setTimeout(function() { 
				$('#transactionlogs_copy').html('Copy');
			}, 2000);
		}

		var clear_logs = function(new_row) 
		{
			$('#transactionlogs').val('');
			$('#transactionlogs_clear, #transactionlogs_copy').attr('disabled','disabled');
		}

		var mhc_formatter = function(number) 
		{
			var decimals = 6,
				dec_point = '.',
				thousands_sep = ',';

			number = number/1000000;
			var n = !isFinite(+number) ? 0 : +number,
				prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
				sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
				dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
				s = '',
				toFixedFix = function(n, prec) {
					var k = Math.pow(10, prec);
					return '' + (Math.round(n * k) / k)
						.toFixed(prec);
				};
			s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
				.split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '')
				.length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1)
					.join('0');
			}
			return s.join(dec);
		}
	</script>
</body>
</html>