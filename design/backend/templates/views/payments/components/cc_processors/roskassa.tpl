{* $Id: roskassa.tpl  $cas *}

<div class="control-group">
	<label class="control-label" for="m_url">{__("roskassa_url")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][m_url]" id="m_url" value="{if $processor_params.m_url == ""}//pay.roskassa.net{/if}{$processor_params.m_url}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="m_shop">{__("roskassa_id")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][m_shop]" id="m_shop" value="{$processor_params.m_shop}" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="m_key1">{__("roskassa_secret_key")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][m_key1]" id="m_key1" value="{$processor_params.m_key}" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="currency">{__("roskassa_currency")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][currency]" id="currency">
			<option value="RUB" {if $processor_params.currency == "RUB"}selected="selected"{/if}>{__("currency_code_rur")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="pathlog">{__("roskassa_pathlog")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][pathlog]" id="pathlog" value="{$processor_params.pathlog}" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="emailerr">{__("roskassa_emailerr")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][emailerr]" id="emailerr" value="{$processor_params.emailerr}" class="input-text" size="100" />
	</div>
</div>
