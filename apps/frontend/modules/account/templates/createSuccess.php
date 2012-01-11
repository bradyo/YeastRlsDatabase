<h2>Create New Account</h2>

<style type="text/css">
  ul.checkbox_list {
    list-style-type: none;
    margin: 0;
    padding: 0
  }
</style>

<p style="margin-top:0">Create a new account and assign access credentials below.
  The user will be notified via e-mail of the new login information:</p>
<br />

<form action="<?php echo url_for('account/create') ?>" method="post">
  <?php echo $form->renderHiddenFields() ?>

  <table class="form">
    <tr>
      <td class="label"><label>Username:</label></td>
      <td>
        <?php echo $form['username'] ?>
        <?php echo $form['username']->renderError() ?>
      </td>
    </tr>
    <tr>
      <td class="label"><label>E-mail:</label></td>
      <td>
        <?php echo $form['email'] ?>
        <?php echo $form['email']->renderError() ?>
      </td>
    </tr>
    
    <tr>
      <td class="label" style="vertical-align:top"><label>Credentials:</label></td>
      <td>
        <?php echo $form['credentials']->renderError() ?>
        <?php echo $form['credentials'] ?>
      </td>
    </tr>
  </table>
  <br />

  <button type="submit" name="submit" style="width:10em">Submit</button>

</form>
