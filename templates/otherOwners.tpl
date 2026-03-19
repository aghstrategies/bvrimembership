{* template block that contains the new field *}
<fieldset id="otherowner">
  <legend>Additonal Owner(s)</legend>
  <div id="certfield-tr">
    <h3>Owner 2</h3>
    <div class="crm-section">
      <div class="label"><label>{$form.first_name_2.label}</label></div>
      <div class="content">{$form.first_name_2.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.last_name_2.label}</label></div>
      <div class="content">{$form.last_name_2.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.email_2.label}</label></div>
      <div class="content">{$form.email_2.html}</div>
    </div>
    <div class="clear"></div>
    <h3>Owner 3</h3>
    <div class="crm-section">
      <div class="label"><label>{$form.first_name_3.label}</label></div>
      <div class="content">{$form.first_name_3.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.last_name_3.label}</label></div>
      <div class="content">{$form.last_name_3.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.email_3.label}</label></div>
      <div class="content">{$form.email_3.html}</div>
    </div>
    <div class="clear"></div>
    <h3>Owner 4</h3>
    <div class="crm-section">
      <div class="label"><label>{$form.first_name_4.label}</label></div>
      <div class="content">{$form.first_name_4.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.last_name_4.label}</label></div>
      <div class="content">{$form.last_name_4.html}</div>
    </div>
    <div class="clear"></div>
    <div class="crm-section">
      <div class="label"><label>{$form.email_4.label}</label></div>
      <div class="content">{$form.email_4.html}</div>
    </div>
    <div class="clear"></div>
</fieldset>
{* reposition the above block after #someOtherBlock *}
<script type="text/javascript">
  cj('#otherowner').insertBefore('#priceset');
</script>
