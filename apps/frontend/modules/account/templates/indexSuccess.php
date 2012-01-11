<h2>Your Account</h2>

<p>
  This page is for updating your account details.
  <?php if (!$sf_user->hasCredential('update account')): ?>
    Your account does not allow editing of account details. Please contact
    <a href="mailto:bradyo@uw.edu">bradyo@uw.edu</a> to enable this.
  <?php endif ?>
</p>


<h3>Update your information:</h3>

<p>This information is attached to resources you have made available 
 through the core collaboration databases.
</p>
<br />
<form name="accountUpdateForm" action="<?php echo url_for('account/index') ?>" method="post" >
  <div>
    <table class="form">
      <?php echo $accountUpdateForm->renderGlobalErrors() ?>
      <tr>
        <td class="label"><label>Email:</label></td>
        <td>
          <?php echo $accountUpdateForm['email'] ?>
          <?php if ($accountUpdateForm['email']->hasError()): ?>
            <br />
            <?php echo $accountUpdateForm['email']->renderError() ?>
          <?php endif ?>
        </td>
        <td class="note">enter an e-mail where you can be reached regarding
          shared resources/strains</td>
      </tr>
      <tr>
        <td class="label"><label>Lab:</label></td>
        <td><?php echo $accountUpdateForm['lab'] ?></td>
        <td class="note">enter the name of the lab you are from (i.e. Kaeberlein Lab)</td>
      </tr>
      <tr>
        <td class="label"><label>Location:</label></td>
        <td><?php echo $accountUpdateForm['location'] ?></td>
        <td class="note">enter where the lab is located (i.e. University of Washington)</td>
      </tr>
      <tr>
        <td class="label"><label>Phone:</label></td>
        <td><?php echo $accountUpdateForm['phone'] ?></td>
        <td class="note">enter a phone where you can be reached regarding shared
          resources/strains</td>
      </tr>

      <tr>
        <td></td>
        <td><input type="submit" value="Update Account" /></td>
      </tr>
    </table>
  </div>
</form>


<h3>Change your password:</h3>

<form name="passwordForm" action="<?php echo url_for('account/index') ?>" method="post" >
  <div>
    <table class="form">
      <?php echo $passwordUpdateForm->renderGlobalErrors() ?>
      <tr>
        <td class="label"><label>Password:</label></td>
        <td>
          <?php echo $passwordUpdateForm['password'] ?>
          <?php echo $passwordUpdateForm['password']->renderError() ?>
        </td>
      </tr>
      <tr>
        <td class="label"><label>(repeat):</label></td>
        <td>
          <?php echo $passwordUpdateForm['password_repeat'] ?>
          <?php echo $passwordUpdateForm['password_repeat']->renderError() ?>
        </td>
      </tr>

      <tr>
        <td></td>
        <td><input type="submit" value="Change Password" /></td>
      </tr>
    </table>
  </div>
</form>


<h3>Credentials granted to you:</h3>
<ul>
  <?php if (count($credentials) > 0): ?>
    <?php foreach ($credentials as $credential): ?>
      <li><?php echo $credential ?></li>
    <?php endforeach ?>
  <?php else: ?>
      <li>None</li>
  <?php endif ?>
</ul>



