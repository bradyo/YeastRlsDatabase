
<?php include_partial('header') ?>


<h2>Update Strains</h2>

<p>Here you can upload new strains or update existing strains.</p>

<ol>
  <li>Download the <a href="<?php echo sfConfig::get('sf_relative_url_root')
			?>files/yeast_strains_template.xls">strains upload template</a>
    and fill in strain information.
    <p>note: If you are updating strains, make sure the "name" field
      matches the existing value.</p>
  </li>

  <li>Paste the data from the upload template into the input box below.</li>
  <li>Click "Submit"</li>
</ol>

<p>If you submit strains that already have entries in the database, you will
  be prompted for further action. You will be able to overwrite
  existing strains in which you are the original creator.</p>
<br />

<form name="updateForm" action="<?php echo url_for('yeast/update') ?>" method="post" >
  <textarea name="inputText" id="inputText" cols="100" rows="10" wrap="off"
    ></textarea>
  <br />
  <input type="submit" name="process" value="Submit" />
</form>


