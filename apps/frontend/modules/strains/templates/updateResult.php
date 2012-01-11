<?php include_partial('header') ?>

<h2>Strain Update Results</h2>

<?php if(count($errors) > 0): ?>
  <div>
    The following errors occured:
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if(count($addedStrains) > 0): ?>
  <div>
    The following strains were added successfully:
    <ul>
      <?php foreach ($addedStrains as $strainName): ?>
        <li><?php echo $strainName ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if(count($updatedStrains) > 0): ?>
  <div>
    The following strains were updated successfully:
    <ul>
      <?php foreach ($updatedStrains as $strainName): ?>
        <li><?php echo $strainName ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if(count($skippedStrains) > 0): ?>
  <div>
    The following strains were skipped:
    <ul>
      <?php foreach ($skippedStrains as $strainName): ?>
        <li><?php echo $strainName ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<br />

<p>Continue to:
  <a href="<?php echo url_for('yeast/index') ?>">Strains</a>
  <a href="<?php echo url_for('yeast/index').'?owner='.$sf_user->getAttribute('username') ?>">My Strains</a>
</p>