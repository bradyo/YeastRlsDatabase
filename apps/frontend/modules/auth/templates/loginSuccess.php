<h2>Log In to Access Core Resources</h2>

<p style="margin-top:0">Log in credentials are given to members of the core collaboration.
  Contact <a href="mailto:bradyo@uw.edu">bradyo@uw.edu</a> to request a login.
</p>
<br />

<form action="<?php echo url_for('auth/login') ?>" method="post">
  <table class="form">
    <?php echo $form ?>
    <tr>
      <td colspan="2">
        <input type="submit" value="submit"/>
      </td>
    </tr>
  </table>
</form>
